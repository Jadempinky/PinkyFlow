<?php
namespace PinkyFlow\Modules;

use Exception;
class Cart {
    private $db;
    private $user;
    private $table;

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
        $this->table = 'carts';
        $this->verifyTable();
    }

    private function verifyTable() {
        if (!$this->db->checkTable($this->table)) {
            $options = "
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `uid` VARCHAR(255) NOT NULL,
                `product_id` VARCHAR(255) NOT NULL,
                `quantity` INT NOT NULL,
                `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`uid`) REFERENCES `users`(`uid`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE
            ";
            try {
                $this->db->createTable($this->table, $options);
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the carts table.";
            }
        }
    }

    public function addToCart($product_id, $quantity) {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to add items to cart.");
        }
        $uid = $this->user->getUid();

        // Check if the item is already in the cart
        $stmt = $this->db->prepare("SELECT `quantity` FROM `{$this->table}` WHERE `uid` = :uid AND `product_id` = :product_id");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
        $result = $stmt->fetch();

        if ($result) {
            // Update quantity
            $newQuantity = $result['quantity'] + $quantity;
            $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `quantity` = :quantity WHERE `uid` = :uid AND `product_id` = :product_id");
            $stmt->execute([
                'quantity' => $newQuantity,
                'uid' => $uid,
                'product_id' => $product_id
            ]);
        } else {
            // Insert new record
            $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`uid`, `product_id`, `quantity`) VALUES (:uid, :product_id, :quantity)");
            $stmt->execute([
                'uid' => $uid,
                'product_id' => $product_id,
                'quantity' => $quantity
            ]);
        }
    }

    public function removeFromCart($product_id) {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to remove items from cart.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `uid` = :uid AND `product_id` = :product_id");
        $stmt->execute(['uid' => $uid, 'product_id' => $product_id]);
    }

    public function getCartItems() {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to view cart items.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("
            SELECT p.*, c.quantity 
            FROM `{$this->table}` c
            JOIN `products` p ON c.product_id = p.product_id
            WHERE c.uid = :uid
        ");
        $stmt->execute(['uid' => $uid]);
        return $stmt->fetchAll();
    }

    public function clearCart() {
        if (!$this->user->isLoggedIn()) {
            throw new Exception("User must be logged in to clear the cart.");
        }
        $uid = $this->user->getUid();
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `uid` = :uid");
        $stmt->execute(['uid' => $uid]);
    }
}
?>