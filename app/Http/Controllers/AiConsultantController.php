<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\OllamaConsultant;
use App\Services\OllamaUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Порт chat_api.php. */
class AiConsultantController extends Controller
{
    public function __construct(private readonly OllamaConsultant $consultant)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        try {
            $answer = $this->consultant->ask($data['message']);
        } catch (OllamaUnavailableException) {
            return response()->json([
                'error' => 'ollama_unavailable',
                'text' => 'Консультант временно недоступен. Попробуйте позже.',
            ], 503);
        }

        return response()->json([
            'text' => $answer['text'],
            'products' => $answer['products']->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'image' => $p->image_url,
                'url' => route('products.show', $p),
                'usage_info' => $p->usage_info,
                'composition' => $p->composition,
                'contraindications' => $p->contraindications,
            ]),
            'disclaimer' => 'ИИ-консультант не заменяет врача или фармацевта. При симптомах обратитесь к специалисту.',
        ]);
    }
}
