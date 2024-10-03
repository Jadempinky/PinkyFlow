<?php
namespace PinkyFlow\Modules;

use Exception;
class Favorite {
    private $db;
    private $user;
    private $table;

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
        $this->table = 'favorites';
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
                echo "An error occurred while creating the favorites table.";
            }
        }
    }

    public function addToFavorite($product_id) {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to add items to favorites.");
        }
        $uid = $this->user->getUid();

        // Check if the item is already in favorites
        $stmt = $this->db->prepare("SELECT `id` FROM `{$this->table}` WHERE `uid` = :uid AND `product_id` = :product_id");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
        if ($stmt->fetch()) {
            throw new Exception("Product is already in favorites.");
        }

        // Insert new favorite
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`uid`, `product_id`) VALUES (:uid, :product_id)");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
    }

    public function removeFromFavorite($product_id) {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to remove items from favorites.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `uid` = :uid AND `product_id` = :product_id");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
    }

    public function getFavorites() {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to view favorite items.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("
            SELECT p.*, f.added_at 
            FROM `{$this->table}` f
            JOIN `products` p ON f.product_id = p.product_id
            WHERE f.uid = :uid
        ");
        $stmt->execute(['uid' => $uid]);
        return $stmt->fetchAll();
    }

    public function clearFavorites() {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to clear favorites.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $uid]);
    }
}

?>