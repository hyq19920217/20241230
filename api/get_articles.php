<?php
mb_internal_encoding('UTF-8');
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $size = isset($_GET['size']) ? (int)$_GET['size'] : 10;
    $offset = ($page - 1) * $size;

    $db = new Database();
    
    // 获取总文章数
    $total = $db->getArticlesCount();
    
    // 获取分页数据
    $articles = $db->getArticles($offset, $size);
    
    echo json_encode([
        'status' => 'success',
        'total' => $total,
        'articles' => $articles
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 