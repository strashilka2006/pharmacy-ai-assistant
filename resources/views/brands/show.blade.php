@extends('layouts.app')

@section('title', $brand->name . ' — официальный бренд')

@section('content')
<main class="container py-5">
    @if ($brand->banner_url)
        <img src="{{ $brand->banner_url }}" alt="{{ $brand->name }}"
             class="w-100 rounded-4 mb-5" style="max-height:320px;object-fit:cover;">
    @endif

    <div class="row g-5">
        <div class="col-lg-4">
            @if ($brand->logo_url)
                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                     class="img-fluid rounded shadow-sm mb-4">
            @endif

            <h1 class="h2">{{ $brand->name }}</h1>

            @if ($brand->description)
                <p class="text-muted" style="white-space:pre-line;">{{ $brand->description }}</p>
            @endif
        </div>

        <div class="col-lg-8">
            <h2 class="h4 mb-4">Все товары бренда ({{ $products->total() }})</h2>

            @if ($products->isEmpty())
                <p class="text-muted">Товаров этого бренда пока нет.</p>
            @else
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-6 col-md-4">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</main>
@endsection
