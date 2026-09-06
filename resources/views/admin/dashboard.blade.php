@extends('layouts.admin')

@section('title', 'Панель администратора')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">Панель администратора</h1>
    <p class="text-muted mb-0">Управление интернет-аптекой</p>
</div>

<div class="row g-3 mb-5">
    @foreach ([
        'Товаров' => $stats['products'],
        'Нет в наличии' => $stats['out_of_stock'],
        'Брендов' => $stats['brands'],
        'Пользователей' => $stats['users'],
        'Заказов сегодня' => $stats['orders_today'],
    ] as $label => $value)
        <div class="col-6 col-lg">
            <div class="bg-white rounded-4 shadow-sm p-3 h-100">
                <div class="text-muted" style="font-size:.75rem;">{{ $label }}</div>
                <div class="fs-3 fw-bold">{{ $value }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    @foreach ([
        ['Товары', 'Просмотр, добавление и редактирование каталога.', route('admin.products.index')],
        ['Бренды', 'Управление брендами, логотипами и баннерами.', route('admin.brands.index')],
        ['На сайт', 'Перейти на главную страницу для клиентов.', route('catalog.index')],
    ] as [$title, $text, $url])
        <div class="col-md-4">
            <a href="{{ $url }}" class="text-decoration-none text-dark">
                <div class="bg-white rounded-4 shadow-sm p-4 h-100">
                    <h3 class="h5 mb-2">{{ $title }}</h3>
                    <p class="text-muted mb-0">{{ $text }}</p>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
