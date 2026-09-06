<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Админка') — Аптека</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg" style="background:#f8fef3;border-bottom:1px solid #e0ebc6;">
    <div class="container">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}" style="letter-spacing:2.5px;">АПТЕКА · АДМИН</a>
        <ul class="navbar-nav ms-auto d-flex flex-row align-items-center gap-4">
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.products.index') }}">Товары</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('admin.brands.index') }}">Бренды</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('catalog.index') }}">На сайт</a></li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary rounded-pill">Выйти</button>
                </form>
            </li>
        </ul>
    </div>
</nav>

<main class="container py-5">
    @include('partials.flash')
    @yield('content')
</main>

</body>
</html>
