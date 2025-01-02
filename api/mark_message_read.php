<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
}
?> 