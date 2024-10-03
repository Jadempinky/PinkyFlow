<?php
require_once __DIR__ . '/src/Core/Autoloader.php';
use PinkyFlow\Core\PinkyFlowDB;
use PinkyFlow\Modules\User;
use PinkyFlow\Modules\Shop;
use PinkyFlow\Modules\Product;
use PinkyFlow\Modules\Comment;

// Load configuration
use PinkyFlow\Config;

// Initialize the objects based on enabled modules
$objects = [];
if (Config::$enableDatabase) {
    $db = new PinkyFlowDB(Config::$dbHost, Config::$dbUser, Config::$dbPass, Config::$dbName);
    $objects['PinkyFlowDB'] = $db;
}
if (Config::$enableUserModule) {
    $user = new User($db);
    $objects['User'] = $user;
}
if (Config::$enableShoppingModule) {
    $shop = new Shop($db, $user);
    $objects['Shop'] = $shop;
    $product = new Product($db);
    $objects['Product'] = $product;
}
if (Config::$enableCommentModule) {
    $comment = new Comment($db, $user);
    $objects['Comment'] = $comment;
}

return $objects;
?>
