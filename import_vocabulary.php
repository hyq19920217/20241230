<?php
require_once 'db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    if (!isset($_FILES['file'])) {
        throw new Exception("请选择文件");
    }

    $inputFileName = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // 跳过标题行
    array_shift($rows);
    
    $db = new Database();
    $errors = [];
    $successCount = 0;
    
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
            
            $db->addVocabulary($word, $partOfSpeech, $meaning, $example, $exampleCn);
            $successCount++;
        } catch (Exception $e) {
            $errors[] = "第" . ($index + 2) . "行: " . $e->getMessage();
        }
    }
    
    $status = empty($errors) ? 'success' : ($successCount > 0 ? 'partial' : 'error');
    $message = $successCount . "个词汇导入成功";
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