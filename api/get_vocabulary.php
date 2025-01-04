<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    echo json_encode($vocabulary);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?> 