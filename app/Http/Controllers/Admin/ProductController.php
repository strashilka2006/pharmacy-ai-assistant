<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Порт admin/products.php, product_add.php, product_edit.php, product_delete.php. */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('brand')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'brands' => Brand::orderBy('name')->get(),
            'labels' => Product::LABELS,
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($this->payload($request));

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Товар добавлен');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'brands' => Brand::orderBy('name')->get(),
            'labels' => Product::LABELS,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($this->payload($request, $product));

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Товар обновлён');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $product->delete();
            $message = 'Товар удалён';
        } catch (QueryException $e) {
            // Товар есть в оформленных заказах — внешний ключ не даст удалить.
            // Логика из оригинала: снимаем с продажи вместо удаления.
            $product->update(['stock' => 0]);
            $message = 'Товар есть в заказах — удалить нельзя. Остаток обнулён, из продажи снят.';
        }

        return redirect()->route('admin.products.index')->with('status', $message);
    }

    /** Картинка: либо загруженный файл, либо внешний URL. */
    private function payload(ProductRequest $request, ?Product $product = null): array
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($product?->image && ! str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete('products/' . $product->image);
            }

            $data['image'] = basename($request->file('photo')->store('products', 'public'));
        } elseif (filled($data['photo_url'] ?? null)) {
            $data['image'] = $data['photo_url'];
        }

        unset($data['photo'], $data['photo_url']);

        return $data;
    }
}
