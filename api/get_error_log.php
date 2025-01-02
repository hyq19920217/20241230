<?php
header('Content-Type: text/plain; charset=utf-8');

// 安全检查
// 允许从同一域名访问
if (!isset($_SERVER['HTTP_REFERER']) || 
    !strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) {
    die('Direct access not permitted');
}

$logFile = '/var/opt/remi/php81/log/php-fpm/error.log';
if (file_exists($logFile)) {
    // 读取最后 50 行日志
    exec("tail -n 50 " . escapeshellarg($logFile), $lines);
    echo implode("\n", $lines);
} else {
    echo "Error log file not found";
}
?> 