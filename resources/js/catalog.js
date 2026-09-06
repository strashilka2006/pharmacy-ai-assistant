/** Слайдер брендов и AJAX-фильтрация каталога. Порт скриптов из index.php. */

/* ── Слайдер брендов ── */
(function () {
    const DURATION = 5500;
    const slides = document.querySelectorAll('.brand-slide');
    const dots = document.querySelectorAll('.brand-slider-dot');
    const progress = document.getElementById('brandProgress');
    const slider = document.getElementById('brandSlider');

    if (!slider || slides.length <= 1) return;

    let current = 0;
    let timer = null;

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
        startProgress();
    }

    function startProgress() {
        clearTimeout(timer);
        progress.style.transition = 'none';
        progress.style.width = '0%';

        requestAnimationFrame(() => requestAnimationFrame(() => {
            progress.style.transition = `width ${DURATION}ms linear`;
            progress.style.width = '100%';
        }));

        timer = setTimeout(() => goTo(current + 1), DURATION);
    }

    window.brandSlide = (dir) => goTo(current + dir);
    window.brandGoTo = (index) => goTo(index);

    slider.addEventListener('mouseenter', () => {
        clearTimeout(timer);
        progress.style.transition = 'none';
    });
    slider.addEventListener('mouseleave', startProgress);

    startProgress();
})();

/* ── AJAX-фильтрация ── */
(function () {
    const form = document.getElementById('filterForm');
    if (!form) return;

    const grid = document.getElementById('productsGrid');
    const counter = document.getElementById('productsCount');

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function card(product) {
        const col = el('div', 'col-6 col-md-4 col-lg-3');

        const wrap = el('div', 'card h-100 d-flex flex-column');
        wrap.style.cssText = 'border:none;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.07);transition:box-shadow .2s;';

        const link = el('a', 'text-decoration-none text-dark d-flex flex-column flex-grow-1');
        link.href = product.url;

        const img = el('img', 'card-img-top');
        img.src = product.image;
        img.alt = product.name;
        img.loading = 'lazy';
        img.style.cssText = 'height:200px;object-fit:contain;background:#f8fef3;padding:1.5rem;';

        const body = el('div', 'card-body d-flex flex-column flex-grow-1');
        body.style.cssText = 'padding:1rem 1rem .5rem;';

        const title = el('h5', 'card-title mb-1', product.name);
        title.style.cssText = 'font-size:.88rem;font-weight:600;line-height:1.35;';
        body.appendChild(title);

        if (product.brand_name) {
            const brand = el('p', 'text-muted mb-2', product.brand_name);
            brand.style.cssText = 'font-size:.78rem;';
            body.appendChild(brand);
        }

        const price = el('div', 'fw-bold mt-auto', Number(product.price).toLocaleString('ru-RU') + ' ₽');
        price.style.cssText = 'font-size:1.1rem;color:#2d5a1b;';
        body.appendChild(price);

        link.append(img, body);

        const footer = el('div');
        footer.style.cssText = 'padding:.75rem 1rem 1rem;';

        const button = el('a', 'btn btn-outline-custom w-100', 'В корзину');
        button.href = product.url;
        button.style.cssText = 'height:42px;display:flex;align-items:center;justify-content:center;font-size:.85rem;';
        footer.appendChild(button);

        wrap.append(link, footer);
        col.appendChild(wrap);

        return col;
    }

    async function load() {
        const params = new URLSearchParams(new FormData(form));

        grid.textContent = '';
        const loader = el('div', 'col-12 text-center py-5');
        const spinner = el('div', 'spinner-border');
        spinner.style.color = '#7AAD3F';
        loader.appendChild(spinner);
        grid.appendChild(loader);

        try {
            const response = await fetch(window.CATALOG_AJAX_URL + '?' + params.toString());
            const products = await response.json();

            counter.textContent = products.length;
            grid.textContent = '';

            if (!products.length) {
                const empty = el('div', 'col-12 text-center py-5');
                empty.appendChild(el('h3', 'text-muted', 'Ничего не найдено'));
                grid.appendChild(empty);
                return;
            }

            products.forEach((product) => grid.appendChild(card(product)));
        } catch (error) {
            grid.textContent = '';
            const fail = el('div', 'col-12 text-center py-5');
            fail.appendChild(el('h3', 'text-muted', 'Не удалось загрузить товары'));
            grid.appendChild(fail);
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        load();
    });

    form.querySelectorAll('select').forEach((select) => select.addEventListener('change', load));

    document.getElementById('resetBtn')?.addEventListener('click', () => {
        form.reset();
        load();
    });
})();
