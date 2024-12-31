<?php
require_once 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);

    if (!isset($data['text'])) {
        throw new Exception("No text provided");
    }

    $db = new Database();
    if ($db->updateContent($data['text'])) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception("Failed to update content");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 