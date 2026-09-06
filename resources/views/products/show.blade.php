@extends('layouts.app')

@section('title', $product->name)

@section('content')
<main class="container py-5">
    <div class="row g-5">
        <div class="col-lg-5">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                 class="img-fluid rounded shadow-sm w-100"
                 style="max-height:480px;object-fit:contain;background:#fafafa;padding:2rem;">
        </div>

        <div class="col-lg-7">
            @if ($product->brand)
                <a href="{{ route('brands.show', $product->brand) }}" class="text-decoration-none"
                   style="color:#7AAD3F;font-size:.8rem;letter-spacing:2px;">
                    {{ mb_strtoupper($product->brand->name) }}
                </a>
            @endif

            <h1 class="h2 mt-2 mb-3">{{ $product->name }}</h1>

            @if ($rating['count'] > 0)
                <div class="mb-3 text-muted" style="font-size:.9rem;">
                    Рейтинг {{ $rating['avg'] }} из 5 · {{ $rating['count'] }} отз.
                </div>
            @endif

            <div class="fs-2 fw-bold mb-3">{{ number_format($product->price, 0, '', ' ') }} ₽</div>

            @if ($product->label_text)
                <div class="alert" style="background:#f8fef3;border:1.5px solid #d4edaa;color:#2d5a1b;">
                    {{ $product->label_text }}
                </div>
            @endif

            @if ($product->prescription)
                <div class="alert alert-warning">
                    Препарат отпускается по рецепту врача.
                </div>
            @endif

            @if ($product->short_description)
                <p class="text-muted">{{ $product->short_description }}</p>
            @endif

            <div class="mb-4">
                @if ($product->in_stock)
                    <span class="badge bg-success">В наличии: {{ $product->stock }} шт.</span>
                @else
                    <span class="badge bg-danger">Нет в наличии</span>
                @endif
            </div>

            @auth
                @if ($product->in_stock)
                    <form method="POST" action="{{ route('cart.store', $product) }}">
                        @csrf
                        <button class="btn btn-lg px-5" style="background:#a6d175;color:#fff;border-radius:24px;">
                            Добавить в корзину
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-lg px-5"
                   style="background:#a6d175;color:#fff;border-radius:24px;">
                    Войдите, чтобы купить
                </a>
            @endauth
        </div>
    </div>

    {{-- Подробности: показываем только заполненные блоки --}}
    @php
        $details = collect([
            'Описание' => $product->description,
            'Подробнее' => $product->long_description,
            'Показания' => $product->indications,
            'Состав' => $product->composition,
            'Способ применения' => $product->usage_info,
            'Противопоказания' => $product->contraindications,
            'Взаимодействие с другими препаратами' => $product->drug_interactions,
            'Передозировка' => $product->overdose,
        ])->filter();
    @endphp

    @if ($details->isNotEmpty())
        <div class="accordion mt-5" id="productDetails">
            @foreach ($details as $title => $text)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button @if (! $loop->first) collapsed @endif" type="button"
                                data-bs-toggle="collapse" data-bs-target="#detail{{ $loop->index }}">
                            {{ $title }}
                        </button>
                    </h2>
                    <div id="detail{{ $loop->index }}"
                         class="accordion-collapse collapse @if ($loop->first) show @endif"
                         data-bs-parent="#productDetails">
                        <div class="accordion-body" style="white-space:pre-line;">{{ $text }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($brandSimilar->isNotEmpty())
        <section class="mt-5">
            <h3 class="mb-4">Другие товары бренда</h3>
            <div class="row g-4">
                @foreach ($brandSimilar as $item)
                    <div class="col-6 col-md-4 col-lg-2">
                        @include('partials.product-card', ['product' => $item])
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($recommended->isNotEmpty())
        <section class="mt-5">
            <h3 class="mb-4">Вам может подойти</h3>
            <div class="row g-4">
                @foreach ($recommended as $item)
                    <div class="col-6 col-md-4 col-lg-2">
                        @include('partials.product-card', ['product' => $item])
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</main>
@endsection
