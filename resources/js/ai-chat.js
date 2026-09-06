/**
 * ИИ-консультант. Порт inline-скрипта из public/index.php.
 *
 * Что поменялось: экранирование через textContent вместо ручного esc() +
 * innerHTML, CSRF берётся из meta-тега, обработка 429 от лимитера Laravel.
 */

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

function messagesBox() {
    return document.getElementById('aiChatMessages');
}

function addMessage(role, text, products = []) {
    const box = messagesBox();
    const wrap = el('div', 'ai-msg ' + role);
    const inner = el('div', 'ai-msg-inner');

    const bubble = el('div', 'bubble');
    // текст ставим построчно, без innerHTML — XSS отсюда просто не возникает
    String(text).split('\n').forEach((line, i) => {
        if (i > 0) bubble.appendChild(document.createElement('br'));
        bubble.appendChild(document.createTextNode(line));
    });
    inner.appendChild(bubble);

    products.forEach((product) => inner.appendChild(productChip(product)));

    wrap.appendChild(inner);
    box.appendChild(wrap);
    box.scrollTop = box.scrollHeight;
}

function productChip(product) {
    const col = el('div', 'ai-product');

    const link = el('a', 'product-chip');
    link.href = product.url;
    link.target = '_blank';
    link.rel = 'noopener';

    const img = el('img');
    img.src = product.image;
    img.alt = product.name;
    img.width = 44;
    img.height = 44;
    img.loading = 'lazy';

    const meta = el('div');
    meta.appendChild(el('div', 'pn', product.name));
    meta.appendChild(el('div', 'pp', Number(product.price).toLocaleString('ru-RU') + ' ₽'));

    link.append(img, meta);
    col.appendChild(link);

    const details = [
        { key: 'usage_info', icon: '💊', label: 'Способ применения' },
        { key: 'composition', icon: '🧪', label: 'Состав' },
        { key: 'contraindications', icon: '⚠️', label: 'Противопоказания' },
    ].filter((d) => product[d.key]);

    if (details.length) {
        const pills = el('div', 'detail-pills');
        let openPill = null;
        let openPanel = null;

        details.forEach((detail) => {
            const pill = el('span', 'detail-pill', detail.icon + ' ' + detail.label);

            pill.addEventListener('click', () => {
                if (openPill === pill) {
                    pill.classList.remove('active');
                    openPanel?.remove();
                    openPill = openPanel = null;
                    return;
                }

                openPill?.classList.remove('active');
                openPanel?.remove();

                pill.classList.add('active');
                const panel = el('div', 'detail-panel');
                panel.appendChild(el('b', null, detail.icon + ' ' + detail.label));
                panel.appendChild(document.createTextNode(product[detail.key]));
                pills.after(panel);

                openPill = pill;
                openPanel = panel;
                messagesBox().scrollTop = messagesBox().scrollHeight;
            });

            pills.appendChild(pill);
        });

        col.appendChild(pills);
    }

    return col;
}

function showTyping() {
    const box = messagesBox();
    const node = el('div', 'ai-msg bot');
    node.id = 'aiTyping';
    const bubble = el('div', 'bubble');
    const dots = el('div', 'typing-dots');
    dots.append(el('span'), el('span'), el('span'));
    bubble.appendChild(dots);
    node.appendChild(bubble);
    box.appendChild(node);
    box.scrollTop = box.scrollHeight;
}

async function sendMessage(message) {
    const input = document.getElementById('aiModalInput');

    addMessage('user', message);
    showTyping();
    input.disabled = true;

    try {
        const response = await fetch(window.AI_CHAT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify({ message }),
        });

        document.getElementById('aiTyping')?.remove();

        if (response.status === 429) {
            addMessage('bot', 'Слишком много вопросов подряд. Подождите немного.');
            return;
        }

        const data = await response.json();

        if (data.error === 'ollama_unavailable') {
            addMessage('bot', '⚠️ ' + (data.text || 'Консультант временно недоступен.'));
            return;
        }

        let text = data.text || 'Не удалось получить ответ';
        if (data.disclaimer) text += '\n\n⚠ ' + data.disclaimer;

        addMessage('bot', text, data.products || []);
    } catch (error) {
        document.getElementById('aiTyping')?.remove();
        addMessage('bot', '⚠️ Ошибка соединения. Попробуйте ещё раз.');
    } finally {
        input.disabled = false;
        input.focus();
    }
}

function openModal(initialMessage) {
    const modal = window.bootstrap.Modal.getOrCreateInstance(document.getElementById('aiChatModal'));
    modal.show();
    if (initialMessage) sendMessage(initialMessage);
}


/* Меняющиеся подсказки под полем ввода (были в index.php) */
const HINTS = [
    'болит голова и температура',
    'сильный кашель уже 3 дня',
    'болит живот, тошнота',
    'не могу уснуть, стресс',
    'насморк и заложенность',
    'болит горло при глотании',
];

function renderHints() {
    const box = document.getElementById('aiHints');
    if (!box) return;

    box.textContent = '';

    [...HINTS]
        .sort(() => Math.random() - 0.5)
        .slice(0, 3)
        .forEach((text) => {
            const pill = el('span', 'hint-pill', text);
            pill.addEventListener('click', () => {
                document.getElementById('aiQuickInput').value = text;
                openModal(text);
            });
            box.appendChild(pill);
        });
}

document.addEventListener('DOMContentLoaded', () => {
    renderHints();
    setInterval(renderHints, 5000);

    document.getElementById('aiQuickForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const input = document.getElementById('aiQuickInput');
        const message = input.value.trim();
        if (!message) return;
        openModal(message);
        input.value = '';
    });

    document.getElementById('aiModalForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const input = document.getElementById('aiModalInput');
        const message = input.value.trim();
        if (!message) return;
        sendMessage(message);
        input.value = '';
    });
});
