<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

/** Порт POST-ветки cart.php. */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly CartService $cart,
    ) {
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->items($request->user())->isEmpty()) {
            return back()->with('error', 'Корзина пуста');
        }

        try {
            $order = $this->checkout->place(
                $request->user(),
                $request->validated(),
                route('payment.return', ['order' => '{order}']),
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('payment.pending', $order);
    }
}
