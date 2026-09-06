<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    /** @return Collection<int, CartItem> */
    public function items(User $user): Collection
    {
        return CartItem::query()
            ->where('user_id', $user->id)
            ->with('product.brand')
            ->latest()
            ->get();
    }

    public function total(User $user): float
    {
        return (float) $this->items($user)->sum(fn (CartItem $item) => $item->subtotal);
    }

    public function count(User $user): int
    {
        return (int) CartItem::where('user_id', $user->id)->sum('qty');
    }

    /** Кладём товар в корзину, не превышая остаток на складе. */
    public function add(User $user, Product $product, int $qty = 1): CartItem
    {
        $item = CartItem::firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $item->qty = min(($item->qty ?? 0) + $qty, $product->stock);
        $item->save();

        return $item;
    }

    /** Плюс/минус из карточки и корзины. Возвращает новое количество. */
    public function changeQty(User $user, Product $product, string $action): int
    {
        $item = CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        $current = $item?->qty ?? 0;

        $new = $action === 'plus'
            ? min($current + 1, $product->stock)
            : max($current - 1, 0);

        if ($new === 0) {
            $item?->delete();

            return 0;
        }

        CartItem::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['qty' => $new],
        );

        return $new;
    }

    public function remove(User $user, CartItem $item): void
    {
        abort_unless($item->user_id === $user->id, 403);

        $item->delete();
    }

    public function clear(User $user): void
    {
        CartItem::where('user_id', $user->id)->delete();
    }
}
