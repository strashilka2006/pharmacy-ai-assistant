@extends('layouts.app')

@section('title', 'Аптека — здоровье в надёжных руках')

@section('content')

{{-- ═══ HERO ═══ --}}
<section class="hero position-relative text-white text-center d-flex align-items-center justify-content-center"
         style="background:linear-gradient(rgba(216,221,140,.23),rgba(0,0,0,0)),url('{{ asset('images/hero.jpg') }}') center/cover no-repeat;height:70vh;min-height:520px;">
    <div class="container">
        <h1 class="display-3 fw-bold mb-4">ЗДОРОВЬЕ НАЧИНАЕТСЯ ЗДЕСЬ</h1>
        <p class="lead fs-2 mb-4">БОЛЕЕ 5000 ТОВАРОВ ДЛЯ ВАШЕГО ЗДОРОВЬЯ С ДОСТАВКОЙ ПО ВСЕЙ РОССИИ</p>

        @include('partials.brands-marquee')

        <a href="#catalog" class="btn btn-lg px-5 py-2 btn-hero-outline">ПЕРЕЙТИ В КАТАЛОГ</a>
    </div>
</section>

{{-- ═══ AI-ВИДЖЕТ ═══ --}}
<div class="container my-5">
    <div class="rounded-4 p-4 p-md-5 shadow-sm"
         style="background:linear-gradient(135deg,#f7ffee 0%,#f0f8e8 100%);border:1px solid #c8e8a0;">
        <div class="row align-items-center g-4">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:46px;height:46px;background:#a6d175;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/><path d="M12 16v-4m0-4h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" style="color:#2d5a1b;">AI&#8209;консультант</h5>
                        <small class="text-muted">Опишите симптомы — подберём препарат</small>
                    </div>
                </div>
                <p class="text-muted small mb-0">
                    Искусственный интеллект анализирует симптомы и находит подходящие препараты из нашего каталога
                </p>
            </div>

            <div class="col-md-7">
                <form id="aiQuickForm" class="d-flex gap-2">
                    <input type="text" id="aiQuickInput" class="form-control form-control-lg rounded-3"
                           placeholder="Например: болит голова и температура..."
                           style="border:2px solid #c5dfa0;font-size:.95rem;background:#fff;" autocomplete="off">
                    <button type="submit" class="btn btn-success btn-lg px-4 rounded-3 fw-bold" style="white-space:nowrap;">
                        Спросить
                    </button>
                </form>
                <div class="mt-2 d-flex flex-wrap gap-2" id="aiHints"></div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ СЛАЙДЕР БРЕНДОВ ═══ --}}
@if ($bannerSlides->isNotEmpty())
    <div class="brand-slider" id="brandSlider">
        <button class="brand-slider-arrow prev" onclick="brandSlide(-1)" aria-label="Назад">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2d5a1b" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button class="brand-slider-arrow next" onclick="brandSlide(1)" aria-label="Вперёд">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2d5a1b" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>

        @foreach ($bannerSlides as $i => $slide)
            <div class="brand-slide @if ($loop->first) active @endif" data-index="{{ $i }}">
                <img src="{{ $slide->banner_url }}" class="brand-slide-banner" alt="{{ $slide->name }}">

                <div class="brand-slide-footer">
                    <div class="container">
                        <span class="brand-slide-label">{{ $slide->name }}</span>
                        <div class="brand-slide-products">
                            @foreach ($slide->products as $p)
                                <a href="{{ route('products.show', $p) }}" class="brand-mini-card">
                                    <img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy">
                                    <div class="brand-mini-card-info">
                                        <div class="brand-mini-card-name">{{ $p->name }}</div>
                                        <div class="brand-mini-card-price">{{ number_format($p->price, 0, '', ' ') }} ₽</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="brand-slider-dots" id="brandDots">
            @foreach ($bannerSlides as $i => $_)
                <div class="brand-slider-dot @if ($loop->first) active @endif" onclick="brandGoTo({{ $i }})"></div>
            @endforeach
        </div>

        <div class="brand-slider-progress" id="brandProgress"></div>
    </div>
@endif

{{-- ═══ ПОИСК + ФИЛЬТРЫ ═══ --}}
<div class="container mb-5" id="catalog">
    <form id="filterForm">
        <div class="filter-bar">

            <div class="filter-search">
                <div class="input-group">
                    <span class="input-group-text" style="background:#f8fef3;border:1.5px solid #d4edaa;border-right:none;color:#a6d175;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                    </span>
                    <input type="text" name="q" class="form-control" placeholder="Поиск препарата..."
                           value="{{ $filters['q'] ?? '' }}"
                           style="border:1.5px solid #d4edaa;border-left:none;font-size:.9rem;">
                </div>
            </div>

            <div class="filter-item">
                <select name="brand" class="form-select" style="border:1.5px solid #d4edaa;font-size:.88rem;color:#444;">
                    <option value="">Все бренды</option>
                    @foreach ($brands as $b)
                        <option value="{{ $b->id }}" @selected(($filters['brand'] ?? '') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-price">
                <div class="d-flex align-items-center gap-1">
                    <input type="number" name="price_min" class="form-control form-control-sm text-center"
                           placeholder="от" value="{{ $filters['price_min'] ?? '' }}"
                           style="border:1.5px solid #d4edaa;font-size:.88rem;min-width:0;">
                    <span style="color:#aaa;font-size:.9rem;">—</span>
                    <input type="number" name="price_max" class="form-control form-control-sm text-center"
                           placeholder="до" value="{{ $filters['price_max'] ?? '' }}"
                           style="border:1.5px solid #d4edaa;font-size:.88rem;min-width:0;">
                    <span style="color:#777;font-size:.8rem;white-space:nowrap;">₽</span>
                </div>
            </div>

            <div class="filter-item">
                <select name="sort" class="form-select" style="border:1.5px solid #d4edaa;font-size:.88rem;color:#444;">
                    <option value="new"        @selected($sort === 'new')>Сначала новые</option>
                    <option value="price_asc"  @selected($sort === 'price_asc')>Цена ↑</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Цена ↓</option>
                    <option value="name_asc"   @selected($sort === 'name_asc')>Название А→Я</option>
                    <option value="brand_asc"  @selected($sort === 'brand_asc')>Бренд А→Я</option>
                </select>
            </div>

            <div class="filter-btns">
                <button type="submit" class="btn btn-success px-4" style="border-radius:10px;font-size:.88rem;white-space:nowrap;">Найти</button>
                <button type="button" id="resetBtn" class="btn" title="Сбросить"
                        style="border:1.5px solid #d4edaa;color:#777;border-radius:10px;font-size:.88rem;padding:.375rem .65rem;">✕</button>
            </div>

        </div>
    </form>
</div>

{{-- ═══ КАТАЛОГ ═══ --}}
<main class="container pb-5">
    <div class="text-center text-muted mb-4" style="font-size:.88rem;">
        Найдено товаров: <strong id="productsCount">{{ $products->total() }}</strong>
    </div>

    <div class="row g-4" id="productsGrid">
        @forelse ($products as $p)
            <div class="col-6 col-md-4 col-lg-3">
                @include('partials.product-card', ['product' => $p, 'inCartQty' => $cartQty[$p->id] ?? 0])
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h3 class="text-muted">Товаров пока нет</h3>
                <p class="text-muted">Добавьте первые товары в админ-панели</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $products->links() }}</div>
</main>

@include('partials.ai-chat-modal')
@endsection

@push('scripts')
<script>
window.CATALOG_AJAX_URL = @json(route('catalog.ajax'));
window.CART_QTY_URL     = @json(route('cart.qty'));
window.IS_LOGGED_IN     = @json(auth()->check());
</script>
@endpush
