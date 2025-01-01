<?php
// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'content_db');
define('DB_USER', 'root');
define('DB_PASS', '${{ secrets.MYSQL_ROOT_PASSWORD }}');

// 错误处理配置
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/opt/remi/php81/log/php-fpm/error.log');
error_reporting(E_ALL);

// 文件上传配置
define('UPLOAD_MAX_SIZE', 20 * 1024 * 1024); // 20MB
define('UPLOAD_TEMP_DIR', '/usr/share/nginx/html/tmp');
define('ALLOWED_EXTENSIONS', ['xlsx', 'xls']);

// 安全配置
define('SECURE_MODE', true);
define('DEBUG_MODE', false);

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        )
    );
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    if (DEBUG_MODE) {
        throw $e;
    } else {
        header('Content-Type: application/json');
        die(json_encode([
            'status' => 'error',
            'message' => '数据库连接失败'
        ], JSON_UNESCAPED_UNICODE));
    }
}

// 设置上传文件的临时目录
if (!file_exists(UPLOAD_TEMP_DIR)) {
    mkdir(UPLOAD_TEMP_DIR, 0755, true);
}
ini_set('upload_tmp_dir', UPLOAD_TEMP_DIR);

?> 