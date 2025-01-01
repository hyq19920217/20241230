<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    $stmt = $pdo->prepare("INSERT INTO messages (content) VALUES (?)");
    $stmt->execute([$content]);
    echo json_encode(['status' => 'success']);
}
?> 