<?php
header('Content-Type: application/json');

// 获取 POST 数据
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['text'])) {
    // 将新内容写入 JSON 文件
    $content = ['text' => $data['text']];
    file_put_contents('content.json', json_encode($content));
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No text provided']);
}
?> 