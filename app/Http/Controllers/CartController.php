<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Порт cart.php, add_to_cart.php, remove_from_cart.php, ajax_qty.php. */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(Request $request): View
    {
        return view('cart.index', [
            'items' => $this->cart->items($request->user()),
            'total' => $this->cart->total($request->user()),
            'user' => $request->user(),
        ]);
    }

    /**
     * Добавление в корзину.
     * В оригинале это был GET-запрос без CSRF — то есть любая картинка
     * <img src=".../add_to_cart.php?id=5"> на стороннем сайте набивала
     * пользователю корзину. Теперь POST.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        if (! $product->in_stock) {
            return back()->with('error', 'Товара нет в наличии');
        }

        $this->cart->add($request->user(), $product);

        return back()->with('status', "«{$product->name}» в корзине");
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->cart->remove($request->user(), $item);

        return back()->with('status', 'Товар удалён из корзины');
    }

    /** Кнопки «+» и «−». */
    public function updateQty(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'action' => ['required', 'in:plus,minus'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $newQty = $this->cart->changeQty($request->user(), $product, $data['action']);

        return response()->json([
            'success' => true,
            'new_qty' => $newQty,
            'max' => $product->stock,
            'subtotal' => $newQty * (float) $product->price,
            'cart_total' => $this->cart->total($request->user()),
        ]);
    }
}
