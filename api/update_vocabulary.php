<?php
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['word']) || !isset($data['partOfSpeech']) || 
        !isset($data['meaning']) || !isset($data['example']) || !isset($data['exampleCn'])) {
        throw new Exception('Missing required fields');
    }

    $db = new Database();
    $result = $db->updateVocabulary(
        $data['id'],
        $data['word'],
        $data['partOfSpeech'],
        $data['meaning'],
        $data['example'],
        $data['exampleCn']
    );

    echo json_encode([
        'status' => 'success',
        'message' => '更新成功'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 