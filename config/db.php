<?php
require_once 'config.php';

class Database {
    public $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $e) {
            throw new Exception("数据库连接失败: " . $e->getMessage());
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

    // 获取词汇总数
    public function getVocabularyCount() {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM pm_vocabulary");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch(PDOException $e) {
            error_log("Count query failed: " . $e->getMessage());
            throw new Exception("获取词汇总数失败");
        }
    }

    // 添加新词汇
    public function addVocabulary($word, $partOfSpeech, $meaning, $example, $exampleCn) {
        try {
            // 标准化单词格式（去除空格和符号，转小写）
            $normalizedWord = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $word));
            
            // 检查是否已存在
            $stmt = $this->conn->prepare(
                "SELECT id FROM pm_vocabulary 
                WHERE LOWER(REPLACE(word, ' ', '')) = LOWER(REPLACE(?, ' ', ''))"
            );
            $stmt->execute([$normalizedWord]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $letter = strtoupper(substr($word, 0, 1));
            
            if ($existing) {
                // 更新已存在的记录
                $stmt = $this->conn->prepare(
                    "UPDATE pm_vocabulary 
                    SET word = ?, part_of_speech = ?, meaning = ?, 
                        example = ?, example_cn = ?, letter = ? 
                    WHERE id = ?"
                );
                return $stmt->execute([
                    $word, $partOfSpeech, $meaning, 
                    $example, $exampleCn, $letter, 
                    $existing['id']
                ]);
            } else {
                // 插入新记录
                $stmt = $this->conn->prepare(
                    "INSERT INTO pm_vocabulary (word, part_of_speech, meaning, example, example_cn, letter) 
                    VALUES (?, ?, ?, ?, ?, ?)"
                );
                return $stmt->execute([$word, $partOfSpeech, $meaning, $example, $exampleCn, $letter]);
            }
        } catch(PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            throw new Exception("添加或更新词汇失败");
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

    public function getArticles() {
        $stmt = $this->conn->query("
            SELECT id, title, content, image_path, created_at, updated_at 
            FROM articles 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addArticle($title, $content, $imagePath = null) {
        $stmt = $this->conn->prepare("
            INSERT INTO articles (title, content, image_path, created_at, updated_at) 
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$title, $content, $imagePath]);
        return $this->conn->lastInsertId();
    }
}
?> 