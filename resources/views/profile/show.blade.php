@extends('layouts.app')

@section('title', 'Профиль')

@section('content')
<main class="container" style="max-width:1380px;padding:56px 24px 80px;">

    <div style="border-bottom:1.5px solid #d4edaa;padding-bottom:28px;margin-bottom:40px;">
        <div style="font-family:'Fragment Mono',monospace;font-size:11px;color:#7AAD3F;letter-spacing:2px;margin-bottom:12px;">
            ЛИЧНЫЙ КАБИНЕТ
        </div>
        <h1 style="font-family:'Fragment Mono',monospace;font-weight:400;color:#1a3a0a;font-size:clamp(1.5rem,4vw,2rem);margin:0;">
            {{ $user->name ?? $user->email }}
        </h1>
    </div>

    {{-- Аватар --}}
    <section style="border:1.5px solid #d4edaa;border-radius:12px;background:#fff;padding:28px;margin-bottom:28px;">
        <div style="font-family:'Fragment Mono',monospace;font-size:11px;color:#7AAD3F;letter-spacing:2px;margin-bottom:20px;">
            ФОТО
        </div>

        <div class="d-flex align-items-center gap-4 flex-wrap">
            <img src="{{ $user->avatarUrl() }}" alt=""
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:2px solid #d4edaa;">

            <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data"
                  class="d-flex align-items-center gap-2 flex-wrap">
                @csrf
                <input type="file" name="avatar" accept="image/*" class="form-control" style="max-width:280px;">
                <button class="btn btn-outline-custom">Загрузить</button>
            </form>
        </div>
    </section>

    {{-- Данные --}}
    <section style="border:1.5px solid #d4edaa;border-radius:12px;background:#fff;padding:28px;margin-bottom:28px;">
        <div style="font-family:'Fragment Mono',monospace;font-size:11px;color:#7AAD3F;letter-spacing:2px;margin-bottom:20px;">
            ДАННЫЕ
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Имя</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Телефон</label>
                    <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Адрес</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                </div>
            </div>

            <div class="mt-3 text-muted" style="font-size:.82rem;">Почта: {{ $user->email }}</div>

            <button class="btn btn-outline-custom mt-3">Сохранить</button>
        </form>
    </section>

    {{-- Заказы --}}
    <section style="border:1.5px solid #d4edaa;border-radius:12px;background:#fff;padding:28px;margin-bottom:28px;">
        <div style="font-family:'Fragment Mono',monospace;font-size:11px;color:#7AAD3F;letter-spacing:2px;margin-bottom:20px;">
            ЗАКАЗЫ
        </div>

        @if ($orders->isEmpty())
            <p class="text-muted mb-0">Заказов пока нет.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr class="text-muted" style="font-size:.8rem;">
                            <th>Номер</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th class="text-end">Сумма</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="fw-bold">№{{ $order->id }}</td>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @php
                                        $color = match ($order->status) {
                                            'cancelled' => '#c0392b',
                                            'ready_for_pickup', 'delivered' => '#2d5a1b',
                                            default => '#555',
                                        };
                                    @endphp
                                    <span style="color:{{ $color }};">{{ $order->status_label }}</span>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($order->total, 0, '', ' ') }} ₽</td>
                                <td class="text-end">
                                    <a href="{{ route('orders.show', $order) }}"
                                       class="btn btn-sm btn-outline-custom">Подробнее</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Просмотренное --}}
    @if ($viewed->isNotEmpty())
        <section style="border:1.5px solid #d4edaa;border-radius:12px;background:#fff;padding:28px;">
            <div style="font-family:'Fragment Mono',monospace;font-size:11px;color:#7AAD3F;letter-spacing:2px;margin-bottom:20px;">
                ВЫ НЕДАВНО СМОТРЕЛИ
            </div>

            <div class="row g-4">
                @foreach ($viewed as $product)
                    <div class="col-6 col-md-3 col-lg-2">
                        @include('partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</main>
@endsection
