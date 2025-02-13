<?php
class Database {
    private $db;

    public function __construct() {
        try {
            $this->db = new SQLite3('code_snippets.db');
            $this->createTable();
        } catch (Exception $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    private function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS snippets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->exec($query);
    }

    public function saveCode($code) {
        $stmt = $this->db->prepare("INSERT INTO snippets (code) VALUES (:code)");
        $stmt->bindValue(':code', $code, SQLITE3_TEXT);
        $result = $stmt->execute();
        if ($result) {
            return $this->db->lastInsertRowID();
        }
        return false;
    }

    public function getCode($id) {
        $stmt = $this->db->prepare("SELECT * FROM snippets WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function updateCode($id, $code) {
        $stmt = $this->db->prepare("UPDATE snippets SET code = :code WHERE id = :id");
        $stmt->bindValue(':code', $code, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }
}
?>