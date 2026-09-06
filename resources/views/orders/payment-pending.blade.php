@extends('layouts.app')

@section('title', "Оплата заказа №{$order->id}")

@section('content')
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow p-4 border-0">
                <h2 class="mb-2">Оплата заказа №{{ $order->id }}</h2>
                <p class="text-muted mb-4">Отсканируйте QR-код или нажмите кнопку для оплаты</p>

                <div id="qrcode" class="d-flex justify-content-center mb-4"></div>

                <a href="{{ $order->pay_url }}" rel="noopener" class="btn btn-success btn-lg w-100 mb-3">
                    Перейти к оплате
                </a>
                <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100">Назад в корзину</a>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrcode'), {
        text: @json($order->pay_url),
        width: 220,
        height: 220,
        correctLevel: QRCode.CorrectLevel.H,
    });
</script>
@endpush
