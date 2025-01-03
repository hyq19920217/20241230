<?php
require_once '../config/config.php';

header('Content-Type: application/json; charset=utf8mb4');

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        throw new Exception('无效的文章ID');
    }
    
    // 获取当前文章
    $stmt = $pdo->prepare("
        SELECT * FROM articles WHERE id = ?
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        throw new Exception('文章不存在');
    }
    
    // 获取上一篇文章ID
    $stmt = $pdo->prepare("
        SELECT id FROM articles 
        WHERE created_at < ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$article['created_at']]);
    $prevArticle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 获取下一篇文章ID
    $stmt = $pdo->prepare("
        SELECT id FROM articles 
        WHERE created_at > ? 
        ORDER BY created_at ASC 
        LIMIT 1
    ");
    $stmt->execute([$article['created_at']]);
    $nextArticle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 添加上一篇/下一篇的ID
    $article['prev_id'] = $prevArticle ? $prevArticle['id'] : null;
    $article['next_id'] = $nextArticle ? $nextArticle['id'] : null;
    
    echo json_encode($article);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 