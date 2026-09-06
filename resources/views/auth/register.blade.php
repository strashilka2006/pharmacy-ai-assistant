@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
<main class="container" style="max-width:480px;padding:56px 24px 80px;">
    <div style="border-bottom:1.5px solid #d4edaa;padding-bottom:28px;margin-bottom:40px;">
        <div style="font-family:'Fragment Mono',monospace;font-size:11px;color:#7AAD3F;letter-spacing:2px;margin-bottom:12px;">
            РЕГИСТРАЦИЯ
        </div>
        <h1 style="font-family:'Fragment Mono',monospace;font-weight:400;color:#1a3a0a;font-size:clamp(1.5rem,4vw,2rem);">
            Создание аккаунта
        </h1>
    </div>

    <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <div class="input-group">
                <input type="email" name="email" id="regEmail" value="{{ old('email') }}" required
                       class="form-control @error('email') is-invalid @enderror">
                <button type="button" class="btn btn-outline-secondary" id="sendCodeBtn">Выслать код</button>
            </div>
            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            <div id="codeStatus" class="small mt-1"></div>
        </div>

        <div class="mb-3 d-none" id="codeBlock">
            <label class="form-label fw-semibold">Код из письма</label>
            <div class="input-group">
                <input type="text" id="regCode" inputmode="numeric" maxlength="6" class="form-control">
                <button type="button" class="btn btn-outline-secondary" id="verifyCodeBtn">Проверить</button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Имя</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Телефон</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required
                   class="form-control @error('phone') is-invalid @enderror">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Пароль</label>
            <input type="password" name="password" required
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Повторите пароль</label>
            <input type="password" name="password_confirmation" required class="form-control">
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" name="policy" value="1" id="policy" class="form-check-input" @checked(old('policy'))>
            <label for="policy" class="form-check-label" style="font-size:.85rem;">
                Согласен с <a href="{{ route('privacy') }}" target="_blank" style="color:#3a6b1f;">политикой конфиденциальности</a>
            </label>
        </div>

        <button class="btn w-100 py-2" style="background:#a6d175;color:#fff;border-radius:20px;">Зарегистрироваться</button>
    </form>

    <p class="text-center mt-4 mb-0 text-muted" style="font-size:.85rem;">
        Уже есть аккаунт? <a href="{{ route('login') }}" style="color:#3a6b1f;">Войти</a>
    </p>
</main>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const status = document.getElementById('codeStatus');

async function post(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
        body: JSON.stringify(payload),
    });
    return { ok: response.ok, status: response.status, data: await response.json() };
}

document.getElementById('sendCodeBtn').addEventListener('click', async () => {
    const email = document.getElementById('regEmail').value.trim();
    if (!email) return;

    status.textContent = 'Отправляем код…';
    status.className = 'small mt-1 text-muted';

    const { ok, status: code, data } = await post(@json(route('register.send-code')), { email });

    if (code === 429) {
        status.textContent = 'Подождите минуту перед повторной отправкой.';
        status.className = 'small mt-1 text-danger';
        return;
    }

    if (!ok) {
        status.textContent = data.message || 'Не удалось отправить письмо';
        status.className = 'small mt-1 text-danger';
        return;
    }

    status.textContent = 'Код отправлен, проверьте почту.';
    status.className = 'small mt-1 text-success';
    document.getElementById('codeBlock').classList.remove('d-none');
});

document.getElementById('verifyCodeBtn').addEventListener('click', async () => {
    const email = document.getElementById('regEmail').value.trim();
    const code = document.getElementById('regCode').value.trim();

    const { ok, data } = await post(@json(route('register.verify-code')), { email, code });

    status.textContent = ok ? 'Почта подтверждена.' : (data.error || 'Неверный код');
    status.className = 'small mt-1 ' + (ok ? 'text-success' : 'text-danger');
});
</script>
@endpush
