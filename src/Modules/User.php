<?php
namespace PinkyFlow\Modules;

use Exception;
use PinkyFlow\Config;


class User {
    private $username;
    private $password;
    private $uid;
    private $role;
    private $db;
    private $table;
    public $sessionname;
    public $comment;

    public function __construct($db) {
        $this->db = $db;
        $this->table = 'users';
        $this->role = 'user';
        $this->sessionname = "pinkyflow_user";

        $this->comment = null;

        if (session_status() == PHP_SESSION_NONE) {
            session_name($this->sessionname);
            session_start();
        }
        if (isset($_SESSION['uid'])) {
            $this->uid = $_SESSION['uid'];
        }

        $this->Verify();
        $this->getComment();
    }

    public function getComment() {
        if ($this->comment === null && Config::$enableCommentModule) {
            $commentClassName = 'PinkyFlowComment';
            if (class_exists($commentClassName)) {
                $this->comment = new $commentClassName($this->db, $this);
            } else {
                // Handle the case where the class doesn't exist
                $this->comment = null;
            }
        }
        return $this->comment;
    }

    

    public function verifyPassword($inputPassword) {
        return password_verify($inputPassword, $this->password);
    }

    public function add($username, $password, $uid) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`username`, `password`, `uid`) VALUES (:username, :password, :uid)");
        $stmt->execute(['username' => $username, 'password' => $hashedPassword, 'uid' => $uid]);
    }

    public function remove() {
        $sql = "DELETE FROM `users` WHERE `uid`=:uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $this->uid]);
    }

    public function edit() {
        $sql = "UPDATE `users` SET `username`=:username, `password`=:password WHERE `uid`=:uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $this->username, 'password' => $this->password, 'uid' => $this->uid]);
    }

    public function __destruct() {
        $this->db->close();
    }

    public function Verify() {
        if (!$this->db->checkTable($this->table)) {
            $options = "
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `uid` VARCHAR(255) NOT NULL UNIQUE,
                `username` VARCHAR(255) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255),
                `role` VARCHAR(255) NOT NULL DEFAULT 'user',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_login` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_ip` VARCHAR(255)
            ";
            try {
                $this->db->createTable($this->table, $options);
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the users table.";
            }
        }
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT `uid`, `password` FROM `{$this->table}` WHERE `username` = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = $user['uid'];
            $this->uid = $user['uid'];
            $this->setLastLogin();
            $this->setLastIp($_SERVER['REMOTE_ADDR']);
            return true;
        }
        return false;
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
        $stmt = $this->db->prepare("SELECT `username` FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $this->uid]);
        $user = $stmt->fetch();
        return $user['username'];
    }

    public function getUsernameFromUid($uid) {
        $stmt = $this->db->prepare("SELECT `username` FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $uid]);
        $user = $stmt->fetch();
        return $user['username'];
    }

    public function getEmail() {
        $stmt = $this->db->prepare("SELECT `email` FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $this->uid]);
        $user = $stmt->fetch();
        return $user['email'];
    }

    public function setEmail($email) {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `email` = :email WHERE `uid` = :uid");
        $stmt->execute(['email' => $email, 'uid' => $this->uid]);
    }

    public function setLastLogin() {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `last_login` = CURRENT_TIMESTAMP WHERE `uid` = :uid");
        $stmt->execute(['uid' => $this->uid]);
    }

    public function setLastIp($ip) {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `last_ip` = :ip WHERE `uid` = :uid");
        $stmt->execute(['ip' => $ip, 'uid' => $this->uid]);
    }

    public function getIp() {
        $stmt = $this->db->prepare("SELECT `last_ip` FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $this->uid]);
        $user = $stmt->fetch();
        return $user['last_ip'];
    }

    public function getCreated() {
        $stmt = $this->db->prepare("SELECT `created_at` FROM `{$this->table}` WHERE `uid` = :uid");
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

        do {
            $uid = uniqid();
        } while ($this->db->verifyInTable($this->table, 'uid', $uid));
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`uid`, `username`, `password`, `email`) VALUES (:uid, :username, :password, :email)");
        $stmt->execute(['uid' => $uid, 'username' => $username, 'password' => $hashedPassword, 'email' => $email]);
        return true;
    }

    public function getRole() {
        $stmt = $this->db->prepare("SELECT `role` FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $this->uid]);
        $user = $stmt->fetch();
        $this->role = $user['role'];
        return $user['role'];
    }

    public function setRole($role) {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `role` = :role WHERE `uid` = :uid");
        $stmt->execute(['role' => $role, 'uid' => $this->uid]);

        $this->role = $role;

        return true;
    }

    public function getTable() {
        return $this->table;
    }

}
?>
