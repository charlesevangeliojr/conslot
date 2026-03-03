<?php
/**
 * ConSlot Database Configuration
 * DCC Consultation Booking Portal
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'conslot_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo;

    // Get database connection
    public function getConnection() {
        $this->pdo = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->pdo;
    }

    // Execute query with prepared statements
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $exception) {
            echo "Query error: " . $exception->getMessage();
            return false;
        }
    }

    // Get single record
    public function getSingle($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    // Get multiple records
    public function getMultiple($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    // Insert record and return ID
    public function insert($sql, $params = []) {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $pdo->lastInsertId();
        } catch(PDOException $exception) {
            echo "Insert error: " . $exception->getMessage();
            return false;
        }
    }

    // Update record
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    // Delete record
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    // Check if record exists
    public function exists($sql, $params = []) {
        $result = $this->getSingle($sql, $params);
        return !empty($result);
    }

    // Count records
    public function count($sql, $params = []) {
        $result = $this->getSingle($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }
}

// Global database instance
$database = new Database();
?>
