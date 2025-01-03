<?php
require_once '../config/config.php';

header('Content-Type: application/json; charset=utf8mb4');

try {
    $stmt = $pdo->query("
        SELECT id, title, content, image_path, created_at, updated_at, status 
        FROM articles 
        ORDER BY created_at DESC
    ");
    
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($articles);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 