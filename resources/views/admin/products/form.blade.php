@extends('layouts.admin')

@section('title', $product->exists ? 'Редактирование товара' : 'Новый товар')

@section('content')
<a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill mb-4">← Все товары</a>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-dark text-white py-3">
        <h1 class="h5 mb-0">{{ $product->exists ? 'Редактирование: ' . $product->name : 'Добавление товара' }}</h1>
    </div>

    <div class="card-body p-4">
        <form method="POST"
              action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if ($product->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Название *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Цена, ₽ *</label>
                    <input type="text" name="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $product->price) }}" required>
                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Остаток *</label>
                    <input type="number" name="stock" class="form-control"
                           value="{{ old('stock', $product->stock ?? 0) }}" min="0" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Бренд</label>
                    <select name="brand_id" class="form-select">
                        <option value="">— Не выбран —</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Ярлык</label>
                    <select name="label" class="form-select">
                        <option value="">— Нет —</option>
                        @foreach ($labels as $key => $title)
                            <option value="{{ $key }}" @selected(old('label', $product->label) === $key)>{{ $title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Поставщик</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $product->supplier) }}">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="prescription" value="1" id="rx" class="form-check-input"
                               @checked(old('prescription', $product->prescription))>
                        <label for="rx" class="form-check-label">
                            Отпускается по рецепту
                            <span class="text-muted d-block" style="font-size:.8rem;">
                                Такие товары ИИ-консультант не рекомендует и не показывает карточкой
                            </span>
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Фото (файл)</label>
                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                    @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if ($product->image)
                        <img src="{{ $product->image_url }}" class="mt-2 rounded" style="height:80px;object-fit:contain;">
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Либо ссылка на фото</label>
                    <input type="url" name="photo_url" class="form-control" placeholder="https://...">
                </div>

                @foreach ([
                    'short_description' => 'Краткое описание',
                    'description' => 'Описание',
                    'long_description' => 'Подробное описание',
                    'usage_info' => 'Способ применения',
                    'indications' => 'Показания',
                    'composition' => 'Состав',
                    'contraindications' => 'Противопоказания',
                    'drug_interactions' => 'Взаимодействие с другими препаратами',
                    'overdose' => 'Передозировка',
                ] as $field => $title)
                    <div class="col-12">
                        <label class="form-label fw-semibold">{{ $title }}</label>
                        <textarea name="{{ $field }}" rows="3" class="form-control">{{ old($field, $product->{$field}) }}</textarea>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-dark px-4 rounded-pill">Сохранить</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-pill">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
