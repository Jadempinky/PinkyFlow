<?php
// registration.php

// Include the initialization script
require_once 'PinkyFlow.php';

// Access the initialized objects

// Check if the user module is enabled and the object is available
if (isset($PinkyFlowObjects['PinkyFlowUser'])) {
    $user = $PinkyFlowObjects['PinkyFlowUser'];
} else {
    die('User module is not enabled or failed to initialize.');
}

// Example user credentials (you can replace these with user input from a form)
$username = 'newuser';
$password = 'securepassword123';
$verifyPassword = 'securepassword123';
$email = 'pVqJf@example.com';

// Register the new user
try {
    // Attempt to register the new user
    $user->register($username, $password, $verifyPassword, $email);
    echo 'User registered successfully!';
} catch (Exception $e) {
    // Handle errors (e.g., username already exists)
    echo 'Error: ' . $e->getMessage();
}
?>
