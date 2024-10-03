<?php
namespace PinkyFlow\Modules;

use Exception;
class Comment {
    private $db;
    private $user;
    private $table;

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
        $this->table = 'comments';
        $this->verifyTable();
    }

    public function verifyTable() {
        if (!$this->db->checkTable($this->table)) {
            $options = "
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `uid` VARCHAR(255) NOT NULL,
                `rating` INT,
                `comment_parent` VARCHAR(255) NOT NULL,
                `comment` TEXT NOT NULL,
                `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`uid`) REFERENCES `users`(`uid`) ON DELETE CASCADE,
                FOREIGN KEY (`comment_parent`) REFERENCES `products`(`product_id`) ON DELETE CASCADE
            ";
            try {
                $this->db->createTable($this->table, $options);
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the comments table: " . $e->getMessage() . "<br>";
            }
        }
    }
    

    public function addComment($product_id, $comment, $user_uid = null, $rating = null) {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to add comments.");
        }
        if ($user_uid === null) {
            $user_uid = $this->user->getUid();
        }
        $uid = $user_uid;
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`uid`, `comment_parent`, `comment`" . ($rating !== null ? ", `rating`" : "") . ") VALUES (:uid, :comment_parent, :comment" . ($rating !== null ? ", :rating" : "") . ")");
        $stmt->execute([
            'uid' => $uid,
            'comment_parent' => $product_id,
            'comment' => $comment,
            'rating' => $rating
        ]);
    }

    public function getComments($product_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `comment_parent` = :comment_parent");
        $stmt->execute(['comment_parent' => $product_id]);
        return $stmt->fetchAll();
    }

    public function deleteComment($comment_id) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = :id");
        $stmt->execute(['id' => $comment_id]);
    }

    public function editComment($comment_id, $comment) {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `comment` = :comment WHERE `id` = :id");
        $stmt->execute(['comment' => $comment, 'id' => $comment_id]);
    }

    public function getComment($comment_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `id` = :id");
        $stmt->execute(['id' => $comment_id]);
        return $stmt->fetch();
    }

    public function clearComments($product_id) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `comment_parent` = :comment_parent");
        $stmt->execute(['comment_parent' => $product_id]);
    }

    public function clearAllComments() {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}`");
        $stmt->execute();
    }

    public function getCommentCount($product_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE `comment_parent` = :comment_parent");
        $stmt->execute(['comment_parent' => $product_id]);
        return $stmt->fetchColumn();
    }

    public function getRating($product_id) {
        $stmt = $this->db->prepare("SELECT AVG(`rating`) FROM `{$this->table}` WHERE `comment_parent` = :comment_parent");
        $stmt->execute(['comment_parent' => $product_id]);
        return $stmt->fetchColumn();
    }

    public function getRatingCount($product_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE `comment_parent` = :comment_parent");
        $stmt->execute(['comment_parent' => $product_id]);
        return $stmt->fetchColumn();
    }

    public function getRatings($product_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `comment_parent` = :comment_parent");
        $stmt->execute(['comment_parent' => $product_id]);
        return $stmt->fetchAll();
    }

    public function deleteRating($product_id) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `comment_parent` = :comment_parent");
        $stmt->execute(['comment_parent' => $product_id]);
    }

    public function deleteAllRatings() {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}`");
        $stmt->execute();
    }
}
?>
