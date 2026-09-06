@extends('layouts.admin')

@section('title', 'Товары')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="h3 mb-1">Все товары</h1>
        <p class="text-muted mb-0">Управление каталогом аптеки</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-dark px-4 py-2 rounded-pill">Добавить товар</a>
</div>

<form method="GET" class="mb-4">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control w-auto d-inline-block"
           placeholder="Поиск по названию" style="min-width:280px;">
</form>

@if ($products->isEmpty())
    <div class="text-center py-5 bg-white rounded-4">
        <h3 class="text-muted mb-4">Товаров пока нет</h3>
        <a href="{{ route('admin.products.create') }}" class="btn btn-dark rounded-pill px-4">Добавить первый товар</a>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white rounded-4 shadow-sm overflow-hidden">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Фото</th>
                    <th>Название</th>
                    <th>Бренд</th>
                    <th>Цена</th>
                    <th>На складе</th>
                    <th class="text-end pe-4">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $product->id }}</td>
                        <td>
                            <img src="{{ $product->image_url }}" alt="" class="rounded"
                                 style="width:60px;height:60px;object-fit:cover;">
                        </td>
                        <td class="fw-semibold">{{ $product->name }}</td>
                        <td class="text-muted">{{ $product->brand?->name ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($product->price, 0, '', ' ') }} ₽</td>
                        <td>
                            <span class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $product->stock }} шт.
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="btn btn-outline-primary btn-sm rounded-pill px-3">Редактировать</a>

                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Удалить товар «{{ $product->name }}»?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-3 ms-2">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
@endif
@endsection
