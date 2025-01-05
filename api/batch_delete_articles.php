<?php
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['ids']) || !is_array($data['ids'])) {
        throw new Exception("无效的请求数据");
    }

    $db = new Database();
    $result = $db->batchDeleteArticles($data['ids']);
    
    echo json_encode([
        'status' => 'success',
        'message' => '删除成功'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 