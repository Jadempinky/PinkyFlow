<?php
namespace PinkyFlow\Core;


// Automatically open and close the DB on use
class PinkyFlowDB {
    private $host;
    private $user;
    private $pass;
    private $db;

    private $conn;

    public function __construct($host, $user, $pass, $db) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
        $dsn = "mysql:host={$this->host}";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, // Use \PDO to reference the global PDO class
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];
        try {
            $this->conn = new \PDO($dsn, $this->user, $this->pass, $options); // Use \PDO here
        } catch (\PDOException $e) { // Use \PDOException for handling PDO-specific exceptions
            die("Database connection failed: " . $e->getMessage());
        }

        if (!$this->verifyDatabase()) {
            $this->createDB($this->db);
        }

        $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
        try {
            $this->conn = new \PDO($dsn, $this->user, $this->pass, $options); // Use \PDO again here
        } catch (\PDOException $e) {
            die("Database selection failed: " . $e->getMessage());
        }
    }

    public function createDB($dbName) {
        $sql = "CREATE DATABASE IF NOT EXISTS `$dbName`";
        $this->query($sql);
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) { // Use \PDOException for exception handling
            error_log($e->getMessage());
            throw new \Exception("An error occurred while executing the query.");
        }
    }

    public function real_escape_string($string) {
        return $this->conn->quote($string);
    }

    public function getLastError() {
        return $this->conn->errorInfo()[2];
    }

    public function close() {
        $this->conn = null;
    }

    public function checkTable($TableName) {
        $stmt = $this->conn->prepare("SHOW TABLES LIKE :tableName");
        $stmt->execute(['tableName' => $TableName]);
        return $stmt->rowCount() > 0;
    }

    public function createTable($tableName, $options) {
        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` ($options) ENGINE=InnoDB;";
        $this->query($sql);
    }

    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    public function verifyInTable($tableName, $column, $value) {
        $stmt = $this->conn->prepare("SELECT * FROM `$tableName` WHERE `$column` = :value");
        $stmt->execute(['value' => $value]);
        return $stmt->rowCount() > 0;
    }

    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

    public function verifyDatabase() {
        $sql = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :dbName";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['dbName' => $this->db]);
            $result = $stmt->fetch();

            if ($result === false) {
                return false;
            }

            return true;
        } catch (\PDOException $e) {
            error_log("Database verification failed: " . $e->getMessage());
            return false;
        }
    }
}
?>
