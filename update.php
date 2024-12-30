<?php
header('Content-Type: application/json');

// 允许跨域请求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// 获取 POST 数据
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['text'])) {
    // 将新内容写入 JSON 文件
    $content = ['text' => $data['text']];
    if (file_put_contents('content.json', json_encode($content))) {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to write file']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No text provided']);
}
?> 