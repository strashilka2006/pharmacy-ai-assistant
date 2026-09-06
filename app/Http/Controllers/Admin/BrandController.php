<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Порт admin/brands.php и brand_edit.php. */
class BrandController extends Controller
{
    public function index(): View
    {
        return view('admin.brands.index', [
            'brands' => Brand::withCount('products')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.brands.form', ['brand' => new Brand()]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        Brand::create($this->payload($request));

        return redirect()->route('admin.brands.index')->with('status', 'Бренд добавлен');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.form', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($this->payload($request, $brand));

        return redirect()->route('admin.brands.index')->with('status', 'Бренд обновлён');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('status', 'Бренд удалён');
    }

    private function payload(BrandRequest $request, ?Brand $brand = null): array
    {
        $data = $request->validated();

        foreach (['logo', 'banner'] as $field) {
            if ($request->hasFile($field)) {
                if ($brand?->{$field}) {
                    Storage::disk('public')->delete('brands/' . $brand->{$field});
                }

                $data[$field] = basename($request->file($field)->store('brands', 'public'));
            } else {
                unset($data[$field]);
            }
        }

        return $data;
    }
}
