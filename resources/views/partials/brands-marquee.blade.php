{{-- Бегущая лента брендов. Порт блока BRANDS MARQUEE из index.php.
     Список дублируется дважды — анимация сдвигает дорожку ровно на -50%,
     поэтому склейка не видна и лента едет бесконечно. --}}
@php
    $marqueeBrands = [
        ['ЭВАЛАР',         120, 'Inter, sans-serif', 20, 700, 2],
        ['SOLGAR',         100, 'Georgia, serif',    21, 400, 3],
        ['LA ROCHE-POSAY', 200, 'Inter, sans-serif', 17, 300, 2],
        ['VICHY',           80, 'Georgia, serif',    21, 400, 4],
        ['BAYER',           80, 'Inter, sans-serif', 20, 700, 2],
        ['DOPPELHERZ',     180, 'Inter, sans-serif', 17, 300, 2],
        ['NUROFEN',        120, 'Inter, sans-serif', 20, 700, 1],
        ['КОМПЛИВИТ',      130, 'Georgia, serif',    19, 400, 2],
        ['CENTRUM',        110, 'Inter, sans-serif', 20, 300, 3],
    ];
@endphp

<div class="brands-marquee-outer mb-4">
    <div class="brands-marquee-track">
        @for ($copy = 0; $copy < 2; $copy++)
            <div class="brands-marquee-inner" @if ($copy) aria-hidden="true" @endif>
                @foreach ($marqueeBrands as [$name, $width, $font, $size, $weight, $spacing])
                    <div class="brand-item">
                        <svg height="28" viewBox="0 0 {{ $width }} 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <text x="0" y="22" font-family="{{ $font }}" font-size="{{ $size }}"
                                  font-weight="{{ $weight }}" letter-spacing="{{ $spacing }}" fill="white">{{ $name }}</text>
                        </svg>
                    </div>
                    <span class="brand-sep">✦</span>
                @endforeach
            </div>
        @endfor
    </div>
</div>
