<?php
require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);

    if (!isset($data['word']) || !isset($data['partOfSpeech']) || 
        !isset($data['meaning']) || !isset($data['example']) || 
        !isset($data['exampleCn'])) {
        throw new Exception("缺少必要的字段");
    }

    $db = new Database();
    if ($db->addVocabulary(
        $data['word'],
        $data['partOfSpeech'],
        $data['meaning'],
        $data['example'],
        $data['exampleCn']
    )) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception("添加词汇失败");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 