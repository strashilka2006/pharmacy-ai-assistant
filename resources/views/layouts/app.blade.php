<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Аптека — здоровье в надёжных руках')</title>

    <link href="https://fonts.googleapis.com/css2?family=Fragment+Mono&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Раньше было ?v=<?= time() ?> — кеш убивался на каждом запросе.
         Vite ставит хеш в имя файла и решает это правильно. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top" style="background:#f8fef3;border-bottom:1px solid #e0ebc6;padding:.3rem 0;">
    <div class="container">
        <a class="navbar-brand nav-link" href="{{ route('catalog.index') }}"
           style="color:#333;font-size:1.45rem;letter-spacing:2.5px;">АПТЕКА</a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon" style="filter:invert(30%) sepia(50%) saturate(400%) hue-rotate(70deg);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center" style="gap:2.25rem;">
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.show') }}" style="color:#333;font-size:.9rem;padding:.4rem 1rem;">ПРОФИЛЬ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('cart.index') }}" style="color:#333;font-size:.9rem;padding:.4rem 1rem;">
                            КОРЗИНА
                            @if ($cartCount = app(\App\Services\CartService::class)->count(auth()->user()))
                                <span class="badge rounded-pill" style="background:#a6d175;">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>
                    @if (auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="{{ route('admin.dashboard') }}"
                               style="color:#a6d175;font-size:.9rem;padding:.4rem 1rem;">АДМИНКА</a>
                        </li>
                    @endif
                    <li class="nav-item ms-2">
                        {{-- Выход теперь POST: GET-логаут вылетал по любой ссылке-приманке --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    style="background:#eef6e0;border:1.5px solid #c5dfa0;color:#3a6b1f;border-radius:20px;padding:.3rem 1.1rem;font-size:.82rem;font-weight:500;">
                                ВЫЙТИ
                            </button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}" style="color:#333;font-size:.9rem;padding:.4rem 1rem;">ВОЙТИ</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="{{ route('register') }}"
                           style="background:#a6d175;color:#fff;border-radius:20px;padding:.3rem 1.1rem;font-size:.82rem;text-decoration:none;font-weight:500;">
                            РЕГИСТРАЦИЯ
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<div style="height:58px;"></div>

@include('partials.flash')

@yield('content')

<footer style="background:#f8fef3;border-top:1px solid #e0ebc6;padding:2rem 0 1.5rem;">
    <div class="container">
        <div class="row align-items-start gy-3">
            <div class="col-12 col-lg-4">
                <a href="{{ route('catalog.index') }}"
                   style="color:#333;font-size:1.2rem;letter-spacing:2.5px;text-decoration:none;font-weight:600;">АПТЕКА</a>
                <p style="color:#777;font-size:.78rem;margin-top:.4rem;letter-spacing:.5px;">
                    &copy; {{ date('Y') }} ОНЛАЙН-АПТЕКА.<br>ВСЕ ПРАВА ЗАЩИЩЕНЫ.
                </p>
            </div>

            <div class="col-6 col-lg-4">
                <p style="color:#aaa;font-size:.7rem;letter-spacing:1.5px;margin-bottom:.6rem;">ИНФОРМАЦИЯ</p>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <li><a href="#" style="color:#333;font-size:.82rem;text-decoration:none;">КОНТАКТЫ</a></li>
                    <li><a href="#" style="color:#333;font-size:.82rem;text-decoration:none;">ДОСТАВКА</a></li>
                    <li><a href="{{ route('privacy') }}" style="color:#333;font-size:.82rem;text-decoration:none;">ПОЛИТИКА КОНФИДЕНЦИАЛЬНОСТИ</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-4">
                <p style="color:#aaa;font-size:.7rem;letter-spacing:1.5px;margin-bottom:.6rem;">МЫ В СЕТИ</p>
                <div class="d-flex flex-column gap-2">
                    @foreach (['vk' => 'VK', 'telegram' => 'TELEGRAM', 'youtube' => 'YOUTUBE'] as $icon => $label)
                        <a href="#" target="_blank" rel="noopener"
                           style="display:inline-flex;align-items:center;gap:.4rem;background:#eef6e0;border:1.5px solid #c5dfa0;color:#3a6b1f;border-radius:20px;padding:.25rem .9rem;font-size:.78rem;text-decoration:none;font-weight:500;width:fit-content;">
                            <i class="bi bi-{{ $icon }}"></i> {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
