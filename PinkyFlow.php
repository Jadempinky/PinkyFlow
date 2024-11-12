<?php
require_once __DIR__ . '/src/Core/Autoloader.php'; // Autoloader

use PinkyFlow\Config;
use PinkyFlow\Core\PinkyFlowDB;
use PinkyFlow\Modules\User;
use PinkyFlow\Modules\Shop;
use PinkyFlow\Modules\Product;
use PinkyFlow\Modules\Comment;
use PinkyFlow\Modules\Favorite;


// Load the configuration file
require_once __DIR__ . '/config/config.php';

function pinkyflow_initialize_globals() {
    global $db, $user, $shop, $product, $comment, $favorite;

    // Initialize the Database if enabled
    if (Config::$enableDatabase) {
        $db = new PinkyFlowDB(Config::$dbHost, Config::$dbUser, Config::$dbPass, Config::$dbName);
    }

    // Initialize the User module if enabled
    if (Config::$enableUserModule) {
        $user = new User($db); // Pass the database object to the User class
        $favorite = new Favorite($db, $user);
    }

    // Initialize the Shopping module if enabled
    if (Config::$enableShoppingModule) {
        $shop = new Shop($db, $user); // Pass the database and user objects
        $product = new Product($db);  // Initialize the product module
    }

    // Initialize the Comment module if enabled
    if (Config::$enableCommentModule) {
        $comment = new Comment($db, $user); // Initialize the comment module
    }
}

// Initialize all objects and assign to global scope
pinkyflow_initialize_globals();
?>
