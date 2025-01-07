<?php
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed)) {
            throw new Exception("只支持 JPG、PNG、GIF 格式的图片");
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadDir = '../uploads/articles/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $targetPath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("图片上传失败");
        }
        
        $imagePath = 'uploads/articles/' . $filename;
    }

    $db = new Database();
    $db->updateArticle($id, $title, $content, $imagePath);

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