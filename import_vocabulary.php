<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    // 添加调试信息
    error_log("Starting file import...");
    
    if (!isset($_FILES['file'])) {
        throw new Exception("请选择文件");
    }

    error_log("File received: " . print_r($_FILES, true));
    
    // 检查文件类型
    $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if (!in_array($fileType, ['xlsx', 'xls'])) {
        throw new Exception("请上传 Excel 文件 (.xlsx 或 .xls)");
    }
    
    // 检查文件大小
    if ($_FILES['file']['size'] > 5000000) { // 5MB 限制
        throw new Exception("文件大小超过限制");
    }
    
    $inputFileName = $_FILES['file']['tmp_name'];
    if (!file_exists($inputFileName)) {
        throw new Exception("文件上传失败");
    }
    
    error_log("Loading file: " . $inputFileName);
    $spreadsheet = IOFactory::load($inputFileName);
    
    // 检查是否成功加载
    if (!$spreadsheet) {
        throw new Exception("无法加载 Excel 文件");
    }
    
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // 跳过标题行
    array_shift($rows);
    
    $db = new Database();
    $errors = [];
    $successCount = 0;
    $updateCount = 0;
    
    foreach ($rows as $index => $row) {
        if (empty($row[0])) continue; // 跳过空行
        
        try {
            $word = trim($row[0]);
            $partOfSpeech = trim($row[1]);
            $meaning = trim($row[2]);
            $example = trim($row[3]);
            $exampleCn = trim($row[4]);
            
            if (empty($word) || empty($partOfSpeech) || empty($meaning)) {
                throw new Exception("必填字段不能为空");
            }
            
            // 检查是否是更新操作
            $normalizedWord = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $word));
            $stmt = $db->conn->prepare(
                "SELECT id FROM pm_vocabulary 
                WHERE LOWER(REGEXP_REPLACE(word, '[^a-zA-Z0-9]', '')) = ?"
            );
            $stmt->execute([$normalizedWord]);
            $isUpdate = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            
            $db->addVocabulary($word, $partOfSpeech, $meaning, $example, $exampleCn);
            if ($isUpdate) {
                $updateCount++;
            } else {
                $successCount++;
            }
        } catch (Exception $e) {
            $errors[] = "第" . ($index + 2) . "行: " . $e->getMessage();
        }
    }
    
    $status = empty($errors) ? 'success' : ($successCount > 0 ? 'partial' : 'error');
    $message = "导入完成：" . $successCount . "个新词汇，" . $updateCount . "个更新";
    if (!empty($errors)) {
        $message .= "，" . count($errors) . "个词汇导入失败";
    }
    
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?> 