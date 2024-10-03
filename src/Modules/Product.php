<?php
namespace PinkyFlow\Modules;

use Exception;

class Product {
    private $db;
    private $table;

    public function __construct($db) {
        $this->db = $db;
        $this->table = 'products';
        $this->verifyTable();
    }

    private function verifyTable() {
        if (!$this->db->checkTable($this->table)) {
            $options = "
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `product_id` VARCHAR(255) NOT NULL UNIQUE,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `price` DECIMAL(10, 2) NOT NULL,
                `stock` INT NOT NULL DEFAULT 0,
                `image` VARCHAR(255),
                `category_id` INT,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
            ";
            try {
                $this->db->createTable($this->table, $options);
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the products table.";
            }
        }
    }
    

    public function createProduct($name, $description, $price, $stock, $image, $category_id = null) {
        $product_id = uniqid('', true);
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`product_id`, `name`, `description`, `price`, `stock`, `image`, `category_id`) VALUES (:product_id, :name, :description, :price, :stock, :image, :category_id)");
        $stmt->execute([
            'product_id' => $product_id,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image' => $image,
            'category_id' => $category_id
        ]);
        return $product_id;
    }
    
    public function getProductsByCategory($category_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `category_id` = :category_id");
        $stmt->execute(['category_id' => $category_id]);
        return $stmt->fetchAll();
    }
    

    public function updateStock($product_id, $quantity) {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `stock` = :stock WHERE `product_id` = :product_id");
        $stmt->execute([
            'stock' => $quantity,
            'product_id' => $product_id
        ]);
    }

    public function getProduct($product_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `product_id` = :product_id");
        $stmt->execute(['product_id' => $product_id]);
        return $stmt->fetch();
    }

    public function getAllProducts() {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}`");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
