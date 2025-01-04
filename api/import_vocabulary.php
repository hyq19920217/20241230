<?php
// 确保不显示 HTML 错误页面
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 设置错误日志路径
ini_set('log_errors', 1);
ini_set('error_log', '/var/opt/remi/php81/log/php-fpm/www-error.log');

require_once '../config/config.php';
require_once '../config/db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    // 添加调试信息
    error_log("Starting file import...");
    error_log("Debug mode: " . (DEBUG_MODE ? 'true' : 'false'));
    error_log("PHP version: " . phpversion());
    error_log("Loaded extensions: " . implode(", ", get_loaded_extensions()));
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    error_log("Upload max filesize: " . ini_get('upload_max_filesize'));
    error_log("Post max size: " . ini_get('post_max_size'));
    error_log("Server info: " . print_r($_SERVER, true));
    
    // 检查 vendor 目录是否存在
    if (!file_exists('../vendor/autoload.php')) {
        throw new Exception("Composer dependencies not installed");
    }
    
    if (!isset($_FILES['file'])) {
        error_log("No file uploaded");
        throw new Exception("请选择文件");
    }

    // 检查文件上传错误
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $_FILES['file']['error']);
        $uploadErrors = array(
            UPLOAD_ERR_INI_SIZE => '文件大小超过 php.ini 中的限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过表单中的限制',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => '文件上传被扩展程序停止'
        );
        throw new Exception(isset($uploadErrors[$_FILES['file']['error']]) 
            ? $uploadErrors[$_FILES['file']['error']] 
            : '文件上传失败');
    }

    error_log("File received: " . print_r($_FILES, true));
    error_log("Upload error code: " . $_FILES['file']['error']);
    error_log("Upload tmp name: " . $_FILES['file']['tmp_name']);
    error_log("Current working directory: " . getcwd());
    error_log("Temp directory: " . sys_get_temp_dir());
    
    // 检查文件大小
    if ($_FILES['file']['size'] > UPLOAD_MAX_SIZE) {
        throw new Exception("文件大小超过限制");
    }
    
    // 检查文件类型
    $fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    if (!in_array($fileType, ALLOWED_EXTENSIONS)) {
        throw new Exception("请上传 Excel 文件 (.xlsx 或 .xls)");
    }
    
    $inputFileName = $_FILES['file']['tmp_name'];
    if (!file_exists($inputFileName)) {
        throw new Exception("文件上传失败");
    }
    
    error_log("Loading file: " . $inputFileName);
    try {
        $spreadsheet = IOFactory::load($inputFileName);
    } catch (Exception $e) {
        error_log("Failed to load spreadsheet: " . $e->getMessage());
        error_log("Exception trace: " . $e->getTraceAsString());
        throw new Exception("Excel 文件格式错误或损坏: " . $e->getMessage());
    }
    
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
                WHERE LOWER(REPLACE(word, ' ', '')) = LOWER(REPLACE(?, ' ', ''))"
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
    
    $debug_info = DEBUG_MODE ? [
        'php_version' => phpversion(),
        'extensions' => get_loaded_extensions(),
        'cwd' => getcwd(),
        'tmp_dir' => sys_get_temp_dir(),
        'file_info' => isset($_FILES['file']) ? [
            'name' => $_FILES['file']['name'],
            'type' => $_FILES['file']['type'],
            'size' => $_FILES['file']['size'],
            'error' => $_FILES['file']['error'],
            'tmp_name' => $_FILES['file']['tmp_name']
        ] : 'No file uploaded',
        'server_info' => [
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time')
        ],
        'database_info' => [
            'host' => DB_HOST,
            'name' => DB_NAME,
            'connected' => isset($db->conn) ? 'yes' : 'no'
        ]
    ] : null;

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'errors' => $errors,
        'debug' => $debug_info
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Import error: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    http_response_code(500);
    $debug_info = DEBUG_MODE ? [
        'php_version' => phpversion(),
        'extensions' => get_loaded_extensions(),
        'cwd' => getcwd(),
        'tmp_dir' => sys_get_temp_dir(),
        'file_info' => isset($_FILES['file']) ? [
            'name' => $_FILES['file']['name'],
            'type' => $_FILES['file']['type'],
            'size' => $_FILES['file']['size'],
            'error' => $_FILES['file']['error'],
            'tmp_name' => $_FILES['file']['tmp_name']
        ] : 'No file uploaded',
        'error_trace' => $e->getTraceAsString(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'server_info' => [
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time')
        ],
        'database_info' => [
            'host' => DB_HOST,
            'name' => DB_NAME,
            'connected' => isset($db->conn) ? 'yes' : 'no'
        ]
    ] : null;
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => $debug_info
    ], JSON_UNESCAPED_UNICODE);
}
exit;
?> 