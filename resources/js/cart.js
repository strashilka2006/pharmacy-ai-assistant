/** Кнопки «+»/«−» в корзине и карточке товара. Порт скрипта из footer.php. */

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-qty-action]').forEach((button) => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const productId = button.dataset.productId;
            const action = button.dataset.qtyAction;

            try {
                const response = await fetch(window.CART_QTY_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ product_id: productId, action }),
                });

                const data = await response.json();
                if (!data.success) return;

                // Убираем строку или перерисовываем количество без перезагрузки —
                // в оригинале здесь был безусловный location.reload()
                const row = button.closest('[data-cart-row]');

                if (data.new_qty === 0 && row) {
                    row.remove();
                } else {
                    const qtyNode = row?.querySelector('[data-qty-value]') ?? button.parentElement.querySelector('[data-qty-value]');
                    if (qtyNode) qtyNode.textContent = data.new_qty;

                    const subtotal = row?.querySelector('[data-subtotal]');
                    if (subtotal) subtotal.textContent = new Intl.NumberFormat('ru-RU').format(data.subtotal) + ' ₽';
                }

                const totalNode = document.querySelector('[data-cart-total]');
                if (totalNode) totalNode.textContent = new Intl.NumberFormat('ru-RU').format(data.cart_total) + ' ₽';

                if (data.new_qty === 0 && !document.querySelector('[data-cart-row]')) {
                    window.location.reload();
                }
            } catch (error) {
                window.location.reload();
            }
        });
    });
});
