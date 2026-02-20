const prices = {
    vip: 99, premium: 249, helper: 199,
    moderator: 399, sponsor: 799, admin: 1499
};

let selectedDonat = null;
let selectedServer = null;
let selectedDays = 90;
let basePrice = 0;

const overlay = document.getElementById('donModalOverlay');
const modalTitle = document.getElementById('donModalTitle');
const totalPrice = document.getElementById('donTotalPrice');

// Открыть модалку
document.querySelectorAll('.don-buy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const card = btn.closest('.don-card');
        const rank = card.querySelector('.don-card__rank').textContent.trim().toLowerCase();
        selectedDonat = rank;
        basePrice = prices[rank] || 0;
        modalTitle.textContent = card.querySelector('.don-card__rank').textContent.trim();
        selectedServer = null;
        // Сбросить выбор сервера
        document.querySelectorAll('#donServerOptions .don-option').forEach(o => o.classList.remove('don-option--active'));
        updateTotal();
        overlay.classList.add('don-modal--open');
    });
});

// Выбор сервера
document.querySelectorAll('#donServerOptions .don-option').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#donServerOptions .don-option').forEach(o => o.classList.remove('don-option--active'));
        btn.classList.add('don-option--active');
        selectedServer = btn.dataset.value;
    });
});

// Выбор периода
document.querySelectorAll('#donPeriodOptions .don-option').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#donPeriodOptions .don-option').forEach(o => o.classList.remove('don-option--active'));
        btn.classList.add('don-option--active');
        selectedDays = parseInt(btn.dataset.value);
        updateTotal();
    });
});

// Обновить итоговую цену
function updateTotal() {
    const activePeriod = document.querySelector('#donPeriodOptions .don-option--active');
    const mult = activePeriod ? parseFloat(activePeriod.dataset.priceMult) : 1;
    const total = Math.round(basePrice * mult);
    totalPrice.textContent = total + ' ₽';
}

// Закрыть
document.getElementById('donModalClose').addEventListener('click', closeModal);
document.getElementById('donModalCancel').addEventListener('click', closeModal);
overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

function closeModal() {
    overlay.classList.remove('don-modal--open');
}

// Подтвердить покупку
document.getElementById('donModalConfirm').addEventListener('click', () => {
    if (!selectedServer) {
        alert('Выберите сервер!');
        return;
    }
    // Здесь будет отправка POST запроса на donatBek.php
    console.log({ donat: selectedDonat, server: selectedServer, days: selectedDays });
    alert('Переход к оплате...');
});