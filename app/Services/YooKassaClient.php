<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Порт curl-вызовов из cart.php и payment_return.php.
 */
class YooKassaClient
{
    private function request()
    {
        return Http::withBasicAuth(
            (string) config('pharmacy.yookassa.shop_id'),
            (string) config('pharmacy.yookassa.secret_key'),
        )->acceptJson()->timeout(30);
    }

    /**
     * Создаёт платёж. Возвращает null, если ЮKassa не отдала ссылку.
     *
     * @return array{id: string, confirmation_url: string}|null
     */
    public function createPayment(Order $order, string $returnUrl): ?array
    {
        try {
            $response = $this->request()
                ->withHeaders([
                    'Idempotence-Key' => 'order_' . $order->id . '_' . Str::uuid(),
                ])
                ->post(config('pharmacy.yookassa.api_url') . '/payments', [
                    'amount' => [
                        'value' => number_format((float) $order->total, 2, '.', ''),
                        'currency' => 'RUB',
                    ],
                    'capture' => true,
                    'confirmation' => [
                        'type' => 'redirect',
                        'return_url' => $returnUrl,
                    ],
                    'description' => "Заказ №{$order->id} — Аптека",
                    'metadata' => ['order_id' => $order->id],
                ]);
        } catch (\Throwable $e) {
            Log::error('ЮKassa: не удалось создать платёж', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        $url = $response->json('confirmation.confirmation_url');
        $id = $response->json('id');

        if (! $url || ! $id) {
            Log::error('ЮKassa: ответ без ссылки на оплату', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        return ['id' => $id, 'confirmation_url' => $url];
    }

    /** Статус платежа: succeeded / canceled / pending / unknown. */
    public function paymentStatus(string $paymentId): string
    {
        try {
            $response = $this->request()
                ->get(config('pharmacy.yookassa.api_url') . '/payments/' . $paymentId);
        } catch (\Throwable $e) {
            Log::warning('ЮKassa: не удалось получить статус', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return 'unknown';
        }

        return (string) $response->json('status', 'unknown');
    }

    /** Проверка, что ссылка на оплату ведёт куда надо (было в payment_pending.php). */
    public function isTrustedPayUrl(?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $parts = parse_url($url);

        return isset($parts['scheme'], $parts['host'])
            && strtolower($parts['scheme']) === 'https'
            && in_array(strtolower($parts['host']), config('pharmacy.yookassa.allowed_pay_hosts'), true);
    }
}
