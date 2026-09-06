@extends('layouts.app')

@section('title', 'Вход')

@section('content')
<main class="container" style="max-width:480px;padding:56px 24px 80px;">
    <div style="border-bottom:1.5px solid #d4edaa;padding-bottom:28px;margin-bottom:40px;">
        <div style="font-family:'Fragment Mono',monospace;font-size:11px;color:#7AAD3F;letter-spacing:2px;margin-bottom:12px;">
            ЛИЧНЫЙ КАБИНЕТ
        </div>
        <h1 style="font-family:'Fragment Mono',monospace;font-weight:400;color:#1a3a0a;font-size:clamp(1.5rem,4vw,2rem);">
            Вход в аккаунт
        </h1>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Пароль</label>
            <input type="password" name="password" required class="form-control">
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Запомнить меня</label>
        </div>

        <button class="btn w-100 py-2" style="background:#a6d175;color:#fff;border-radius:20px;">Войти</button>
    </form>

    <p class="text-center mt-4 mb-0 text-muted" style="font-size:.85rem;">
        Нет аккаунта? <a href="{{ route('register') }}" style="color:#3a6b1f;">Зарегистрироваться</a>
    </p>
</main>
@endsection
