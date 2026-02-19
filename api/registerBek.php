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

$login = trim((string)($_POST['login'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['pass'] ?? '');
$repeatPassword = (string)($_POST['repeatpass'] ?? '');

$errors = [];

if ($login === '') {
    $errors['login'] = 'Введите логин.';
} elseif (!preg_match('/^[a-zA-Z0-9_]{3,24}$/', $login)) {
    $errors['login'] = 'Логин: 3-24 символа, только буквы, цифры и _.';
}

if ($email === '') {
    $errors['email'] = 'Введите email.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный email.';
}

if ($password === '') {
    $errors['pass'] = 'Введите пароль.';
} elseif (strlen($password) < 8) {
    $errors['pass'] = 'Пароль должен быть не короче 8 символов.';
}

if ($repeatPassword === '') {
    $errors['repeatpass'] = 'Подтвердите пароль.';
} elseif ($password !== '' && $password !== $repeatPassword) {
    $errors['repeatpass'] = 'Пароли не совпадают.';
}

if ($errors !== []) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'errors' => $errors,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

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

    $checkStmt = $pdo->prepare('SELECT username, email FROM users WHERE username = :username OR email = :email LIMIT 1');
    $checkStmt->execute([
        ':username' => $login,
        ':email' => $email,
    ]);
    $existingUser = $checkStmt->fetch();

    if ($existingUser !== false) {
        $conflictErrors = [];
        if (($existingUser['username'] ?? '') === $login) {
            $conflictErrors['login'] = 'Этот логин уже занят.';
        }
        if (($existingUser['email'] ?? '') === $email) {
            $conflictErrors['email'] = 'Этот email уже зарегистрирован.';
        }

        http_response_code(409);
        echo json_encode([
            'ok' => false,
            'errors' => $conflictErrors,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $insertStmt = $pdo->prepare(
        'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)'
    );
    $insertStmt->execute([
        ':username' => $login,
        ':email' => $email,
        ':password' => $passwordHash,
    ]);

    http_response_code(201);
    echo json_encode([
        'ok' => true,
        'message' => 'Пользователь успешно зарегистрирован.',
        'data' => [
            'id' => (int)$pdo->lastInsertId(),
            'login' => $login,
            'email' => $email,
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Ошибка базы данных при регистрации.',
    ], JSON_UNESCAPED_UNICODE);
}
