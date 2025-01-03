<?php
require_once '../config/config.php';

header('Content-Type: application/json; charset=utf8mb4');

$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?> 