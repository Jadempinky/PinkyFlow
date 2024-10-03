<?php
namespace PinkyFlow\Modules;

use Exception;

class Category {
    private $db;
    private $table;

    public function __construct($db) {
        $this->db = $db;
        $this->table = 'categories';
        $this->verifyTable();
    }

    private function verifyTable() {
        if (!$this->db->checkTable($this->table)) {
            $options = "
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT,
                `image` VARCHAR(255),
                `parent_id` INT,
                FOREIGN KEY (`parent_id`) REFERENCES `{$this->table}`(`id`) ON DELETE CASCADE
            ";
            try {
                $this->db->createTable($this->table, $options);
            } catch (Exception $e) {
                error_log($e->getMessage());
                echo "An error occurred while creating the categories table.";
            }
        }
    }

    // Function to create a category with similarity check
    public function createCategory($name, $description = '', $image = '', $parent_id = null) {
        // Check for similar category names up to 80% similarity
        $similarCategories = $this->getSimilarCategories($name, 80);
        if (!empty($similarCategories)) {
            throw new Exception("A similar category name already exists.");
        }

        // Insert new category
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`name`, `description`, `image`, `parent_id`) VALUES (:name, :description, :image, :parent_id)");
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'parent_id' => $parent_id
        ]);

        return $this->db->getLastInsertId();
    }

    // Function to check for similar category names
    public function getSimilarCategories($name, $similarityThreshold = 80) {
        $stmt = $this->db->prepare("SELECT `name` FROM `{$this->table}`");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        $similarCategories = [];
        foreach ($categories as $category) {
            similar_text(strtolower($name), strtolower($category['name']), $percent);
            if ($percent >= $similarityThreshold) {
                $similarCategories[] = $category['name'];
            }
        }
        return $similarCategories;
    }

    // Additional methods for category management
    public function getCategoryById($id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `id` = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getCategoryByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `name` = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch();
    }

    public function getSubcategories($parent_id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `parent_id` = :parent_id");
        $stmt->execute(['parent_id' => $parent_id]);
        return $stmt->fetchAll();
    }
    

    public function getAllCategories() {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}`");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateCategory($id, $name, $description = '', $image = '', $parent_id = null) {
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `name` = :name, `description` = :description, `image` = :image, `parent_id` = :parent_id WHERE `id` = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'parent_id' => $parent_id
        ]);
    }

    public function deleteCategory($id) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = :id");
        $stmt->execute(['id' => $id]);
    }
}
?>