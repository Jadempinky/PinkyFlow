<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PinkyFlow Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .nav {
            margin-bottom: 20px;
        }
        .nav a {
            margin-right: 15px;
            text-decoration: none;
            color: blue;
        }
        .product, .favorite-item, .wishlist-item, .cart-item {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 10px;
        }
        .cart-item, .favorite-item, .wishlist-item {
            border: none;
            padding: 10px 0;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input[type="text"], input[type="password"], input[type="email"], input[type="number"], textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        input[type="submit"], button {
            margin-top: 15px;
            padding: 10px 20px;
        }
        .actions form {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>
<body>



<h1>PinkyFlow Test</h1>
<?php

// Enable error reporting for debugging (optional)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable Modules
$enableCommentModule = true; // Enable comment module for testing
$enableUserModule = true;
$enableShoppingModule = true;

require_once("PinkyFlow-Objects.php");

// Initialize Database
try {
    $db = new PinkyFlowDB("localhost", "root", "", "pinkyflow");
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}

// Initialize User Module
if ($enableUserModule) {
    $user = new PinkyFlowUser($db);
    $user->Verify(); // Ensure users table exists
}

if ($enableCommentModule && isset($user)) {
    $comment = new PinkyFlowComment($db, $user);
}


// Initialize Shop Module
if ($enableShoppingModule && isset($user)) {
    $shop = new PinkyFlowShop($db, $user);
    // Insert sample products if none exist
    $products = $shop->listProducts();
    if (empty($products)) {
        // Sample products data
        $sampleProducts = [
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with adjustable DPI.',
                'price' => 25.99,
                'stock' => 50,
                'image' => 'images/wireless_mouse.jpg'
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB backlit mechanical keyboard with blue switches.',
                'price' => 79.99,
                'stock' => 30,
                'image' => 'images/mechanical_keyboard.jpg'
            ],
            [
                'name' => 'HD Webcam',
                'description' => '1080p HD webcam with built-in microphone.',
                'price' => 49.99,
                'stock' => 20,
                'image' => 'images/hd_webcam.jpg'
            ]
        ];

        foreach ($sampleProducts as $prod) {
            try {
                $shop->addProduct(
                    $prod['name'],
                    $prod['description'],
                    $prod['price'],
                    $prod['stock'],
                    $prod['image']
                );
            } catch (Exception $e) {
                echo "<p class='error'>Error adding product: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        echo "<p class='success'>Sample products have been added.</p>";
    }
}

// Handle Comment Submission
if (isset($_GET['add_comment']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];
    $comment = trim($_POST['comment'] ?? '');
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

    try {
        if (empty($comment)) {
            throw new Exception("Comment cannot be empty.");
        }

        if ($rating !== null && ($rating < 1 || $rating > 5)) {
            throw new Exception("Rating must be between 1 and 5.");
        }

        if ($rating == null) {
            throw new Exception("Rating is not supported yet.");
        }

        if (!$shop->user->comment->verifyTable()) {
            throw new Exception("Comment module not initialized.");
        }

        if (!$shop->productExists($product_id)) {
            throw new Exception("Product does not exist.");
        }
        $shop->user->comment->addComment($product_id, $comment, $shop->user->getUid(), $rating);
        echo "<p class='success'>Comment added successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}


// Handle View Product Comments
if (isset($_GET['view_comments']) && isset($shop)) {
    $product_id = $_GET['product_id'];
    echo "<h2>Comments and Reviews</h2>";
    $comments = $shop->user->comment->getComments($product_id);
    if (empty($comments)) {
        echo "<p>No comments yet.</p>";
    } else {
        foreach ($comments as $comm) {
            echo "<div class='comment-item'>";
            echo "<p><strong>User:</strong> " . htmlspecialchars($comm['uid']) . "</p>";
            echo "<p><strong>Comment:</strong> " . htmlspecialchars($comm['comment']) . "</p>";
            if (isset($comm['rating'])) {
                echo "<p><strong>Rating:</strong> " . (int)$comm['rating'] . "/5</p>";
            }
            echo "<p><small>Posted on: " . htmlspecialchars($comm['added_at']) . "</small></p>";
            echo "</div><br>";
        }
    }
    echo "<br><a href='index.php'>Back to Home</a>";
    exit;
}

// Navigation Links
echo "<div class='nav'>";
if (isset($user) && $user->isLoggedIn()) {
    $username = $user->getUsername();

    if ($username !== null) {
        echo "Logged in as " . htmlspecialchars($username);
    } else {
        echo "Logged in as Unknown User";
    }

    echo " | <a href='?logout'>Log out</a> | ";
    if ($enableShoppingModule) {
        echo "<a href='?action=list_products'>View Products</a> | ";
        echo "<a href='?action=view_cart'>View Cart</a> | ";
        echo "<a href='?view_favorites'>View Favorites</a> | ";
        echo "<a href='?view_wishlist'>View Wishlist</a>";
    }
} else {
    echo "<a href='?login_form'>Log in</a> | ";
    echo "<a href='?register_form'>Register</a>";
}
echo "</div>";

// Main Content Handling
if (isset($user) && $user->isLoggedIn() && $enableShoppingModule) {
    // Handle different shop actions
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'list_products':
            echo "<h2>Product List</h2>";
            $products = $shop->listProducts();
            if (empty($products)) {
                echo "<p>No products available.</p>";
            } else {
                foreach ($products as $product) {
                    echo "<div class='product'>";
                    echo "<h3>" . htmlspecialchars($product['name']) . "</h3>";
                    echo "<p>" . htmlspecialchars($product['description']) . "</p>";
                    echo "<p>Price: $" . number_format($product['price'], 2) . "</p>";
                    echo "<p>Stock: " . (int)$product['stock'] . "</p>";
                    if ($product['image']) {
                        echo "<img src='" . htmlspecialchars($product['image']) . "' alt='" . htmlspecialchars($product['name']) . "' style='max-width:150px;'><br>";
                    }

                    if ($product['stock'] > 0) {
                        echo "<form action='?add_to_cart=true&product_id=" . urlencode($product['product_id']) . "' method='post' style='display:inline-block; margin-right:10px;'>";
                        echo "<label for='quantity'>Quantity:</label>";
                        echo "<input type='number' name='quantity' id='quantity' min='1' max='" . (int)$product['stock'] . "' value='1' required>";
                        echo "<input type='submit' value='Add to Cart'>";
                        echo "</form>";

                        // Add to Favorites Form
                        echo "<form action='?add_to_favorite=true&product_id=" . urlencode($product['product_id']) . "' method='post' style='display:inline-block; margin-right:10px;'>
                                <input type='submit' value='Add to Favorites'>
                              </form>";

                        // Add to Wishlist Form
                        echo "<form action='?add_to_wishlist=true&product_id=" . urlencode($product['product_id']) . "' method='post' style='display:inline-block;'>
                                <input type='submit' value='Add to Wishlist'>
                              </form>";

                        // Add Comment Form
                        echo "<h4>Add a Review/Comment</h4>";
                        echo "<form action='?add_comment=true&product_id=" . urlencode($product['product_id']) . "' method='post'>";
                        echo "<label for='comment'>Comment:</label>";
                        echo "<textarea name='comment' id='comment' required></textarea>";
                        echo "<label for='rating'>Rating (1-5):</label>";
                        echo "<input type='number' name='rating' id='rating' min='1' max='5'>";
                        echo "<input type='submit' value='Submit'>";
                        echo "</form>";
                    } else {
                        echo "<p><em>Out of stock</em></p>";
                    }

                    // View Comments
                    echo "<a href='?view_comments=true&product_id=" . urlencode($product['product_id']) . "'>View Comments/Reviews</a>";
                    echo "</div>";
                }
            }
            break;

        case 'view_cart':
            echo "<h2>Your Cart</h2>";
            $cartItems = $shop->viewCart();
            if (empty($cartItems)) {
                echo "<p>Your cart is empty.</p>";
            } else {
                foreach ($cartItems as $item) {
                    echo "<div class='cart-item'>";
                    echo "<h3>" . htmlspecialchars($item['name']) . "</h3>";
                    echo "<p>Price: $" . number_format($item['price'], 2) . "</p>";
                    echo "<p>Quantity: " . (int)$item['quantity'] . "</p>";
                    echo "<p>Subtotal: $" . number_format($item['price'] * $item['quantity'], 2) . "</p>";
                    echo "<a href='?remove_from_cart=true&product_id=" . urlencode($item['product_id']) . "' onclick=\"return confirm('Are you sure you want to remove this item?');\">Remove</a>";
                    echo "</div>";
                }
                echo "<h3>Total: $" . number_format(array_reduce($cartItems, function($carry, $item) {
                    return $carry + ($item['price'] * $item['quantity']);
                }, 0), 2) . "</h3>";
                echo "<a href='?checkout=true' onclick=\"return confirm('Proceed to checkout?');\"><button>Checkout</button></a>";
            }
            break;

        default:
            echo "<h2>Welcome to PinkyFlow Shop</h2>";
            echo "<p>Explore our products by clicking <a href='?action=list_products'>here</a>.</p>";
            break;
    }
} else {
    if (isset($_GET['login_form'])) {
        echo "<h2>Login</h2>";
        echo "<form action='?login=true' method='post'>
            <label for='username'>Username:</label>
            <input type='text' name='username' id='username' required>
            <br>
            <label for='password'>Password:</label>
            <input type='password' name='password' id='password' required>
            <br>
            <input type='submit' value='Log in'>
        </form>";
        echo "<br><a href='index.php'>Back to Home</a>";
    } elseif (isset($_GET['register_form'])) {
        echo "<h2>Register</h2>";
        echo "<form action='?register=true' method='post'>
            <label for='username'>Username:</label>
            <input type='text' name='username' id='username' required>
            <br>
            <label for='password'>Password:</label>
            <input type='password' name='password' id='password' required>
            <br>
            <label for='verifyPassword'>Verify Password:</label>
            <input type='password' name='verifyPassword' id='verifyPassword' required>
            <br>
            <label for='email'>Email:</label>
            <input type='email' name='email' id='email' required>
            <br>
            <input type='submit' value='Register'>
        </form>";
        echo "<br><a href='index.php'>Back to Home</a>";
    } else {
        echo "<p>Welcome to PinkyFlow! Please <a href='?login_form'>log in</a> or <a href='?register_form'>register</a> to continue.</p>";
    }
}
?>
</body>
</html>
