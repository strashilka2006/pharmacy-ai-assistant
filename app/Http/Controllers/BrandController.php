<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\View\View;

/** Порт brand.php. */
class BrandController extends Controller
{
    public function show(Brand $brand): View
    {
        $products = $brand->products()
            ->orderBy('name')
            ->paginate(24);

        return view('brands.show', compact('brand', 'products'));
    }
}
