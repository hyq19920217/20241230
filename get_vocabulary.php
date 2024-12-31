<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = new Database();
    
    if (isset($_GET['letter'])) {
        // 如果指定了字母，获取该字母开头的词汇
        $letter = strtoupper($_GET['letter']);
        if (!preg_match('/^[A-Z]$/', $letter)) {
            throw new Exception("无效的字母参数");
        }
        $vocabulary = $db->getVocabularyByLetter($letter);
    } else {
        // 如果没有指定字母，获取所有词汇
        $vocabulary = $db->getAllVocabulary();
    }
    
    // 如果没有数据，返回空数组而不是 null
    echo json_encode($vocabulary ?: [], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 