<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 设置错误日志路径
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/nginx/error.log');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 记录请求开始
error_log("Update request started");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
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
            throw new Exception("JSON encoding failed: " . json_last_error_msg());
        }
        
        if (file_put_contents('content.json', $json_content) === false) {
            throw new Exception("Failed to write file");
        }
        
        error_log("File written successfully");
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception("No text provided");
    }
} catch (Exception $e) {
    error_log("Error occurred: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 