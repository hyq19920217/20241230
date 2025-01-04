<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../config/db.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    
    // 处理图片上传
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imagePath = 'uploads/' . $fileName;
        }
    }

    $db = new Database();
    $articleId = $db->addArticle($title, $content, $imagePath);
    
    echo json_encode([
        'status' => 'success',
        'message' => '文章发布成功',
        'id' => $articleId
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 