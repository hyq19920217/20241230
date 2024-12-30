<?php
// 设置错误日志路径
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php-fpm/php-error.log');

header('Content-Type: application/json');

// 允许跨域请求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 开启错误日志
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 获取原始 POST 数据并记录
$raw_data = file_get_contents('php://input');
error_log("Received raw data: " . $raw_data);

$data = json_decode($raw_data, true);
error_log("Decoded data: " . print_r($data, true));

if (isset($data['text'])) {
    // 将新内容写入 JSON 文件
    $content = ['text' => $data['text']];
    $json_content = json_encode($content);
    
    if ($json_content === false) {
        $error = json_last_error_msg();
        error_log("JSON encoding failed: " . $error);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'JSON encoding failed: ' . $error]);
        exit;
    }
    
    if (file_put_contents('content.json', $json_content)) {
        error_log("File written successfully");
        echo json_encode(['status' => 'success']);
    } else {
        error_log("Failed to write file");
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to write file']);
    }
} else {
    error_log("No text provided in data");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No text provided']);
}
?> 