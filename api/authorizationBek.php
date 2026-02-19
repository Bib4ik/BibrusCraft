<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'error' => 'Method Not Allowed. Use POST.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['pass'] ?? '');

$errors = [];

if ($email === '') {
    $errors['email'] = 'Введите email.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный email.';
}

if ($password === '') {
    $errors['pass'] = 'Введите пароль.';
}

if ($errors !== []) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'errors' => $errors,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=db;dbname=my_app;charset=utf8mb4',
        'root',
        'root',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->prepare('SELECT id, username, email, password FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user === false || !password_verify($password, (string)$user['password'])) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Неверный email или пароль.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'username' => (string)$user['username'],
        'email' => (string)$user['email'],
    ];

    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'message' => 'Авторизация успешна.',
        'data' => [
            'id' => (int)$user['id'],
            'login' => (string)$user['username'],
            'email' => (string)$user['email'],
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Ошибка базы данных при авторизации.',
    ], JSON_UNESCAPED_UNICODE);
}
