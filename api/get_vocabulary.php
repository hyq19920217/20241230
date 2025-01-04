<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 设置错误处理函数
function exception_handler($e) {
    header('Content-Type: application/json');
    http_response_code(500);
    error_log("Error in get_vocabulary.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
set_exception_handler('exception_handler');

require_once "../config/db.php";

try {
    // 测试数据库连接
    $db = new Database();
    if (!$db->conn) {
        throw new Exception("Database connection failed");
    }
    
    // 测试 SQL 查询
    $stmt = $db->conn->query("SELECT 1");
    if (!$stmt) {
        throw new Exception("Basic query failed");
    }
    
    $vocabulary = $db->getAllVocabulary();
    header('Content-Type: application/json');
    echo json_encode($vocabulary);
} catch (Exception $e) {
    throw $e;
}
?> 