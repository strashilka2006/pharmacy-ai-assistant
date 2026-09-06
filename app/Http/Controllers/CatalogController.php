<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Порт index.php и catalog_ajax.php. */
class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'brand', 'price_min', 'price_max']);

        $products = Product::query()
            ->with('brand')
            ->filter($filters)
            ->sorted($request->string('sort')->toString())
            ->paginate(24)
            ->withQueryString();

        // Сколько каждого товара уже в корзине — одним запросом.
        // В оригинале это был отдельный SELECT внутри цикла по товарам.
        $cartQty = auth()->check()
            ? \App\Models\CartItem::where('user_id', auth()->id())
                ->pluck('qty', 'product_id')
                ->all()
            : [];

        return view('catalog.index', [
            'products' => $products,
            'cartQty' => $cartQty,
            'brands' => Brand::orderBy('name')->get(),
            'bannerSlides' => $this->bannerSlides(),
            'filters' => $filters,
            'sort' => $request->string('sort', 'new')->toString(),
        ]);
    }

    /** Тот же список, но JSON — для живой фильтрации на главной. */
    public function ajax(Request $request): JsonResponse
    {
        $products = Product::query()
            ->select('products.id', 'products.name', 'products.price', 'products.image', 'products.stock')
            ->with('brand:id,name')
            ->filter($request->only(['q', 'brand', 'price_min', 'price_max']))
            ->sorted($request->string('sort')->toString())
            ->limit(60)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'image' => $p->image_url,
                'brand_name' => $p->brand?->name,
                'url' => route('products.show', $p),
            ]);

        return response()->json($products);
    }

    /**
     * Баннеры брендов на главной.
     * В оригинале это был запрос в цикле — по одному SELECT на каждый бренд.
     * Здесь один eager-load с ограничением по количеству товаров.
     */
    private function bannerSlides()
    {
        return Brand::query()
            ->withBanner()
            ->with(['products' => fn ($q) => $q->latest('id')->limit(4)])
            ->get()
            ->filter(fn (Brand $brand) => $brand->products->isNotEmpty())
            ->values();
    }
}
