<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../config/db.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, no-store, must-revalidate");

try {
    $db = new Database();
    $articles = $db->getArticles();
    echo json_encode($articles);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 