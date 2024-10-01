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
        input[type="text"], input[type="password"], input[type="email"], input[type="number"] {
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

    // Handle Logout Action
    if (isset($_GET['logout'])) {
        if (isset($user)) {
            $user->logout();
        }
        header("Location: index.php");
        exit;
    }

    // Handle Login Action
    if (isset($_GET['login']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($user)) {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($user->login($username, $password)) {
                header("Location: index.php");
                exit;
            } else {
                echo "<p class='error'>Incorrect username or password.</p>";
            }
        }
    }

    // Handle Registration Action
    if (isset($_GET['register']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($user)) {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $verifyPassword = trim($_POST['verifyPassword'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $result = $user->register($username, $password, $verifyPassword, $email);
            if ($result === true) {
                echo "<p class='success'>Registration successful. You can now <a href='?login_form'>log in</a>.</p>";
            } else {
                echo "<p class='error'>" . htmlspecialchars($result) . "</p>";
            }
        }
    }

    // Handle Add to Cart Action
    if (isset($_GET['add_to_cart']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
        $product_id = $_GET['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

        try {
            $shop->addToCart($product_id, $quantity);
            echo "<p class='success'>Product added to cart.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Handle Remove from Cart Action
    if (isset($_GET['remove_from_cart']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
        $product_id = $_GET['product_id'];

        try {
            $shop->removeFromCart($product_id);
            echo "<p class='success'>Product removed from cart.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Handle Checkout Action
    if (isset($_GET['checkout']) && isset($shop) && $shop->user->isLoggedIn()) {
        try {
            $shop->checkout();
            echo "<p class='success'>Checkout successful. Thank you for your purchase!</p>";
        } catch (Exception $e) {
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Handle Add to Favorite Action
    if (isset($_GET['add_to_favorite']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
        $product_id = $_GET['product_id'];

        try {
            $shop->addToFavorite($product_id);
            echo "<p class='success'>Product added to favorites.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Handle Remove from Favorite Action
    if (isset($_GET['remove_from_favorite']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
        $product_id = $_GET['product_id'];

        try {
            $shop->removeFromFavorite($product_id);
            echo "<p class='success'>Product removed from favorites.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Handle View Favorites Action
    if (isset($_GET['view_favorites']) && isset($shop) && $shop->user->isLoggedIn()) {
        echo "<h2>Your Favorites</h2>";
        $favorites = $shop->getFavoriteItems();
        if (empty($favorites)) {
            echo "<p>You have no favorite items.</p>";
        } else {
            foreach ($favorites as $fav) {
                echo "<div class='favorite-item'>";
                echo "<h3>" . htmlspecialchars($fav['name']) . "</h3>";
                echo "<p>" . htmlspecialchars($fav['description']) . "</p>";
                echo "<p>Price: $" . number_format($fav['price'], 2) . "</p>";
                echo "<p>Added on: " . htmlspecialchars($fav['added_at']) . "</p>";
                if ($fav['image']) {
                    echo "<img src='" . htmlspecialchars($fav['image']) . "' alt='" . htmlspecialchars($fav['name']) . "' style='max-width:150px;'><br>";
                }
                echo "<div class='actions'>";
                echo "<form action='?add_to_cart&product_id=" . urlencode($fav['product_id']) . "' method='post' style='display:inline-block;'>
                        <input type='hidden' name='quantity' value='1'>
                        <input type='submit' value='Add to Cart'>
                      </form>";
                echo "<a href='?remove_from_favorite=true&product_id=" . urlencode($fav['product_id']) . "' onclick=\"return confirm('Remove from favorites?');\">Remove from Favorites</a>";
                echo "</div>";
                echo "</div>";
            }
        }
        echo "<br><a href='index.php'>Back to Home</a>";
        exit;
    }

    // Handle Add to Wishlist Action
    if (isset($_GET['add_to_wishlist']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
        $product_id = $_GET['product_id'];

        try {
            $shop->addToWishlist($product_id);
            echo "<p class='success'>Product added to wishlist.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Handle Remove from Wishlist Action
    if (isset($_GET['remove_from_wishlist']) && isset($_GET['product_id']) && isset($shop) && $shop->user->isLoggedIn()) {
        $product_id = $_GET['product_id'];

        try {
            $shop->removeFromWishlist($product_id);
            echo "<p class='success'>Product removed from wishlist.</p>";
        } catch (Exception $e) {
            echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Handle View Wishlist Action
    if (isset($_GET['view_wishlist']) && isset($shop) && $shop->user->isLoggedIn()) {
        echo "<h2>Your Wishlist</h2>";
        $wishlist = $shop->getWishlistItems();
        if (empty($wishlist)) {
            echo "<p>Your wishlist is empty.</p>";
        } else {
            foreach ($wishlist as $wish) {
                echo "<div class='wishlist-item'>";
                echo "<h3>" . htmlspecialchars($wish['name']) . "</h3>";
                echo "<p>" . htmlspecialchars($wish['description']) . "</p>";
                echo "<p>Price: $" . number_format($wish['price'], 2) . "</p>";
                echo "<p>Added on: " . htmlspecialchars($wish['added_at']) . "</p>";
                if ($wish['image']) {
                    echo "<img src='" . htmlspecialchars($wish['image']) . "' alt='" . htmlspecialchars($wish['name']) . "' style='max-width:150px;'><br>";
                }
                echo "<div class='actions'>";
                echo "<form action='?add_to_cart&product_id=" . urlencode($wish['product_id']) . "' method='post' style='display:inline-block;'>
                        <input type='hidden' name='quantity' value='1'>
                        <input type='submit' value='Add to Cart'>
                      </form>";
                echo "<a href='?remove_from_wishlist=true&product_id=" . urlencode($wish['product_id']) . "' onclick=\"return confirm('Remove from wishlist?');\">Remove from Wishlist</a>";
                echo "</div>";
                echo "</div>";
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
                        } else {
                            echo "<p><em>Out of stock</em></p>";
                        }
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
                // Default action could be to list products
                echo "<h2>Welcome to PinkyFlow Shop</h2>";
                echo "<p>Explore our products by clicking <a href='?action=list_products'>here</a>.</p>";
                break;
        }
    } else {
        // Handle login and registration forms
        if (isset($_GET['login_form'])) {
            // Display Login Form
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
            // Display Registration Form
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
            // Display Home or other content if necessary
            echo "<p>Welcome to PinkyFlow! Please <a href='?login_form'>log in</a> or <a href='?register_form'>register</a> to continue.</p>";
        }
    }
    ?>
</body>
</html>
