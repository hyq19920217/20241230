<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $letter = isset($_GET['letter']) ? strtoupper($_GET['letter']) : 'A';
    
    if (!preg_match('/^[A-Z]$/', $letter)) {
        throw new Exception("无效的字母参数");
    }
    
    $db = new Database();
    $vocabulary = $db->getVocabularyByLetter($letter);
    echo json_encode($vocabulary, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?> 