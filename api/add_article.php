<?php
require_once '../config/config.php';

header('Content-Type: application/json; charset=utf8mb4');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('请使用 POST 方法');
    }

    // 获取文章数据
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    
    if (empty($title) || empty($content)) {
        throw new Exception('标题和内容不能为空');
    }

    // 处理图片上传
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../public/uploads/articles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('只允许上传 JPG, JPEG, PNG 或 GIF 格式的图片');
        }

        $imagePath = 'uploads/articles/' . uniqid() . '.' . $fileExtension;
        $fullPath = '../public/' . $imagePath;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
            throw new Exception('图片上传失败');
        }
    }

    // 插入数据库
    $stmt = $pdo->prepare("
        INSERT INTO articles (title, content, image_path, updated_at) 
        VALUES (?, ?, ?, NOW())
    ");

    if (!$stmt->execute([$title, $content, $imagePath])) {
        throw new Exception('保存文章失败');
    }

    echo json_encode([
        'status' => 'success',
        'message' => '文章发布成功'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 