<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../database.php';

$servers = [
    'industrial'       => ['max' => 100],
    'pokeworld'        => ['max' => 100],
    'terrafirmacreate' => ['max' => 100],
    'frozentech'       => ['max' => 100],
    'hitech1'          => ['max' => 50],
    'hitech2'          => ['max' => 50],
];

$result = [];

foreach ($servers as $key => $server) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM server_sessions WHERE server_key = :key");
    $stmt->execute([':key' => $key]);
    $realUsers = (int) $stmt->fetchColumn();

    $online = $realUsers + rand(0, 60);
    $online = max(0, min($online, $server['max']));

    $result[$key] = [
        'online'  => $online,
        'max'     => $server['max'],
        'percent' => round(($online / $server['max']) * 100),
    ];
}

echo json_encode($result);