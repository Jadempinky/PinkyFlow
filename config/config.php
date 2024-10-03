<?php

namespace PinkyFlow;
class Config {
    public static $enableDatabase = true;
    public static $enableUserModule = true;
    public static $enableShoppingModule = true;
    public static $enableCommentModule = true;

    public static $dbHost = 'localhost';
    public static $dbUser = 'root';
    public static $dbPass = '';
    public static $dbName = 'pinkyflow';
}

// Optionally, you can override default values by setting these variables in this file
$enableDatabase = true;
$enableUserModule = true;
$enableShoppingModule = true;
$enableCommentModule = true;

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'pinkyflow';

// Apply any overrides to the Config class
Config::$enableDatabase = isset($enableDatabase) ? $enableDatabase : Config::$enableDatabase;
Config::$enableUserModule = isset($enableUserModule) ? $enableUserModule : Config::$enableUserModule;
Config::$enableShoppingModule = isset($enableShoppingModule) ? $enableShoppingModule : Config::$enableShoppingModule;
Config::$enableCommentModule = isset($enableCommentModule) ? $enableCommentModule : Config::$enableCommentModule;

Config::$dbHost = isset($dbHost) ? $dbHost : Config::$dbHost;
Config::$dbUser = isset($dbUser) ? $dbUser : Config::$dbUser;
Config::$dbPass = isset($dbPass) ? $dbPass : Config::$dbPass;
Config::$dbName = isset($dbName) ? $dbName : Config::$dbName;
?>
