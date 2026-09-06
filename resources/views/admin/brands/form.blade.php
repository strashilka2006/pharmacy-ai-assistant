@extends('layouts.admin')

@section('title', $brand->exists ? 'Редактирование бренда' : 'Новый бренд')

@section('content')
<a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary rounded-pill mb-4">← Все бренды</a>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-dark text-white py-3">
        <h1 class="h5 mb-0">{{ $brand->exists ? 'Редактирование: ' . $brand->name : 'Добавление бренда' }}</h1>
    </div>

    <div class="card-body p-4">
        <form method="POST"
              action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($brand->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Название *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $brand->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Описание</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $brand->description) }}</textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Логотип</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    @if ($brand->logo_url)
                        <img src="{{ $brand->logo_url }}" class="mt-2 rounded" style="height:70px;object-fit:contain;">
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Баннер для главной</label>
                    <input type="file" name="banner" class="form-control" accept="image/*">
                    <div class="form-text">Если баннер загружен, бренд появится в карусели на главной.</div>
                    @if ($brand->banner_url)
                        <img src="{{ $brand->banner_url }}" class="mt-2 rounded" style="height:70px;object-fit:cover;">
                    @endif
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-dark px-4 rounded-pill">Сохранить</button>
                <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary rounded-pill">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
