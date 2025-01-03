<?php
require_once '../config/config.php';

header('Content-Type: application/json; charset=utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    $stmt = $pdo->prepare("INSERT INTO messages (content) VALUES (?)");
    $stmt->execute([$content]);
    echo json_encode(['status' => 'success']);
}
?> 