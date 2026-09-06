{{-- Карточка товара. Порт разметки из index.php. --}}
@php $inCartQty = $inCartQty ?? 0; @endphp

<div class="card h-100 d-flex flex-column position-relative overflow-hidden"
     style="border:none;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);transition:box-shadow .2s;">

    <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark d-flex flex-column flex-grow-1">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="card-img-top"
             style="height:200px;object-fit:contain;background:#f8fef3;padding:1.5rem;">

        <div class="card-body d-flex flex-column flex-grow-1" style="padding:1rem 1rem .5rem;">
            <h5 class="card-title mb-1" style="font-size:.88rem;font-weight:600;line-height:1.35;">{{ $product->name }}</h5>

            @if ($product->brand)
                <p class="text-muted mb-2" style="font-size:.78rem;">{{ $product->brand->name }}</p>
            @endif

            <p class="text-muted flex-grow-1 mb-2" style="font-size:.78rem;line-height:1.4;">
                {{ Str::limit($product->short_description ?: $product->description ?: '', 80) }}
            </p>

            @if ($product->label_text)
                <div class="mb-2"><span class="label-badge">{{ $product->label_text }}</span></div>
            @endif

            <div class="fw-bold mt-auto" style="font-size:1.1rem;color:#2d5a1b;">
                {{ number_format($product->price, 0, '', ' ') }} ₽
            </div>
        </div>
    </a>

    <div style="padding:.75rem 1rem 1rem;">
        @auth
            @if (! $product->in_stock)
                <div class="btn btn-outline-custom w-100 disabled" style="height:42px;display:flex;align-items:center;justify-content:center;font-size:.85rem;">
                    Нет в наличии
                </div>
            @elseif ($inCartQty > 0)
                <div class="btn btn-outline-custom w-100 position-relative d-flex align-items-center"
                     style="height:42px;padding:0 8px;" data-cart-row>
                    <a href="#" class="text-decoration-none position-absolute start-0 top-0 bottom-0 d-flex align-items-center justify-content-center"
                       data-qty-action="minus" data-product-id="{{ $product->id }}"
                       style="width:48px;z-index:2;font-size:1.6rem;font-weight:bold;">−</a>

                    <span class="fw-bold position-absolute start-50 top-50 translate-middle"
                          style="font-size:1rem;z-index:1;" data-qty-value>{{ $inCartQty }}</span>

                    <a href="#" class="text-decoration-none position-absolute end-0 top-0 bottom-0 d-flex align-items-center justify-content-center"
                       data-qty-action="plus" data-product-id="{{ $product->id }}"
                       style="width:48px;z-index:2;font-size:1.6rem;font-weight:bold;">+</a>
                </div>
            @else
                <form method="POST" action="{{ route('cart.store', $product) }}">
                    @csrf
                    <button class="btn btn-outline-custom w-100"
                            style="height:42px;display:flex;align-items:center;justify-content:center;font-size:.85rem;">
                        В корзину
                    </button>
                </form>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn btn-outline-custom w-100"
               style="height:42px;display:flex;align-items:center;justify-content:center;font-size:.85rem;">
                В корзину
            </a>
        @endauth
    </div>
</div>
