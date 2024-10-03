<?php
require_once __DIR__ . '/PinkyFlow.php';  // Load PinkyFlow

// You should already have $user and $comment initialized from PinkyFlow
// Assuming you have a logged-in user with a valid user ID

// Test user ID (normally this would be obtained from the logged-in user session)

// Register a new user
echo "<h3>Registering a new user:</h3>";
$username = "TestUser";
$password = "TestPassword";
$email = "P6kS6@example.com";
try {
    $user->register($username, $password, $password, $email);
    echo "User registered successfully!";
} catch (Exception $e) {
    echo "Error registering user: " . $e->getMessage();
}

// Log-in the user
echo "<h3>Logging in the user:</h3>";
try {
    $user->login($username, $password);
    echo "User logged in successfully!";
} catch (Exception $e) {
    echo "Error logging in user: " . $e->getMessage();
}


$userId = $user->getUid();  // Assuming getUid() returns the logged-in user ID

// Test case: Adding a comment for a product
echo "<h3>Adding a comment for a product:</h3>";
try {
    $productId = 101;  // Example product ID
    $comment->addComment($userId, $productId, "This is a comment about a product.", 5);
} catch (Exception $e) {
    echo "Error adding product comment: " . $e->getMessage();
}

// Test case: Adding a comment for a profile
echo "<h3>Adding a comment for a profile:</h3>";
try {
    $profileId = 202;  // Example profile ID
    $comment->addComment($userId, $profileId, "This is a comment on a user profile.");
} catch (Exception $e) {
    echo "Error adding profile comment: " . $e->getMessage();
}

// Test case: Replying to an existing comment
echo "<h3>Replying to a comment:</h3>";
try {
    $replyToCommentId = 1;  // Example ID of the comment you're replying to
    $productId = 101;  // Same product ID to keep comments linked to the same product
    $comment->addComment($userId, $productId, "This is a reply to another comment.", null, $replyToCommentId);
} catch (Exception $e) {
    echo "Error adding reply: " . $e->getMessage();
}

// Display comments for a specific product (e.g., product with ID 101)
echo "<h3>Displaying comments for product with ID 101:</h3>";
try {
    $comments = $comment->getComments(101);
    foreach ($comments as $commentData) {
        echo "<p>Comment ID: " . $commentData['comment_id'] . "<br>";
        echo "User ID: " . $commentData['uid'] . "<br>";
        echo "Comment: " . $commentData['comment'] . "<br>";
        echo "Rating: " . $commentData['rating'] . "<br>";
        if ($commentData['reply_to'] !== null) {
            echo "Reply to: " . $commentData['reply_to'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Error displaying comments: " . $e->getMessage();
}

?>
