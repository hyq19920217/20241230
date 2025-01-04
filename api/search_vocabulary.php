<?php
// Trigger deploy - fix article API deployment
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../config/db.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

try {
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';
    
    if (empty($term)) {
        throw new Exception("搜索词不能为空");
    }
    
    $db = new Database();
    $vocabulary = $db->searchVocabulary($term);
    echo json_encode($vocabulary);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 