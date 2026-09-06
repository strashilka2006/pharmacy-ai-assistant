<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Порт product.php. */
class ProductController extends Controller
{
    public function show(Request $request, Product $product): View
    {
        $product->load(['brand', 'reviews.user']);

        $this->rememberViewed($request, $product);

        return view('products.show', [
            'product' => $product,
            'brandSimilar' => $this->brandSimilar($product),
            'recommended' => $this->recommended($product),
            'rating' => [
                'avg' => round((float) $product->reviews->avg('rating'), 1),
                'count' => $product->reviews->count(),
            ],
        ])->withCookie($this->viewedCookie($request, $product));
    }

    /** «Вы смотрели» — список id в куке, как и было. */
    private function rememberViewed(Request $request, Product $product): void
    {
        // сама кука ставится во withCookie(), метод оставлен для читаемости
    }

    private function viewedCookie(Request $request, Product $product)
    {
        $ids = array_filter(explode(',', (string) $request->cookie('viewed')));
        $ids = array_diff($ids, [(string) $product->id]);
        array_unshift($ids, (string) $product->id);
        $ids = array_slice($ids, 0, 20);

        return cookie('viewed', implode(',', $ids), 60 * 24 * 30);
    }

    private function brandSimilar(Product $product)
    {
        if (! $product->brand_id) {
            return collect();
        }

        return Product::query()
            ->where('brand_id', $product->brand_id)
            ->whereKeyNot($product->id)
            ->inRandomOrder()
            ->limit(6)
            ->get();
    }

    private function recommended(Product $product)
    {
        $exclude = $this->brandSimilar($product)->pluck('id')->push($product->id);

        // 1) та же категория
        if ($product->category_id) {
            $byCategory = Product::query()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($exclude)
                ->inRandomOrder()
                ->limit(6)
                ->get();

            if ($byCategory->isNotEmpty()) {
                return $byCategory;
            }
        }

        // 2) похожая цена ±30%
        $byPrice = Product::query()
            ->whereBetween('price', [(float) $product->price * 0.7, (float) $product->price * 1.3])
            ->whereKeyNot($exclude)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        if ($byPrice->isNotEmpty()) {
            return $byPrice;
        }

        // 3) что угодно
        return Product::query()
            ->whereKeyNot($exclude)
            ->inRandomOrder()
            ->limit(6)
            ->get();
    }
}
