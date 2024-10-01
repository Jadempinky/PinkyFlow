<?php

/*
Note to self, to add
////////////////////////////////////////////////////////////////////////////////////////////////////
DONE ----> Add a favourite option
DONE ----> Make Wish list != Favourite
DONE ----> Make a wish list for items
DONE ----> Add a role optiion to the user
DOING ---> Add reviews, comments, and rating
DOING ---> Add a cart and change certain functions to check for role


Add easy discount function
Get the best rated items with a function getting $num of them
When doing the js file, link it to the DB for shop autocompletion
Edit the shop db to properly incorporate users and other needed things
Add categories, and subcategories
And, add an option for certain subcategories to show different options
For example (Clothes > Shirt > Large, Medium, Small)
Add a function to add / Get and edit categories, subcategories, etc

////////////////////////////////////////////////////////////////////////////////////////////////////



DO NOT FORGET TO FINISH COMMENT SECTION

ADD CATEGORY TO SHOP ITEMS




////////////////////////////////////////////////////////////////////////////////////////////////////
*/

if (!isset($enableDatabase)) {
    $enableDatabase = false;
}
if (!isset($enableUserModule)) {
    $enableUserModule = false;
}
if (!isset($enableShoppingModule)) {
    $enableShoppingModule = false;
}
if (!isset($enableCommentModule)) {
    $enableCommentModule = false;
}

// Update conditional checks accordingly
if ($enableUserModule) {
    $enableDatabase = true; // Ensure database is enabled if user functionality is required
}
if ($enableShoppingModule) {
    $enableDatabase = true; // Ensure database is enabled if shopping functionality is required
    $enableUserModule = true;
}

if ($enableCommentModule) {
    $enableDatabase = true;
    $enableUserModule = true;
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
                $dsn = "mysql:host={$this->host}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                try {
                    $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
                } catch (PDOException $e) {
                    die("Database connection failed: " . $e->getMessage());
                }

                if (!$this->verifyDatabase()) {
                    $this->createDB($this->db);
                }

                $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
                try {
                    $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
                } catch (PDOException $e) {
                    die("Database selection failed: " . $e->getMessage());
                }
            }

            public function createDB($dbName) {
                $sql = "CREATE DATABASE IF NOT EXISTS `$dbName`";
                $this->query($sql);
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
                $sql = "CREATE TABLE IF NOT EXISTS `$tableName` ($options) ENGINE=InnoDB;";
                $this->query($sql);
            }

            public function prepare($sql) {
                return $this->conn->prepare($sql);
            }

            public function verifyInTable($tableName, $column, $value) {
                $stmt = $this->conn->prepare("SELECT * FROM `$tableName` WHERE `$column` = :value");
                $stmt->execute(['value' => $value]);
                return $stmt->rowCount() > 0;
            }

            public function verifyDatabase() {
                $sql = "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :dbName";
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute(['dbName' => $this->db]);
                    $result = $stmt->fetch(); // This could return false if the schema doesn't exist

                    if ($result === false) {
                        // Database does not exist
                        return false;
                    }

                    return true;
                } catch (PDOException $e) {
                    error_log("Database verification failed: " . $e->getMessage());
                    return false;
                }
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
                $this->comment = new PinkyFlowComment($db, $this->table, $this);                
                if (!$enableCommentModule) {
                    $this->comment = "A";
                }

                if (session_status() == PHP_SESSION_NONE) {
                    session_name($this->sessionname);
                    session_start();
                }
                if (isset($_SESSION['uid'])) {
                    $this->uid = $_SESSION['uid'];
                }

                $this->Verify();
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

                $uid = uniqid();
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
    }
}

if ($enableShoppingModule) {
    if (!class_exists('PinkyFlowProduct')) {
        class PinkyFlowProduct {
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
                        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ";
                    try {
                        $this->db->createTable($this->table, $options);
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        echo "An error occurred while creating the products table.";
                    }
                }
            }

            public function createProduct($name, $description, $price, $stock, $image) {
                $product_id = uniqid('', true); // More unique product_id
                $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`product_id`, `name`, `description`, `price`, `stock`, `image`) VALUES (:product_id, :name, :description, :price, :stock, :image)");
                $stmt->execute([
                    'product_id' => $product_id,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'image' => $image
                ]);
                return $product_id;
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
    }

    // PinkyFlowCart Class
    if (!class_exists('PinkyFlowCart')) {
        class PinkyFlowCart {
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
    }

    // PinkyFlowShop Class
    if (!class_exists('PinkyFlowShop')) {
        class PinkyFlowShop {
            private $db;
            public $user;
            private $product;
            private $cart;
            private $favorite; // Added for favorites
            private $wishlist; // Added for wishlist

            public function __construct($db, $user) {
                $this->db = $db;
                $this->user = $user;
                $this->product = new PinkyFlowProduct($db);
                $this->cart = new PinkyFlowCart($db, $user);
                $this->favorite = new PinkyFlowFavorite($db, $user); // Initialize favorites
                $this->wishlist = new PinkyFlowWishlist($db, $user); // Initialize wishlist
            }

            public function listProducts() {
                return $this->product->getAllProducts();
            }

            public function viewProduct($product_id) {
                return $this->product->getProduct($product_id);
            }

            public function addProduct($name, $description, $price, $stock, $image) {
                return $this->product->createProduct($name, $description, $price, $stock, $image);
            }

            public function updateProductStock($product_id, $quantity) {
                $this->product->updateStock($product_id, $quantity);
            }

            public function addToCart($product_id, $quantity) {
                $this->cart->addToCart($product_id, $quantity);
            }

            public function removeFromCart($product_id) {
                $this->cart->removeFromCart($product_id);
            }

            public function viewCart() {
                return $this->cart->getCartItems();
            }

            public function checkout() {
                $cartItems = $this->cart->getCartItems();
                foreach ($cartItems as $item) {
                    $new_stock = $item['stock'] - $item['quantity'];

                    if ($new_stock < 0) {
                        throw new Exception("Insufficient stock for product: " . $item['name']);
                    }

                    $this->product->updateStock($item['product_id'], $new_stock);
                }
                $this->cart->clearCart();
            }

            // Favorites Management
            public function addToFavorite($product_id) {
                $this->favorite->addToFavorite($product_id);
            }

            public function removeFromFavorite($product_id) {
                $this->favorite->removeFromFavorite($product_id);
            }

            public function getFavoriteItems() {
                return $this->favorite->getFavorites();
            }

            public function clearFavorites() {
                $this->favorite->clearFavorites();
            }

            // Wishlist Management
            public function addToWishlist($product_id) {
                $this->wishlist->addToWishlist($product_id);
            }

            public function removeFromWishlist($product_id) {
                $this->wishlist->removeFromWishlist($product_id);
            }

            public function getWishlistItems() {
                return $this->wishlist->getWishlist();
            }

            public function clearWishlist() {
                $this->wishlist->clearWishlist();
            }

            // Add getter methods if needed
            public function getProductClass() {
                return $this->product;
            }

            public function getCartClass() {
                return $this->cart;
            }

            public function getFavoriteClass() { // Added for favorites
                return $this->favorite;
            }

            public function getWishlistClass() { // Added for wishlist
                return $this->wishlist;
            }
        }
    }

    // PinkyFlowFavorite Class
    if (!class_exists('PinkyFlowFavorite')) {
        class PinkyFlowFavorite {
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
    }

    // PinkyFlowWishlist Class
    if (!class_exists('PinkyFlowWishlist')) {
        class PinkyFlowWishlist {
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

            // PinkyFlowWishlist Class
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
    }

    // PinkyFlowShopFavorite Class (Removed as separate class since favorites are now managed by PinkyFlowFavorite)
    // Similarly, PinkyFlowShopWishlist Class is not needed as PinkyFlowWishlist handles wishlist functionality.
}

if ($enableCommentModule) {
    if (!class_exists('PinkyFlowComment')) {
        class PinkyFlowComment {
            private $db;
            private $user;
            private $table;

            public function __construct($db, $user) {
                $this->db = $db;
                $this->user = $user;
                $this->table = 'comments';
                $this->verifyTable();
            }

            private function verifyTable() {
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
                        echo "An error occurred while creating the comments table.";
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
    }
}
?>
