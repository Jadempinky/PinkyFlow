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
        try {
            // Check for similar category names up to 90% similarity
            $similarCategories = $this->getSimilarCategories($name, 90);
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

            getCategoryFolder($name);
            return $this->db->getLastInsertId();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to create category.");
        }
    }

    // Function to check for similar category names
    public function getSimilarCategories($name, $similarityThreshold = 90) {
        try {
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
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Error fetching similar categories.");
        }
    }

    // Fetch a category by ID
    public function getCategoryById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `id` = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to fetch category by ID.");
        }
    }

    // Fetch a category by Name
    public function getCategoryByName($name) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `name` = :name");
            $stmt->execute(['name' => $name]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to fetch category by name.");
        }
    }

    // Fetch all subcategories
    public function getSubcategories($parent_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `parent_id` = :parent_id");
            $stmt->execute(['parent_id' => $parent_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to fetch subcategories.");
        }
    }

    // Fetch all categories
    public function getAllCategories() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM `{$this->table}`");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to fetch categories.");
        }
    }

    // Update a category
    public function updateCategory($id, $name, $description = '', $image = '', $parent_id = null) {
        try {
            $params = [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'image' => $image
            ];
    
            // Base fields to update
            $fields = "`name` = :name, `description` = :description, `image` = :image";
    
            // Check if parent_id is provided
            if ($parent_id !== null) {
                $params['parent_id'] = $parent_id;
                $fields .= ", `parent_id` = :parent_id";
            }
    
            // Prepare and execute the update statement
            $stmt = $this->db->prepare("UPDATE `{$this->table}` SET $fields WHERE `id` = :id");
            $stmt->execute($params);
    
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to update category.");
        }
    }
    

    // Delete a category
    public function deleteCategory($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `id` = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to delete category.");
        }
    }

    
    // Add image to category
    public function addCategoryImage($id, $image) {
        try {
            // Get the current image
            $stmt = $this->db->prepare("SELECT `image` FROM `{$this->table}` WHERE `id` = :id");
            $stmt->execute(['id' => $id]);
            $oldImage = $stmt->fetchColumn();
            if ($oldImage) {
                $oldImages = explode(",", $oldImage);
                $newImages = array_merge($oldImages, [$image]);
                $newImageString = implode(",", $newImages);
            } else {
                $newImageString = $image;
            }
    
            $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `image` = :image WHERE `id` = :id");
            $stmt->execute(['id' => $id, 'image' => $newImageString]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to add image to category.");
        }
    }
    
    // Add description to category
    public function setCategoryDescription($id, $description) {
        try {
            $stmt = $this->db->prepare("UPDATE `{$this->table}` SET `description` = :description WHERE `id` = :id");
            $stmt->execute(['id' => $id, 'description' => $description]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw new Exception("Failed to add description to category.");
        }
    }

    public function getCategoryFolder($name) {
        // Dynamically determine the root directory (ensure it's based on the absolute path)
        $rootDir = $_SERVER['DOCUMENT_ROOT'] . '/';  // Ensures we start from the document root
        
        // Define potential folder paths relative to the root directory
        $possiblepaths = [
            'assets/shop/pictures',
            'images/shop',
            'img/shop',
            'pics/shop',
            'pictures/shop',
            'assets/shop',
            'assets/images/shop',
            'assets/img/shop',
            'assets/pics/shop',
            'assets/pictures/shop',
            'shop',
            'shop/images',
            'shop/img',
            'shop/pics',
            'shop/pictures',
            'shop/assets',
            'shop/assets/images',
            'shop/assets/img',
            'shop/assets/pics',
            'shop/assets/pictures',
            'assets/shop/images',
            'assets/shop/img',
            'assets/shop/pics'
        ];
    
        $imageFolder = '';
    
        // Try to find an existing path
        foreach ($possiblepaths as $folderName) {
            $fullPath = $rootDir . $folderName;
            if (file_exists($fullPath)) {
                $imageFolder = $fullPath;
                break;
            }
        }
    
        // If no valid folder is found, start creating the path step by step
        if (!$imageFolder) {
            $imageFolder = $rootDir . $possiblepaths[0];  // Default to the first option in the list
            $folders = explode('/', $possiblepaths[0]);   // Split the folder path into parts
            $currentPath = $rootDir;                      // Start from the root directory
    
            foreach ($folders as $folder) {
                $currentPath .= $folder . '/';            // Build the path step by step
                if (!file_exists($currentPath)) {         // Check if the folder exists
                    if (!mkdir($currentPath, 0755, true)) { // Create the folder if it doesn't exist
                        throw new Exception("Failed to create folder: $currentPath");
                    }
                }
            }
        }
    
        // Create category folder within the selected image folder
        $categoryFolder = $imageFolder . '/' . $name;
    
        if (!file_exists($categoryFolder)) {
            if (!mkdir($categoryFolder, 0755, true)) {
                throw new Exception("Failed to create the category folder: $categoryFolder");
            }
        }
    
        // Optionally return the folder path for further use
        return $categoryFolder;
    }
}    
?>
