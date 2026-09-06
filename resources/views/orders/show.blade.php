@extends('layouts.app')

@section('title', "Заказ №{$order->id}")

@section('content')
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Заказ №{{ $order->id }}</h1>
        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Все заказы</a>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <div class="text-muted" style="font-size:.78rem;letter-spacing:1.5px;">СТАТУС</div>
                <div class="fs-5 fw-semibold">{{ $order->status_label }}</div>
            </div>
            <div>
                <div class="text-muted" style="font-size:.78rem;letter-spacing:1.5px;">ОФОРМЛЕН</div>
                <div>{{ $order->created_at->format('d.m.Y H:i') }}</div>
            </div>
            <div>
                <div class="text-muted" style="font-size:.78rem;letter-spacing:1.5px;">СУММА</div>
                <div class="fs-5 fw-bold">{{ number_format($order->total, 0, '', ' ') }} ₽</div>
            </div>
        </div>

        @if ($order->status !== 'cancelled')
            <div class="progress mt-4" style="height:8px;">
                <div class="progress-bar" role="progressbar"
                     style="width:{{ $order->progress_percent }}%;background:#a6d175;"></div>
            </div>
        @endif
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="mb-3">Состав заказа</h5>
        <table class="table align-middle mb-0">
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td style="width:80px;">
                            <img src="{{ $item->product->image_url }}" width="60" height="60"
                                 alt="{{ $item->product->name }}" style="object-fit:contain;">
                        </td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">{{ $item->qty }} шт.</td>
                        <td class="text-end">{{ number_format($item->subtotal, 0, '', ' ') }} ₽</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="mb-3">Доставка</h5>
        <p class="mb-1">{{ $order->name }}, {{ $order->phone }}</p>
        <p class="text-muted mb-0">{{ $order->address }}</p>
    </div>

    @if ($order->isCancellable())
        <form method="POST" action="{{ route('orders.cancel', $order) }}"
              onsubmit="return confirm('Отменить заказ?')">
            @csrf
            <button class="btn btn-outline-danger">Отменить заказ</button>
        </form>
    @endif
</main>
@endsection
