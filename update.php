<?php
header('Content-Type: application/json');

// 允许跨域请求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 开启错误日志
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取原始 POST 数据
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

// 记录接收到的数据
error_log("Received data: " . $raw_data);

if (isset($data['text'])) {
    // 将新内容写入 JSON 文件
    $content = ['text' => $data['text']];
    $json_content = json_encode($content);
    
    if ($json_content === false) {
        http_response_code(500);
        error_log("JSON encoding failed: " . json_last_error_msg());
        echo json_encode(['status' => 'error', 'message' => 'JSON encoding failed']);
        exit;
    }
    
    if (file_put_contents('content.json', $json_content)) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        error_log("Failed to write file");
        echo json_encode(['status' => 'error', 'message' => 'Failed to write file']);
    }
} else {
    http_response_code(400);
    error_log("No text provided in data");
    echo json_encode(['status' => 'error', 'message' => 'No text provided']);
}
?> 