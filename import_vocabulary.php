<?php
require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

try {
    if (!isset($_FILES['file'])) {
        throw new Exception("请选择文件");
    }

    $inputFileName = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // 跳过表头
    array_shift($rows);
    
    $db = new Database();
    $errors = [];
    $success = 0;

    foreach ($rows as $i => $row) {
        // 跳过空行
        if (empty(array_filter($row))) continue;

        // 验证必填字段
        if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4])) {
            $errors[] = "第" . ($i + 2) . "行：缺少必填字段";
            continue;
        }

        try {
            $db->addVocabulary(
                trim($row[0]), // word
                trim($row[1]), // partOfSpeech
                trim($row[2]), // meaning
                trim($row[3]), // example
                trim($row[4])  // exampleCn
            );
            $success++;
        } catch (Exception $e) {
            $errors[] = "第" . ($i + 2) . "行：" . $e->getMessage();
        }
    }

    echo json_encode([
        'status' => empty($errors) ? 'success' : 'partial',
        'message' => "成功导入 {$success} 条记录" . 
                    (empty($errors) ? '' : "，失败 " . count($errors) . " 条"),
        'errors' => $errors
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 