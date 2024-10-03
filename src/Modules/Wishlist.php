<?php
namespace PinkyFlow\Modules;

use Exception;

class Wishlist {
    private $db;
    private $user;
    private $table;

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
        $this->table = 'wishlists';
        $this->verifyTable();
    }

    private function verifyTable() {
        if (!$this->db->checkTable($this->table)) {
            $options = "
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `uid` VARCHAR(255) NOT NULL,
                `product_id` VARCHAR(255) NOT NULL,
                `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`uid`) REFERENCES `users`(`uid`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE
            ";
            try {
                $this->db->createTable($this->table, $options);
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the wishlists table.";
            }
        }
    }

    public function addToWishlist($product_id) {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to add items to wishlist.");
        }
        $uid = $this->user->getUid();

        // Check if the item is already in wishlist
        $stmt = $this->db->prepare("SELECT `id` FROM `{$this->table}` WHERE `uid` = :uid AND `product_id` = :product_id");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
        if ($stmt->fetch()) {
            throw new Exception("Product is already in wishlist.");
        }

        // Insert new wishlist item
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`uid`, `product_id`) VALUES (:uid, :product_id)");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
    }

    // Wishlist Class
    public function removeFromWishlist($product_id) {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to remove items from wishlist.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `uid` = :uid AND `product_id` = :product_id");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
    }


    public function getWishlist() {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to view wishlist items.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("
            SELECT p.*, w.added_at 
            FROM `{$this->table}` w
            JOIN `products` p ON w.product_id = p.product_id
            WHERE w.uid = :uid
        ");
        $stmt->execute(['uid' => $uid]);
        return $stmt->fetchAll();
    }

    public function clearWishlist() {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to clear wishlist.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $uid]);
    }
}

?>