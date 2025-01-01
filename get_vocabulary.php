<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = new Database();
    $stmt = $db->conn->prepare("SELECT * FROM pm_vocabulary ORDER BY word ASC");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Fetched data: " . json_encode($result));
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Error fetching vocabulary: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([], JSON_UNESCAPED_UNICODE);
}
?> 