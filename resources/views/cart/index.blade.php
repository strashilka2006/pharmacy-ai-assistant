@extends('layouts.app')

@section('title', 'Корзина и оформление заказа')

@section('content')
<main class="container py-5">
    <h1 class="mb-5">Корзина и оформление заказа</h1>

    @if ($items->isEmpty())
        <div class="text-center py-5">
            <h3 class="text-muted">Корзина пуста</h3>
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary mt-3">Перейти в каталог</a>
        </div>
    @else
        <div class="row g-5">
            <div class="col-lg-8">
                <h3 class="mb-4">Ваши товары</h3>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Фото</th>
                                <th>Товар</th>
                                <th class="text-center">Количество</th>
                                <th class="text-end">Цена</th>
                                <th class="text-end">Сумма</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr data-cart-row>
                                    <td>
                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                                             class="rounded shadow-sm" width="90" height="90" style="object-fit:contain;">
                                    </td>
                                    <td class="fw-semibold">
                                        <a href="{{ route('products.show', $item->product) }}" class="text-decoration-none text-dark">
                                            {{ $item->product->name }}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3"
                                                    data-qty-action="minus" data-product-id="{{ $item->product_id }}">–</button>
                                            <span class="fw-bold fs-5 mx-3" data-qty-value>{{ $item->qty }}</span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3"
                                                    data-qty-action="plus" data-product-id="{{ $item->product_id }}">+</button>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($item->product->price, 0, '', ' ') }} ₽</td>
                                    <td class="text-end fw-bold fs-5" data-subtotal>{{ number_format($item->subtotal, 0, '', ' ') }} ₽</td>
                                    <td>
                                        <form method="POST" action="{{ route('cart.destroy', $item) }}"
                                              onsubmit="return confirm('Удалить товар?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <h2>Итого: <span data-cart-total>{{ number_format($total, 0, '', ' ') }} ₽</span></h2>
                </div>
            </div>

            <div class="col-lg-4">
                <h3 class="mb-4">Данные для доставки</h3>

                <div class="card p-4 border-0 shadow-sm">
                    <form method="POST" action="{{ route('checkout.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ФИО *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Телефон *</label>
                            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Адрес доставки *</label>
                            <textarea name="address" rows="3" required
                                      class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button class="btn w-100 py-3" style="background:#a6d175;color:#fff;">
                            Оформить заказ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</main>
@endsection

@push('scripts')
<script>window.CART_QTY_URL = @json(route('cart.qty'));</script>
@endpush
