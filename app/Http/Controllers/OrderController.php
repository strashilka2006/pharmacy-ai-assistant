<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Порт order_view.php и order_success.php. */
class OrderController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout)
    {
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403, 'Заказ не найден или доступ запрещён');

        $order->load('items.product');

        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isCancellable()) {
            return back()->with('error', 'Этот заказ уже нельзя отменить');
        }

        $this->checkout->cancel($order);

        return back()->with('status', 'Заказ отменён, товары вернулись на склад');
    }
}
