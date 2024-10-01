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
    $user = new PinkyFlowUser($db, $enableCommentModule); // Pass $enableCommentModule as per previous fix
    $user->Verify(); // Ensure users table exists
}

// Initialize Comment Module
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

// Handle Logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    if (isset($user)) {
        $user->logout();
        echo "<p class='success'>Logged out successfully.</p>";
    }
    // Optionally, redirect to home after logout
    // header("Location: index.php");
    // exit;
}

// Handle Login Submission
if (isset($_GET['login']) && $_GET['login'] === 'true' && isset($user)) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        if (empty($username) || empty($password)) {
            throw new Exception("Both username and password are required.");
        }

        if ($user->login($username, $password)) {
            echo "<p class='success'>Logged in successfully!</p>";
        } else {
            throw new Exception("Invalid username or password.");
        }
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Register Submission
if (isset($_GET['register']) && $_GET['register'] === 'true' && isset($user)) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $verifyPassword = trim($_POST['verifyPassword'] ?? '');
    $email = trim($_POST['email'] ?? '');

    try {
        if (empty($username) || empty($password) || empty($verifyPassword) || empty($email)) {
            throw new Exception("All fields are required.");
        }

        if ($password !== $verifyPassword) {
            throw new Exception("Passwords do not match.");
        }

        $registrationResult = $user->register($username, $password, $verifyPassword, $email);

        if ($registrationResult === true) {
            echo "<p class='success'>Registration successful! You can now <a href='?login_form'>log in</a>.</p>";
        } else {
            // Assuming $registrationResult contains the error message
            throw new Exception($registrationResult);
        }
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Comment Submission
if (isset($_GET['add_comment']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];
    $commentText = trim($_POST['comment'] ?? '');
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

    try {
        if (empty($commentText)) {
            throw new Exception("Comment cannot be empty.");
        }

        if ($rating !== null && ($rating < 1 || $rating > 5)) {
            throw new Exception("Rating must be between 1 and 5.");
        }

        if ($rating == null) {
            // If rating is not supported yet, you can choose to set a default rating or handle accordingly
            // For now, we'll proceed without rating
            // Alternatively, uncomment the next line to throw an exception
            // throw new Exception("Rating is required.");
        }

        // Remove the verifyTable check as it might cause issues (the table should already exist)
        // if (!$shop->user->comment->verifyTable()) {
        //     throw new Exception("Comment module not initialized.");
        // }

        $shop->user->comment->addComment($product_id, $commentText, $shop->user->getUid(), $rating);
        echo "<p class='success'>Comment added successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Add to Cart
if (isset($_GET['add_to_cart']) && $_GET['add_to_cart'] === 'true' && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    try {
        if ($quantity < 1) {
            throw new Exception("Quantity must be at least 1.");
        }

        $shop->addToCart($product_id, $quantity);
        echo "<p class='success'>Product added to cart successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Remove from Cart
if (isset($_GET['remove_from_cart']) && $_GET['remove_from_cart'] === 'true' && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];

    try {
        $shop->removeFromCart($product_id);
        echo "<p class='success'>Product removed from cart successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Checkout
if (isset($_GET['checkout']) && $_GET['checkout'] === 'true' && isset($shop) && $shop->user->isLoggedIn()) {
    try {
        $shop->checkout();
        echo "<p class='success'>Checkout successful! Your order has been placed.</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Add to Favorite
if (isset($_GET['add_to_favorite']) && $_GET['add_to_favorite'] === 'true' && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];

    try {
        $shop->addToFavorite($product_id);
        echo "<p class='success'>Product added to favorites successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Remove from Favorite
if (isset($_GET['remove_from_favorite']) && $_GET['remove_from_favorite'] === 'true' && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];

    try {
        $shop->removeFromFavorite($product_id);
        echo "<p class='success'>Product removed from favorites successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Add to Wishlist
if (isset($_GET['add_to_wishlist']) && $_GET['add_to_wishlist'] === 'true' && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];

    try {
        $shop->addToWishlist($product_id);
        echo "<p class='success'>Product added to wishlist successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Handle Remove from Wishlist
if (isset($_GET['remove_from_wishlist']) && $_GET['remove_from_wishlist'] === 'true' && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
    $product_id = $_GET['product_id'];

    try {
        $shop->removeFromWishlist($product_id);
        echo "<p class='success'>Product removed from wishlist successfully!</p>";
    } catch (Exception $e) {
        echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
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

    echo " | <a href='?logout=true'>Log out</a> | ";
    if ($enableShoppingModule) {
        echo "<a href='?action=list_products'>View Products</a> | ";
        echo "<a href='?action=view_cart'>View Cart</a> | ";
        echo "<a href='?action=view_favorites'>View Favorites</a> | ";
        echo "<a href='?action=view_wishlist'>View Wishlist</a>";
    }
} else {
    echo "<a href='?login_form=true'>Log in</a> | ";
    echo "<a href='?register_form=true'>Register</a>";
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

        case 'view_favorites':
            echo "<h2>Your Favorites</h2>";
            $favoriteItems = $shop->getFavoriteItems();
            if (empty($favoriteItems)) {
                echo "<p>You have no favorite items.</p>";
            } else {
                foreach ($favoriteItems as $item) {
                    echo "<div class='favorite-item'>";
                    echo "<h3>" . htmlspecialchars($item['name']) . "</h3>";
                    echo "<p>" . htmlspecialchars($item['description']) . "</p>";
                    echo "<p>Price: $" . number_format($item['price'], 2) . "</p>";
                    echo "<p>Added on: " . htmlspecialchars($item['added_at']) . "</p>";
                    echo "<a href='?remove_from_favorite=true&product_id=" . urlencode($item['product_id']) . "' onclick=\"return confirm('Remove this item from favorites?');\">Remove from Favorites</a>";
                    echo "</div>";
                }
            }
            break;

        case 'view_wishlist':
            echo "<h2>Your Wishlist</h2>";
            $wishlistItems = $shop->getWishlistItems();
            if (empty($wishlistItems)) {
                echo "<p>Your wishlist is empty.</p>";
            } else {
                foreach ($wishlistItems as $item) {
                    echo "<div class='wishlist-item'>";
                    echo "<h3>" . htmlspecialchars($item['name']) . "</h3>";
                    echo "<p>" . htmlspecialchars($item['description']) . "</p>";
                    echo "<p>Price: $" . number_format($item['price'], 2) . "</p>";
                    echo "<p>Added on: " . htmlspecialchars($item['added_at']) . "</p>";
                    echo "<a href='?remove_from_wishlist=true&product_id=" . urlencode($item['product_id']) . "' onclick=\"return confirm('Remove this item from wishlist?');\">Remove from Wishlist</a>";
                    echo "</div>";
                }
            }
            break;

        default:
            echo "<h2>Welcome to PinkyFlow Shop</h2>";
            echo "<p>Explore our products by clicking <a href='?action=list_products'>here</a>.</p>";
            break;
    }
} else {
    if (isset($_GET['login_form']) && $_GET['login_form'] === 'true') {
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
    } elseif (isset($_GET['register_form']) && $_GET['register_form'] === 'true') {
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
        echo "<p>Welcome to PinkyFlow! Please <a href='?login_form=true'>log in</a> or <a href='?register_form=true'>register</a> to continue.</p>";
    }
}
?>
</body>
</html>
