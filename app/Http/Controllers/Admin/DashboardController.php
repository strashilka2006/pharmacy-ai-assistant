<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::count(),
                'out_of_stock' => Product::where('stock', '<=', 0)->count(),
                'brands' => Brand::count(),
                'users' => User::count(),
                'orders_today' => Order::whereDate('created_at', today())->count(),
            ],
        ]);
    }
}
