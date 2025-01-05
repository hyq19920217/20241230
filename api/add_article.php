<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../config/db.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['image'])) {
        throw new Exception("没有上传图片");
    }

    $file = $_FILES['image'];
    // 检查文件类型
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed)) {
        throw new Exception("只支持 JPG、PNG、GIF 格式的图片");
    }

    // 生成唯一的文件名
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    
    // 确保上传目录存在
    $uploadDir = '../uploads/articles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // 移动文件到目标目录
    $targetPath = $uploadDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception("图片上传失败");
    }
    
    // 保存到数据库的路径（相对路径）
    $dbPath = 'uploads/articles/' . $filename;

    $db = new Database();
    $title = $_POST['title'];
    $content = $_POST['content'];
    $imagePath = $dbPath;

    $articleId = $db->addArticle($title, $content, $imagePath);
    
    echo json_encode([
        'status' => 'success',
        'message' => '文章发布成功',
        'data' => [
            'id' => $articleId,
            'image_path' => $imagePath
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 