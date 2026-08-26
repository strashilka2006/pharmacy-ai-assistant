<!-- public/layout/footer.php -->
<footer style="background:#f8fef3;border-top:1px solid #e0ebc6;padding:2rem 0 1.5rem;">
    <div class="container">
        <div class="row align-items-start gy-3">

            <!-- Бренд + копирайт -->
            <div class="col-12 col-lg-4">
                <a href="<?= BASE_URL ?>/index.php"
                   style="color:#333;font-size:1.2rem;letter-spacing:2.5px;text-decoration:none;font-weight:600;">
                    АПТЕКА
                </a>
                <p style="color:#777;font-size:.78rem;margin-top:.4rem;letter-spacing:.5px;">
                    &copy; <?= date("Y") ?> ОНЛАЙН-АПТЕКА.<br>ВСЕ ПРАВА ЗАЩИЩЕНЫ.
                </p>
            </div>

            <!-- Навигация -->
            <div class="col-6 col-lg-4">
                <p style="color:#aaa;font-size:.7rem;letter-spacing:1.5px;margin-bottom:.6rem;">ИНФОРМАЦИЯ</p>
                <ul class="list-unstyled mb-0" style="display:flex;flex-direction:column;gap:.4rem;">
                    <li><a href="#" style="color:#333;font-size:.82rem;letter-spacing:.5px;text-decoration:none;">КОНТАКТЫ</a></li>
                    <li><a href="#" style="color:#333;font-size:.82rem;letter-spacing:.5px;text-decoration:none;">ДОСТАВКА</a></li>
                    <li><a href="#" style="color:#333;font-size:.82rem;letter-spacing:.5px;text-decoration:none;">ПОЛИТИКА КОНФИДЕНЦИАЛЬНОСТИ</a></li>
                </ul>
            </div>

            <!-- Соцсети -->
            <div class="col-6 col-lg-4">
                <p style="color:#aaa;font-size:.7rem;letter-spacing:1.5px;margin-bottom:.6rem;">МЫ В СЕТИ</p>
                <div style="display:flex;flex-direction:column;gap:.4rem;">
                    <a href="https://vk.com" target="_blank" rel="noopener"
                       style="display:inline-flex;align-items:center;gap:.4rem;background:#eef6e0;border:1.5px solid #c5dfa0;color:#3a6b1f;border-radius:20px;padding:.25rem .9rem;font-size:.78rem;text-decoration:none;font-weight:500;width:fit-content;">
                        <i class="bi bi-vk"></i> VK
                    </a>
                    <a href="https://t.me" target="_blank" rel="noopener"
                       style="display:inline-flex;align-items:center;gap:.4rem;background:#eef6e0;border:1.5px solid #c5dfa0;color:#3a6b1f;border-radius:20px;padding:.25rem .9rem;font-size:.78rem;text-decoration:none;font-weight:500;width:fit-content;">
                        <i class="bi bi-telegram"></i> TELEGRAM
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener"
                       style="display:inline-flex;align-items:center;gap:.4rem;background:#eef6e0;border:1.5px solid #c5dfa0;color:#3a6b1f;border-radius:20px;padding:.25rem .9rem;font-size:.78rem;text-decoration:none;font-weight:500;width:fit-content;">
                        <i class="bi bi-youtube"></i> YOUTUBE
                    </a>
                </div>
            </div>

        </div>
    </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.qty-change, .qty-change-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.id;
            const action = this.dataset.action;
            fetch('ajax_qty.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + encodeURIComponent(productId)
                    + '&action=' + encodeURIComponent(action)
                    + '&csrf=' + encodeURIComponent(window.CSRF_TOKEN)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const qtySpan = this.closest('div').querySelector('span.fw-bold, span.position-absolute');
                    if (data.new_qty > 0) {
                        qtySpan.textContent = data.new_qty;
                        if (document.querySelector('table.table')) location.reload();
                    } else {
                        location.reload();
                    }
                }
            })
            .catch(err => { console.error(err); location.reload(); });
        });
    });
});
</script>

<script>
(function(){
    const canvas = document.getElementById('cursorTrail');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    // ... твой код курсора
})();
</script>


</body>
</html>
