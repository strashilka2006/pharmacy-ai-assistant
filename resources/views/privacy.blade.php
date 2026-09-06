@extends('layouts.app')

@section('title', 'Политика конфиденциальности')

@section('content')
<main class="container" style="max-width:820px;padding:56px 24px 80px;">
    <h1 class="mb-4">Политика конфиденциальности</h1>

    {{-- Текст политики перенеси сюда из старого public/privacy.php.
         Там ~150 строк готовой вёрстки: скопируй содержимое <main>,
         заменив <?= ?> на {{ }}, если они там есть. --}}
    <p class="text-muted">
        Здесь должен быть текст политики из старого файла <code>public/privacy.php</code>.
    </p>
</main>
@endsection
