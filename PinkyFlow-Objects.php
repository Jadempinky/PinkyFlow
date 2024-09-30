<?php

if (!isset($enableDatabase)) {
    $enableDatabase = false;
}
if (!isset($enableUserModule)) {
    $enableUserModule = false;
}

// Update conditional checks accordingly
if ($enableUserModule) {
    $enableDatabase = true; // Ensure database is enabled if user functionality is required
}

if ($enableDatabase) {
    if (!class_exists('PinkyFlowDB')) {
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
                $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            }
        
            public function query($sql, $params = []) {
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute($params);
                    return $stmt;
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    throw new Exception("An error occurred while executing the query.");
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
                $sql = "CREATE TABLE IF NOT EXISTS `$tableName` ($options);";
                $this->query($sql);
            }
            

            public function prepare($sql) {
                return $this->conn->prepare($sql);
            }

            public function verifyInTable($tableName, $column, $value) {
                $stmt = $this->conn->prepare("SELECT * FROM $tableName WHERE $column = :value");
                $stmt->execute(['value' => $value]);
                return $stmt->rowCount() > 0;
            }
        }
    }
}

if ($enableUserModule) {
    if (!class_exists('PinkyFlowUser')) {
        class PinkyFlowUser {
            private $username;
            private $password;
            private $uid;
            private $db;
            private $table;
            public $sessionname;

            public function __construct($db) {
                $this->db = $db;
                $this->table = 'users';
                $this->sessionname = "pinkyflow_user";
            
                if (session_status() == PHP_SESSION_NONE) {
                    session_name($this->sessionname);
                    session_start();
                }
                if (isset($_SESSION['uid'])) {
                    $this->uid = $_SESSION['uid'];
                }
            }
            
            

            public function verifyPassword($inputPassword) {
                return password_verify($inputPassword, $this->password);
            }
            

            public function add($username, $password, $uid) {
                $username = $this->db->real_escape_string($username);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("INSERT INTO {$this->table} (username, password, uid) VALUES (:username, :password, :uid)");
                $stmt->execute(['username' => $username, 'password' => $hashedPassword, 'uid' => $uid]);
            }
            
            

            public function remove() {
                $sql = "DELETE FROM users WHERE uid=:uid";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['uid' => $this->uid]);
            }

            public function edit() {
                $sql = "UPDATE users SET username=:username, password=:password WHERE uid=:uid";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['username' => $this->username, 'password' => $this->password, 'uid' => $this->uid]);
            }

            public function __destruct() {
                $this->db->close();
            }

            public function Verify() {
                if (!$this->db->CheckTable($this->table)) {
                    $options = "
                        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        uid VARCHAR(255) NOT NULL,
                        username VARCHAR(255) NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        email VARCHAR(255),
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        last_login DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        last_ip VARCHAR(255)
                    ";
                    try {
                        $this->db->CreateTable($this->table, $options);
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        // Display a user-friendly message
                        echo "An error occurred while creating the table.";
                    }
                    
                }
            }

            public function login($username, $password) {
                $stmt = $this->db->prepare("SELECT uid, password FROM {$this->table} WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();
            
                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['uid'] = $user['uid'];
                    $this->uid = $user['uid'];
                    return true; // Authentication successful
                }
                return false; // Authentication failed
            }

            public function logout() {
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();
            }

            public function isLoggedIn() {
                return isset($_SESSION['uid']);
            }

            public function getUid() {
                return $_SESSION['uid'];
            }

            public function getUsername() {
                $stmt = $this->db->prepare("SELECT username FROM {$this->table} WHERE uid = :uid");
                $stmt->execute(['uid' => $this->uid]);
                $user = $stmt->fetch();
                return $user['username'];
            }

            public function getEmail() {
                $stmt = $this->db->prepare("SELECT email FROM {$this->table} WHERE uid = :uid");
                $stmt->execute(['uid' => $this->uid]);
                $user = $stmt->fetch();
                return $user['email'];
            }

            public function setEmail($email) {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET email = :email WHERE uid = :uid");
                $stmt->execute(['email' => $email, 'uid' => $this->uid]);
            }

            public function setLastLogin() {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET last_login = CURRENT_TIMESTAMP WHERE uid = :uid");
                $stmt->execute(['uid' => $this->uid]);
            }

            public function setLastIp($ip) {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET last_ip = :ip WHERE uid = :uid");
                $stmt->execute(['ip' => $ip, 'uid' => $this->uid]);
            }

            public function getIp() {
                $stmt = $this->db->prepare("SELECT last_ip FROM {$this->table} WHERE uid = :uid");
                $stmt->execute(['uid' => $this->uid]);
                $user = $stmt->fetch();
                return $user['last_ip'];
            }

            public function getCreated() {
                $stmt = $this->db->prepare("SELECT created_at FROM {$this->table} WHERE uid = :uid");
                $stmt->execute(['uid' => $this->uid]);
                $user = $stmt->fetch();
                return $user['created_at'];
            }

            public function register($username, $password, $verifyPassword, $email) {
                if ($password !== $verifyPassword) {
                    return "Passwords do not match";
                }
                if ($this->db->verifyInTable($this->table, 'username', $username)) {
                    return "Username already exists";
                }
                if ($this->db->verifyInTable($this->table, 'email', $email)) {
                    return "Email already exists";
                }
                
                $uid = uniqid();
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("INSERT INTO {$this->table} (uid, username, password, email) VALUES (:uid, :username, :password, :email)");
                $stmt->execute(['uid' => $uid, 'username' => $username, 'password' => $hashedPassword, 'email' => $email]);
                return true;
            }
            
            
        }
    }
}
