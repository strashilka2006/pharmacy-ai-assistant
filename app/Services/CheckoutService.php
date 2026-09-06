<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Оформление заказа. Порт POST-ветки cart.php.
 *
 * Здесь закрыты две дыры оригинала:
 *  1) остатки на складе не списывались вообще — можно было заказать
 *     20 упаковок при stock = 1;
 *  2) сумма считалась из корзины без блокировки строк, то есть при двух
 *     параллельных запросах заказ мог уехать с неправильным total.
 */
class CheckoutService
{
    public function __construct(private readonly YooKassaClient $yooKassa)
    {
    }

    /**
     * @param array{name: string, phone: string, address: string} $contacts
     *
     * @throws RuntimeException
     */
    public function place(User $user, array $contacts, string $returnUrlTemplate): Order
    {
        $order = DB::transaction(function () use ($user, $contacts) {
            /** @var \Illuminate\Support\Collection<int, CartItem> $items */
            $items = CartItem::query()
                ->where('user_id', $user->id)
                ->with('product')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new RuntimeException('Корзина пуста');
            }

            $total = 0.0;

            foreach ($items as $item) {
                $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();

                if (! $product) {
                    throw new RuntimeException('Товар из корзины больше не продаётся');
                }

                if ($product->stock < $item->qty) {
                    throw new RuntimeException(
                        "«{$product->name}»: в наличии осталось {$product->stock} шт."
                    );
                }

                $total += (float) $product->price * $item->qty;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total' => $total,
                'status' => 'pending_payment',
                'name' => $contacts['name'],
                'phone' => $contacts['phone'],
                'address' => $contacts['address'],
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'price' => $item->product->price,
                ]);

                Product::whereKey($item->product_id)->decrement('stock', $item->qty);
            }

            return $order;
        });

        $payment = $this->yooKassa->createPayment(
            $order,
            str_replace('{order}', (string) $order->id, $returnUrlTemplate)
        );

        if (! $payment) {
            // Платёж не создался — откатываем заказ и возвращаем остатки.
            $this->releaseOrder($order);

            throw new RuntimeException('Не удалось создать платёж. Попробуйте ещё раз.');
        }

        $order->update([
            'payment_id' => $payment['id'],
            'pay_url' => $payment['confirmation_url'],
        ]);

        // Корзину чистим только после успешного создания платежа —
        // эта логика в оригинале была правильной, сохраняем.
        CartItem::where('user_id', $user->id)->delete();

        return $order;
    }

    /** Отмена заказа с возвратом остатков на склад. */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                Product::whereKey($item->product_id)->increment('stock', $item->qty);
            }

            $order->update(['status' => 'cancelled']);
        });
    }

    private function releaseOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                Product::whereKey($item->product_id)->increment('stock', $item->qty);
            }

            $order->items()->delete();
            $order->delete();
        });
    }
}
