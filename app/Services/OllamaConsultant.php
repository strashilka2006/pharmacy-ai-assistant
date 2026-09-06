<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ИИ-консультант. Порт public/chat_api.php.
 *
 * Что изменилось по сравнению с оригиналом:
 *  - в промпт уходит не весь каталог, а ограниченная выборка (см. config);
 *  - рецептурные товары отсеиваются ДО отправки в модель, а не после;
 *  - curl заменён на Http-клиент с таймаутом и логированием;
 *  - парсинг ответа вынесен в отдельный метод и покрывается тестом.
 */
class OllamaConsultant
{
    public function __construct(
        private readonly ?string $url = null,
        private readonly ?string $model = null,
    ) {
    }

    /**
     * @return array{text: string, products: Collection<int, Product>, has_prescription_matches: bool}
     */
    public function ask(string $question): array
    {
        $question = Str::limit(trim($question), 500, '');

        $catalog = $this->catalogForPrompt($question);
        $raw = $this->callModel($this->buildSystemPrompt($catalog), $question);

        [$text, $names] = $this->splitAnswer($raw);

        $matched = $this->matchProducts($names);

        $hasPrescription = $matched
            ->filter(fn (Product $p) => $p->prescription)
            ->isNotEmpty();

        if ($hasPrescription) {
            $text .= "\n\n⚠ Часть подходящих препаратов отпускается по рецепту — их может назначить только врач.";
        }

        return [
            'text' => $text,
            'products' => $matched->reject(fn (Product $p) => $p->prescription)->values(),
            'has_prescription_matches' => $hasPrescription,
        ];
    }

    /**
     * Сужаем каталог до релевантных позиций.
     * Ключевые слова из вопроса → LIKE по названию/показаниям, добиваем популярными.
     */
    private function catalogForPrompt(string $question): Collection
    {
        $limit = config('pharmacy.ollama.catalog_limit');

        $keywords = collect(preg_split('/\s+/u', mb_strtolower($question)))
            ->filter(fn ($w) => mb_strlen($w) >= 4)
            ->take(6);

        $relevant = collect();

        if ($keywords->isNotEmpty()) {
            $relevant = Product::query()
                ->overTheCounter()
                ->available()
                ->where(function ($q) use ($keywords) {
                    foreach ($keywords as $word) {
                        $q->orWhere('name', 'like', "%{$word}%")
                            ->orWhere('indications', 'like', "%{$word}%")
                            ->orWhere('short_description', 'like', "%{$word}%");
                    }
                })
                ->limit($limit)
                ->get();
        }

        if ($relevant->count() < $limit) {
            $filler = Product::query()
                ->overTheCounter()
                ->available()
                ->whereNotIn('id', $relevant->pluck('id'))
                ->orderBy('name')
                ->limit($limit - $relevant->count())
                ->get();

            $relevant = $relevant->concat($filler);
        }

        return $relevant;
    }

    private function buildSystemPrompt(Collection $products): string
    {
        $list = $products->map(function (Product $p) {
            $lines = ["- {$p->name} ({$p->price} руб.)"];

            foreach ([
                'Описание' => [$p->short_description, 100],
                'Способ применения' => [$p->usage_info, 150],
                'Состав' => [$p->composition, 150],
                'Противопоказания' => [$p->contraindications, 150],
            ] as $title => [$value, $length]) {
                if (filled($value)) {
                    $lines[] = '  ' . $title . ': ' . Str::limit($value, $length);
                }
            }

            return implode("\n", $lines);
        })->implode("\n\n");

        return <<<PROMPT
        Ты фармацевт-консультант интернет-аптеки. Отвечай только на русском языке, кратко и по делу (2-4 предложения).

        ВАЖНО: Ты можешь рекомендовать ТОЛЬКО товары из списка ниже. Не придумывай названия, которых нет в списке.
        Если подходящего товара нет в списке — НЕ добавляй блок ###MEDICINES### вообще, просто напиши текстом, что такого товара нет.
        Когда рекомендуешь товар — кратко упомяни способ применения и главные противопоказания, если они есть.

        === ТОВАРЫ В НАШЕМ КАТАЛОГЕ ===
        {$list}
        ================================

        Когда рекомендуешь товар — указывай его название ТОЧНО как в списке выше.
        В конце ответа ОБЯЗАТЕЛЬНО добавь блок строго в таком формате (только если нашёл подходящий товар):
        ###MEDICINES###
        ["Точное название товара 1","Точное название товара 2"]
        ###END###

        Если вопрос не про здоровье — ответь вежливо, что ты только фармацевт-консультант, блок ###MEDICINES### не добавляй.
        PROMPT;
    }

    private function callModel(string $systemPrompt, string $question): string
    {
        try {
            $response = Http::timeout(config('pharmacy.ollama.timeout'))
                ->acceptJson()
                ->post($this->url ?? config('pharmacy.ollama.url'), [
                    'model' => $this->model ?? config('pharmacy.ollama.model'),
                    'stream' => false,
                    'think' => false,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $question],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Ollama недоступна', ['error' => $e->getMessage()]);

            throw new OllamaUnavailableException(previous: $e);
        }

        if ($response->failed()) {
            throw new OllamaUnavailableException('Ollama ответила ' . $response->status());
        }

        return (string) $response->json('message.content', '');
    }

    /**
     * Отделяет текст ответа от блока с названиями товаров.
     *
     * @return array{0: string, 1: array<int, string>}
     */
    public function splitAnswer(string $raw): array
    {
        $names = [];
        $text = $raw;

        if (preg_match('/###MEDICINES###(.*?)(?:###END###|$)/s', $raw, $m)) {
            $text = preg_replace('/###MEDICINES###.*?(?:###END###|$)/s', '', $raw);
            $block = trim($m[1]);

            $decoded = json_decode($block, true);

            if (is_array($decoded)) {
                $names = $decoded;
            } else {
                preg_match_all('/"([^"]+)"|[\-\*]\s*(.+)/u', $block, $hits);
                $names = array_filter(array_map('trim', array_merge($hits[1], $hits[2])));
            }
        }

        // qwen любит оставлять следы рассуждений
        $text = preg_replace('/<think>.*?<\/think>/s', '', $text);
        $text = preg_replace('/###\w+###/s', '', $text);

        $names = array_values(array_filter(array_map(
            fn ($name) => trim((string) $name, " \t\n\r-*\"'"),
            $names
        )));

        return [trim($text), $names];
    }

    /** @param array<int, string> $names */
    private function matchProducts(array $names): Collection
    {
        $matched = collect();

        foreach ($names as $name) {
            $product = Product::query()->where('name', $name)->first()
                ?? Product::query()->where('name', 'like', '%' . $name . '%')->first();

            if ($product && ! $matched->contains('id', $product->id)) {
                $matched->push($product);
            }
        }

        return $matched;
    }
}
