// Маппинг ключей из API → data-атрибуты карточек
const SERVER_KEYS = [
    'industrial',
    'pokeworld',
    'terrafirmacreate',
    'frozentech',
    'hitech1',
    'hitech2'
];

function updateOnline(key, data) {
    const card = document.querySelector(`[data-server="${key}"]`);
    if (!card) return;

    const bar   = card.querySelector('.oc-online-bar__fill');
    const count = card.querySelector('.oc-online-count span');
    const max   = card.querySelector('.oc-online-count');

    if (bar)   bar.style.width = data.percent + '%';
    if (count) count.textContent = data.online;
    if (max)   max.innerHTML = `<span>${data.online}</span> / ${data.max}`;
}

async function fetchOnline() {
    try {
        const res = await fetch('/api/servers.php');
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        SERVER_KEYS.forEach(key => {
            if (data[key]) updateOnline(key, data[key]);
        });
    } catch (e) {
        console.warn('Ошибка получения онлайна:', e);
    }
}

// Первый запрос сразу
fetchOnline();

// Обновляем каждые 30 секунд
setInterval(fetchOnline, 30000);