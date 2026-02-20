<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once '../database.php';

// Получаем данные из запроса
$user_id    = (int)($_POST['user_id'] ?? 0);
$server_key = trim((string)($_POST['server_key'] ?? ''));
$donat      = trim((string)($_POST['donat'] ?? ''));
$days       = (int)($_POST['days'] ?? 30); // на сколько дней покупается

// Валидация
$allowed_donats = ['vip', 'premium', 'helper', 'moderator', 'sponsor', 'admin'];
$allowed_servers = ['industrial', 'pokeworld', 'terrafirmacreate', 'frozentech', 'hitech1', 'hitech2'];

if ($user_id <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Авторизируйтесь'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($server_key, $allowed_servers)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Неизвестный сервер'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($donat, $allowed_donats)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Неверная привилегия'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Проверяем существует ли юзер
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Пользователь не найден'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Проверяем нет ли уже активной привилегии
    $stmt = $pdo->prepare("
        SELECT id, expires_at FROM donat 
        WHERE user_id = :user_id 
        AND server_key = :server_key 
        AND donat = :donat 
        AND expires_at > NOW()
    ");
    $stmt->execute([
        ':user_id'    => $user_id,
        ':server_key' => $server_key,
        ':donat'      => $donat,
    ]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Продлеваем существующую привилегию
        $stmt = $pdo->prepare("
            UPDATE donat 
            SET expires_at = DATE_ADD(expires_at, INTERVAL :days DAY)
            WHERE id = :id
        ");
        $stmt->execute([':days' => $days, ':id' => $existing['id']]);

        echo json_encode([
            'ok'      => true,
            'message' => 'Привилегия продлена',
            'expires_at' => $existing['expires_at'],
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Создаём новую привилегию
        $stmt = $pdo->prepare("
            INSERT INTO donat (user_id, server_key, donat, expires_at)
            VALUES (:user_id, :server_key, :donat, DATE_ADD(NOW(), INTERVAL :days DAY))
        ");
        $stmt->execute([
            ':user_id'    => $user_id,
            ':server_key' => $server_key,
            ':donat'      => $donat,
            ':days'       => $days,
        ]);

        echo json_encode([
            'ok'      => true,
            'message' => 'Привилегия успешно выдана',
            'data'    => [
                'user_id'    => $user_id,
                'server_key' => $server_key,
                'donat'      => $donat,
                'days'       => $days,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Ошибка базы данных'], JSON_UNESCAPED_UNICODE);
}