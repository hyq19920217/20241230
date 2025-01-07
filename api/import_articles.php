<?php
require_once '../vendor/autoload.php';
require_once '../config/db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_FILES['file'])) {
        throw new Exception('没有上传文件');
    }

    $inputFileName = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // 跳过表头
    array_shift($rows);

    $db = new Database();
    $successCount = 0;
    $errors = [];

    foreach ($rows as $index => $row) {
        if (empty($row[0]) || empty($row[1])) {
            continue; // 跳过空行
        }

        try {
            $title = trim($row[0]);
            $content = trim($row[1]);
            $imagePath = null;

            // 检查单元格是否包含图片
            $coordinate = 'C' . ($index + 2);
            $drawing = null;
            foreach ($worksheet->getDrawingCollection() as $drawing) {
                if ($drawing->getCoordinates() == $coordinate) {
                    break;
                }
            }

            if ($drawing) {
                $extension = strtolower($drawing->getExtension());
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $filename = uniqid() . '.' . $extension;
                    $uploadDir = '../uploads/articles/';
                    
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $targetPath = $uploadDir . $filename;
                    $drawing->getPath();
                    copy($drawing->getPath(), $targetPath);
                    $imagePath = 'uploads/articles/' . $filename;
                }
            }

            $db->addArticle($title, $content, $imagePath);
            $successCount++;
        } catch (Exception $e) {
            $errors[] = "第 " . ($index + 2) . " 行导入失败：" . $e->getMessage();
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => "成功导入 {$successCount} 篇文章" . 
                    (count($errors) > 0 ? "，" . count($errors) . " 篇导入失败" : ""),
        'errors' => $errors
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 