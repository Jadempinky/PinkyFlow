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
        $sql = "
            CREATE TABLE IF NOT EXISTS `comments` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `uid` VARCHAR(255) NOT NULL,
                `parent_id` INT DEFAULT NULL,
                `rating` INT NULL,
                `comment` TEXT NOT NULL,
                `reply_to` INT DEFAULT NULL,
                `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`uid`) REFERENCES `users`(`uid`) ON DELETE CASCADE,
                FOREIGN KEY (`reply_to`) REFERENCES `comments`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ";
    
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            echo "Comments table created successfully!<br>";
        } catch (\PDOException $e) {
            echo "Error creating the comments table: " . $e->getMessage() . "<br>";
            error_log($e->getMessage());
        }
    }
    
    
    
    

    public function addComment($userId, $parentId, $content, $rating = null, $replyTo = null) {
        $sql = "INSERT INTO comments (uid, parent_id, comment, rating, reply_to) 
                VALUES (:uid, :parent_id, :comment, :rating, :reply_to)";
        $stmt = $this->db->prepare($sql);
    
        try {
            $stmt->execute([
                'uid' => $userId,
                'parent_id' => $parentId,
                'comment' => $content,
                'rating' => $rating,
                'reply_to' => $replyTo
            ]);
            echo "Comment added successfully!";
        } catch (\PDOException $e) {
            throw new \Exception("Error adding comment: " . $e->getMessage());
        }
    }
    

    public function getComments($product_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $product_id]);
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
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $product_id]);
    }

    public function clearAllComments() {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}`");
        $stmt->execute();
    }

    public function getCommentCount($product_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $product_id]);
        return $stmt->fetchColumn();
    }

    public function getRating($product_id) {
        $stmt = $this->db->prepare("SELECT AVG(`rating`) FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $product_id]);
        return $stmt->fetchColumn();
    }

    public function getRatingCount($product_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $product_id]);
        return $stmt->fetchColumn();
    }

    public function getRatings($product_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $product_id]);
        return $stmt->fetchAll();
    }

    public function deleteRating($product_id) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $product_id]);
    }

    public function deleteAllRatings() {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}`");
        $stmt->execute();
    }

}
?>