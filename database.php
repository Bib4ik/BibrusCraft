<?php
$host = 'db'; // Имя из docker-compose.yml
$db   = 'my_app'; // Из переменной MYSQL_DATABASE
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "БД подключена успешно!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
