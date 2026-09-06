<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\YooKassaClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Порт payment_pending.php и payment_return.php. */
class PaymentController extends Controller
{
    public function __construct(private readonly YooKassaClient $yooKassa)
    {
    }

    /** Страница с QR-кодом и кнопкой оплаты. */
    public function pending(Request $request, Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if (in_array($order->status, ['paid', 'cancelled'], true)) {
            return redirect()->route('orders.show', $order);
        }

        if (! $this->yooKassa->isTrustedPayUrl($order->pay_url)) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Ссылка на оплату недоступна. Свяжитесь с поддержкой.');
        }

        return view('orders.payment-pending', compact('order'));
    }

    /**
     * Возврат из ЮKassa.
     *
     * Внимание: это НЕ источник правды об оплате — пользователь может просто
     * не вернуться на сайт. Для продакшена нужен вебхук ЮKassa на отдельный
     * маршрут (см. README, раздел «Что осталось доделать»).
     */
    public function return(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if (blank($order->payment_id)) {
            return redirect()->route('orders.show', $order);
        }

        $status = $this->yooKassa->paymentStatus($order->payment_id);

        match ($status) {
            'succeeded' => $order->update(['status' => 'paid']),
            'canceled' => $order->update(['status' => 'cancelled']),
            default => null,
        };

        return redirect()
            ->route('orders.show', $order)
            ->with('payment_status', $status);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
    }
}
