<?php
mb_internal_encoding('UTF-8');
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (!isset($_GET['id'])) {
        throw new Exception("文章ID不能为空");
    }

    $db = new Database();
    $article = $db->getArticle($_GET['id']);
    
    if (!$article) {
        throw new Exception("文章不存在");
    }
    
    // 获取上一篇和下一篇文章的ID
    $prevId = $db->getPrevArticleId($_GET['id']);
    $nextId = $db->getNextArticleId($_GET['id']);
    
    echo json_encode([
        'status' => 'success',
        'article' => $article,
        'prev_id' => $prevId,
        'next_id' => $nextId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 