<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment System with Like/Dislike</title>
    <style>
    /* Styles for the page and comments */
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

    /* Styling for like/dislike buttons */
    .like-button, .dislike-button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        margin-top: 5px;
        transition: background-color 0.3s;
    }

    .like-button:hover, .dislike-button:hover {
        background-color: #0056b3;
    }

    .likes-count, .dislikes-count {
        display: inline-block;
        margin-left: 5px;
        font-weight: bold;
    }
    </style>

    <script>
        function toggleReplyForm(commentId) {
            var form = document.getElementById('reply-form-' + commentId);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>

</head>
<body>
    <?php
    require_once __DIR__ . '/PinkyFlow.php';

    echo "<h1>" + $commentsystem + "/h1>";

    $userId = $user->getUid() ?? null;  // Get logged-in user ID

    if ($userId) {
        echo "<p>Welcome, " . $user->getUsernameFromUid($userId) . "!</p>";
    }

    // Register form
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $username . '@example.com';
        try {
            $user->register($username, $password, $password, $email);
            echo "Registered successfully! <br>";
            header("Refresh:0");
        } catch (Exception $e) {
            echo "Error registering: " . $e->getMessage() . "<br>";
        }
    }

    // Log-in form
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        try {
            $user->login($username, $password);
            echo "Logged in successfully! <br>";
            header("Refresh:0");
        } catch (Exception $e) {
            echo "Error logging in: " . $e->getMessage() . "<br>";
        }
    }

    // Log-out form
    if (isset($_POST['logout'])) {
        try {
            $user->logout();
            echo "Logged out successfully! <br>";
            header("Refresh:0");
        } catch (Exception $e) {
            echo "Error logging out: " . $e->getMessage() . "<br>";
        }
    }

    // Handling Like/Dislike
    if ($userId && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
        $commentId = $_POST['comment_id'];
        try {
            $comment->likeComment($userId, $commentId);
            echo "Liked comment successfully!<br>";
            header("Refresh:0");
        } catch (Exception $e) {
            echo "Error liking comment: " . $e->getMessage() . "<br>";
        }
    }

    if ($userId && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dislike'])) {
        $commentId = $_POST['comment_id'];
        try {
            $comment->dislikeComment($userId, $commentId);
            echo "Disliked comment successfully!<br>";
            header("Refresh:0");
        } catch (Exception $e) {
            echo "Error disliking comment: " . $e->getMessage() . "<br>";
        }
    }
    ?>

    <form action="" method="post">
        <h2>Register or Login</h2>
        <label>Username: <input type="text" name="username"></label><br>
        <label>Password: <input type="password" name="password"></label><br>
        <input type="submit" name="register" value="Register">
        <input type="submit" name="login" value="Login">
        <input type="submit" name="logout" value="Logout">
    </form>

    <?php
    // Displaying comments with like/dislike
    echo "<h3>Comments for product ID 101:</h3>";
    try {
        $comments = $comment->getComments(101);

        echo "<h3>Leave a comment:</h3>";
        echo "<form method='post'>";
        echo "<textarea name='comment' rows='4' cols='50' placeholder='Your comment...'></textarea> <br>";
        echo "<input type='number' name='rating' min='0' max='5' step='0.5' placeholder='Rating (0-5)'><br>";
        echo "<input type='submit' name='add_comment' value='Add comment'>";
        echo "</form>";

        function displayComments($comments, $user, $parentId = null, $depth = 0) {
            foreach ($comments as $commentData) {
                if ($commentData['reply_to'] == $parentId) {
                    $commentClass = 'comment' . ($commentData['reply_to'] ? ' comment-reply' : '');
                    $likedBy = explode(',', $commentData['liked_by']);
                    $dislikedBy = explode(',', $commentData['disliked_by']);
                    $likesCount = count(array_filter($likedBy));
                    $dislikesCount = count(array_filter($dislikedBy));

                    echo "<p class='$commentClass'><strong>" . $user->getUsernameFromUid($commentData['uid']) . ":</strong> {$commentData['comment']} <br>";
                    echo "Rating: " . ($commentData['rating'] ?? 'N/A') . "<br>";
                    echo "<button class='like-button' type='submit' form='like-form-{$commentData['id']}'>Like</button> <span class='likes-count'>Likes: $likesCount</span> ";
                    echo "<button class='dislike-button' type='submit' form='dislike-form-{$commentData['id']}'>Dislike</button> <span class='dislikes-count'>Dislikes: $dislikesCount</span>";
                    echo "<button class='reply-button' onclick='toggleReplyForm({$commentData['id']})'>Reply</button>";

                    // Like form
                    echo "<form id='like-form-{$commentData['id']}' method='POST' style='display:none;'>";
                    echo "<input type='hidden' name='comment_id' value='{$commentData['id']}'>";
                    echo "<input type='hidden' name='like' value='1'>";
                    echo "</form>";

                    // Dislike form
                    echo "<form id='dislike-form-{$commentData['id']}' method='POST' style='display:none;'>";
                    echo "<input type='hidden' name='comment_id' value='{$commentData['id']}'>";
                    echo "<input type='hidden' name='dislike' value='1'>";
                    echo "</form>";

                    echo "<div id='reply-form-{$commentData['id']}' class='reply-form'>
                        <form method='POST'>
                            <input type='hidden' name='reply_to' value='{$commentData['id']}'>
                            <textarea name='reply_comment' placeholder='Reply...'></textarea>
                            <button type='submit' name='reply'>Submit Reply</button>
                        </form>
                    </div>";
                    echo "</p>";

                    displayComments($comments, $user, $commentData['id'], $depth + 1);
                }
            }
        }

        displayComments($comments, $user);
    } catch (Exception $e) {
        echo "Error displaying comments: " . $e->getMessage();
    }
    ?>
</body>
</html>
