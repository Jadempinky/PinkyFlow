<?php
require_once __DIR__ . '/PinkyFlow.php';  // Load PinkyFlow

// HTML structure for registration, login, comment submission, and display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    h1, h3 {
        color: #333;
        text-align: center;
        margin: 20px;
    }

    form {
        background-color: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 20px;
        max-width: 600px;
        margin: auto;
    }

    form input, form textarea {
        width: calc(100% - 20px);
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    form button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    form button:hover {
        background-color: #0056b3;
    }

    .comments-section {
        max-width: 800px;
        margin: 20px auto;
        background-color: white;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
    }

    .comment {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .comment-header {
        font-weight: bold;
        color: #007bff;
    }

    .comment-body {
        margin-top: 10px;
        color: #333;
    }

    .comment-reply {
        margin-left: 40px;
        margin-top: 10px;
        padding-left: 10px;
        border-left: 2px solid #007bff;
    }

    .comment-reply textarea {
        margin-top: 10px;
        width: 100%;
        height: 60px;
    }

    .comment button.reply-button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        margin-top: 10px;
        transition: background-color 0.3s;
    }

    .comment button.reply-button:hover {
        background-color: #218838;
    }

    /* Nested replies styling */
    .comment-reply .comment {
        background-color: #eef7ff;
    }
</style>

</head>
<body>
    <?php
    echo "<h1>Comment System Test</h1>";
    
    // Register a new user form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
        $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    try {
        $user->register($username, $password, $password, $email);
        echo "User registered successfully!<br>";
    } catch (Exception $e) {
        echo "Error registering user: " . $e->getMessage() . "<br>";
    }
}

// Log-in the user form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $user->login($username, $password);
        echo "User logged in successfully!<br>";
    } catch (Exception $e) {
        echo "Error logging in user: " . $e->getMessage() . "<br>";
    }
}

$userId = $user->getUid() ?? null;  // Get logged-in user ID

if ($userId) {
    echo "<p>Welcome, user #$userId!</p>";
}

// Comment form for logged-in users
if ($userId && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $productId = 101;  // Example product ID
    $commentText = $_POST['comment'];
    $rating = $_POST['rating'];
    
    try {
        $comment->addComment($userId, $productId, $commentText, $rating);
        echo "Comment added successfully!<br>";
    } catch (Exception $e) {
        echo "Error adding comment: " . $e->getMessage() . "<br>";
    }
}

// Reply to a comment
if ($userId && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $productId = 101;  // Same product ID
    $commentText = $_POST['reply_comment'];
    $replyTo = $_POST['reply_to'];
    
    try {
        $comment->addComment($userId, $productId, $commentText, null, $replyTo);
        echo "Reply added successfully!<br>";
    } catch (Exception $e) {
        echo "Error adding reply: " . $e->getMessage() . "<br>";
    }
}

// Display comments for a specific product (e.g., product with ID 101)
echo "<h3>Comments for product ID 101:</h3>";
try {
    $comments = $comment->getComments(101);
    
    // Recursive function to display comments and replies
    function displayComments($comments, $parentId = null, $depth = 0) {
        foreach ($comments as $commentData) {
            if ($commentData['reply_to'] == $parentId) {
                if ($depth > 0) {
                    echo "<p class='comment-reply'><strong>User #{$commentData['uid']}:</strong> {$commentData['comment']} <br>";
                    echo "Rating: " . ($commentData['rating'] ?? 'N/A') . "<br>";
                    echo "<form method='POST'>
                        <input type='hidden' name='reply_to' value='{$commentData['id']}'>
                        <textarea name='reply_comment' placeholder='Reply...'></textarea>
                        <button type='submit' name='reply'>Reply</button>
                    </form>";
                    echo "</p>";
                } else {
                    echo "<p class='comment'><strong>User #{$commentData['uid']}:</strong> {$commentData['comment']} <br>";
                    echo "Rating: " . ($commentData['rating'] ?? 'N/A') . "<br>";
                    echo "</p>";
                    echo "<form method='POST'>
                        <input type='hidden' name='reply_to' value='{$commentData['id']}'>
                        <textarea name='reply_comment' placeholder='Reply...'></textarea>
                        <button type='submit' name='reply'>Reply</button>
                    </form>";
                }
                echo str_repeat('&nbsp;', $depth * 5);  // Indent based on depth
                
                // Recursively display replies
                displayComments($comments, $commentData['id'], $depth + 1);
            }
        }
    }
    
    displayComments($comments);  // Start displaying comments and replies
} catch (Exception $e) {
    echo "Error displaying comments: " . $e->getMessage();
}

?>

<!-- Registration Form -->
<h3>Register</h3>
<form method="POST">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit" name="register">Register</button>
</form>

<!-- Login Form -->
<h3>Login</h3>
<form method="POST">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <button type="submit" name="login">Login</button>
</form>

<!-- Add Comment Form (if logged in) -->
<?php if ($userId): ?>
    <h3>Add a Comment</h3>
    <form method="POST">
        <textarea name="comment" placeholder="Write your comment..." required></textarea><br>
        <label for="rating">Rating (1-5):</label>
        <input type="number" name="rating" min="1" max="5" required><br>
        <button type="submit" name="add_comment">Submit Comment</button>
    </form>
    <?php endif; ?>
    
</body>
</html>