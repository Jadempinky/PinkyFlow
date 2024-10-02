<?php

/**
 * Check if config file exists, create it if it does not, and load the objects automatically
 */

// Use a nowdoc to prevent variable interpolation
$Config_content = <<<'EOD'
<?php

/*
Default config variables
*/

$enableDatabase = false;
$enableUserModule = false;
$enableShoppingModule = false;
$enableCommentModule = false;

// Database credentials
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'pinkyflow';

?>
EOD;

function pinkyflow_load_objects() {
    global $Config_content;
    $config_file = __DIR__ . '/config.php';
    
    // Check if config.php exists; if not, create it with default contents
    if (!file_exists($config_file)) {
        file_put_contents($config_file, $Config_content);
    }
    
    // Include config.php to get configuration variables
    require_once $config_file;
    
    // Define the Config class and set its static properties
    class Config {
        public static $enableDatabase = false;
        public static $enableUserModule = false;
        public static $enableShoppingModule = false;
        public static $enableCommentModule = false;

        public static $dbHost = 'localhost';
        public static $dbUser = 'root';
        public static $dbPass = '';
        public static $dbName = 'pinkyflow';
    }
    
    // Set the Config class properties using variables from config.php
    Config::$enableDatabase = isset($enableDatabase) ? $enableDatabase : Config::$enableDatabase;
    Config::$enableUserModule = isset($enableUserModule) ? $enableUserModule : Config::$enableUserModule;
    Config::$enableShoppingModule = isset($enableShoppingModule) ? $enableShoppingModule : Config::$enableShoppingModule;
    Config::$enableCommentModule = isset($enableCommentModule) ? $enableCommentModule : Config::$enableCommentModule;

    Config::$dbHost = isset($dbHost) ? $dbHost : Config::$dbHost;
    Config::$dbUser = isset($dbUser) ? $dbUser : Config::$dbUser;
    Config::$dbPass = isset($dbPass) ? $dbPass : Config::$dbPass;
    Config::$dbName = isset($dbName) ? $dbName : Config::$dbName;

    // Include the class definitions
    require_once __DIR__ . '/PinkyFlow-Objects.php';
    
    // Initialize the enabled modules
    $objects = [];
    if (Config::$enableDatabase) {
        $db = new PinkyFlowDB(Config::$dbHost, Config::$dbUser, Config::$dbPass, Config::$dbName);
        $objects['PinkyFlowDB'] = $db;
    }
    if (Config::$enableUserModule) {
        $user = new PinkyFlowUser($db);
        $objects['PinkyFlowUser'] = $user;
    }
    if (Config::$enableShoppingModule) {
        $product = new PinkyFlowProduct($db);
        $shop = new PinkyFlowShop($db, $user);
        $objects['PinkyFlowProduct'] = $product;
        $objects['PinkyFlowShop'] = $shop;
    }
    if (Config::$enableCommentModule) {
        $comment = new PinkyFlowComment($db, $user);
        $objects['PinkyFlowComment'] = $comment;
    }

    // Return the initialized objects
    return $objects;
}

// Call the function and retrieve the objects
$PinkyFlowObjects = pinkyflow_load_objects();

// Now you can use $PinkyFlowObjects['PinkyFlowDB'], $PinkyFlowObjects['PinkyFlowUser'], etc.

?>
