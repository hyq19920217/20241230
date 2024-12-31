<?php
require_once 'config.php';

class Database {
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("数据库连接失败");
        }
    }
    
    public function getContent() {
        try {
            $stmt = $this->conn->query("SELECT content FROM page_content ORDER BY id DESC LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['content'] : '';
        } catch(PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("获取内容失败");
        }
    }
    
    public function updateContent($text) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO page_content (content) VALUES (?)");
            return $stmt->execute([$text]);
        } catch(PDOException $e) {
            error_log("Update failed: " . $e->getMessage());
            throw new Exception("更新内容失败");
        }
    }
}
?> 