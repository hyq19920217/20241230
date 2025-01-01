<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = new Database();
    $stmt = $db->conn->prepare("SELECT * FROM pm_vocabulary ORDER BY word ASC");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Error fetching vocabulary: " . $e->getMessage());
    // 当发生错误时返回空数组，这样前端代码仍然可以正常工作
    echo json_encode([], JSON_UNESCAPED_UNICODE);
}
?> 