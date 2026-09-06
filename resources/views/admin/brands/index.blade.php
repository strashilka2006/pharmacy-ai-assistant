@extends('layouts.admin')

@section('title', 'Бренды')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="h3 mb-1">Бренды</h1>
        <p class="text-muted mb-0">Логотипы, описания и баннеры на главной</p>
    </div>
    <a href="{{ route('admin.brands.create') }}" class="btn btn-dark px-4 py-2 rounded-pill">Добавить бренд</a>
</div>

@if ($brands->isEmpty())
    <div class="text-center py-5 bg-white rounded-4">
        <h3 class="text-muted mb-4">Брендов пока нет</h3>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-dark rounded-pill px-4">Добавить первый</a>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white rounded-4 shadow-sm overflow-hidden">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Логотип</th>
                    <th>Название</th>
                    <th>Товаров</th>
                    <th>Баннер</th>
                    <th class="text-end pe-4">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $brand->id }}</td>
                        <td>
                            @if ($brand->logo_url)
                                <img src="{{ $brand->logo_url }}" class="rounded"
                                     style="width:56px;height:56px;object-fit:contain;background:#fafafa;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $brand->name }}</td>
                        <td>{{ $brand->products_count }}</td>
                        <td>
                            @if ($brand->banner)
                                <span class="badge bg-success">есть</span>
                            @else
                                <span class="badge bg-secondary">нет</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.brands.edit', $brand) }}"
                               class="btn btn-outline-primary btn-sm rounded-pill px-3">Редактировать</a>

                            <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" class="d-inline"
                                  onsubmit="return confirm('Удалить бренд «{{ $brand->name }}»? Товары останутся, но потеряют бренд.')">
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

    {{ $brands->links() }}
@endif
@endsection
