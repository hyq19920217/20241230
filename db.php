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
    
    // 获取指定字母开头的词汇
    public function getVocabularyByLetter($letter) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM pm_vocabulary WHERE letter = ? ORDER BY word");
            $stmt->execute([$letter]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("获取词汇列表失败");
        }
    }

    // 获取所有词汇
    public function getAllVocabulary() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM pm_vocabulary ORDER BY word");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("获取词汇列表失败");
        }
    }

    // 添加新词汇
    public function addVocabulary($word, $partOfSpeech, $meaning, $example, $exampleCn) {
        try {
            $letter = strtoupper(substr($word, 0, 1));
            $stmt = $this->conn->prepare("INSERT INTO pm_vocabulary (word, part_of_speech, meaning, example, example_cn, letter) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$word, $partOfSpeech, $meaning, $example, $exampleCn, $letter]);
        } catch(PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            throw new Exception("添加词汇失败");
        }
    }

    // 搜索词汇
    public function searchVocabulary($term) {
        try {
            $term = "%$term%";
            $stmt = $this->conn->prepare(
                "SELECT * FROM pm_vocabulary 
                WHERE word LIKE ? 
                OR meaning LIKE ? 
                ORDER BY word"
            );
            $stmt->execute([$term, $term]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Search failed: " . $e->getMessage());
            throw new Exception("搜索词汇失败");
        }
    }

    // 删除词汇
    public function deleteVocabulary($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM pm_vocabulary WHERE id = ?");
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log("Delete failed: " . $e->getMessage());
            throw new Exception("删除词汇失败");
        }
    }
}
?> 