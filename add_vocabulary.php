<?php
require_once 'config.php';
require_once 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = new Database();
    error_log("Attempting to connect to database...");
    error_log("DB_HOST: " . DB_HOST);
    error_log("DB_NAME: " . DB_NAME);
    error_log("DB_USER: " . DB_USER);
    error_log("Connection status: " . ($db->conn ? 'connected' : 'failed'));
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception("无效的数据格式");
    }
    
    // 验证必填字段
    $required = ['word', 'partOfSpeech', 'meaning', 'example', 'exampleCn'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("字段 {$field} 不能为空");
        }
    }
    
    $result = $db->addVocabulary(
        $data['word'],
        $data['partOfSpeech'],
        $data['meaning'],
        $data['example'],
        $data['exampleCn']
    );
    
    echo json_encode([
        'status' => 'success',
        'message' => '添加成功'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 