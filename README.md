
# PinkyFlow Framework

**PinkyFlow** is a simple and modular PHP framework designed for tasks like user management, shopping carts, comments, and more. It handles autoloading, database interactions, and module initialization, allowing you to focus on building your application.

## Getting Started

### 1. Install via Composer

To install **PinkyFlow** in your project, use Composer:

```bash
composer require jadempinky/pinkyflow
```

### 2. Configuring the Framework

**PinkyFlow** uses a `config/config.php` file for configuration. Edit the values to your needs.

```php
<?php

namespace PinkyFlow;

class Config {
    public static $enableDatabase = true;
    public static $enableUserModule = true;
    public static $enableShoppingModule = true;
    public static $enableCommentModule = true;

    public static $dbHost = 'localhost';  // Database host
    public static $dbUser = 'root';       // Database username
    public static $dbPass = '';           // Database password
    public static $dbName = 'pinkyflow';  // Database name
}
```

### 3. Example: User Registration and Login

The following code demonstrates how to use **PinkyFlow** for user registration, login, and retrieving user details:

```php
<?php
require_once __DIR__ . '/vendor/jadempinky/pinkyflow/PinkyFlow.php';


// Register a new user
if (isset($_POST['register'])) {
    $user->register($_POST['username'], $_POST['password'], $_POST['password'], $_POST['email']);
}

// Check if the user is logged in; if not, log them in
if (!$user->isLoggedIn()) {
    if (isset($_POST['login'])) {
        $user->login($_POST['username'], $_POST['password']);
    }
}

// Once the user is logged in, retrieve and display user details
if ($user->isLoggedIn()) {
    echo "User is logged in\n";
    
    $email = $user->getEmail();
    $uid = $user->getUid();
    $username = $user->getUsernameFromUid($uid);

    echo "Username: " . $username . "\n";
    echo "Email: " . $email . "\n";
    echo "Uid: " . $uid . "\n";
}

if ($user->isLoggedIn()) {
    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post"><button type="submit" name="logout">Logout</button></form>';
}

if (isset($_POST['logout'])) {
    $user->logout();
}
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <input type="text" name="username" placeholder="Username"><br>
    <input type="password" name="password" placeholder="Password"><br>
    <input type="email" name="email" placeholder="Email"><br>
    <button type="submit" name="register">Register</button>
</form>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <input type="text" name="username" placeholder="Username"><br>
    <input type="password" name="password" placeholder="Password"><br>
    <button type="submit" name="login">Login</button>
</form>

<?php
// Register a new user
if (isset($_POST['register'])) {
    $user->register($_POST['username'], $_POST['password'], $_POST['password'], $_POST['email']);
}

// Check if the user is logged in; if not, log them in
if (!$user->isLoggedIn()) {
    if (isset($_POST['login'])) {
        $user->login($_POST['username'], $_POST['password']);
    }
}

?>
```


### Running Your Code

To see your code in action, you can use local development environments like Laragon, XAMPP, or WAMP. These applications provide an easy way to set up a local web server and database on your machine.

#### Using Laragon

1. Download and install [Laragon](https://laragon.org/download/).
2. Place your project files in the Laragon's `www` directory (usually `C:\laragon\www\`).
3. Start Laragon and click the "Start All" button.
4. Open your web browser and navigate to `http://localhost/your-project-folder`.

#### Using XAMPP or WAMP

1. Download and install [XAMPP](https://www.apachefriends.org/index.html) or [WAMP](https://www.wampserver.com/en/).
2. Place your project files in the `htdocs` (XAMPP) or `www` (WAMP) directory.
3. Start Apache and MySQL services from the control panel.
4. Open your web browser and navigate to `http://localhost/your-project-folder`.

Remember to configure your database settings in the `Config` class to match your local environment.

By using these tools, you can easily test and debug your PinkyFlow application on your local machine before deploying it to a live server.


### Key Features

- **Automatic Database Handling:** PinkyFlow creates the database and necessary tables automatically.
- **Autoloading:** Classes and modules are loaded automatically. No need for manual `use` statements.
- **User Management:** Easily handle user registration, login, and details retrieval with minimal code.
- **Shopping Cart:** Implement shopping cart functionality with ease.
- **Comment System:** Add and manage comments in your application.
- **New Modules coming soon:**

## License

**PinkyFlow** is open-source and licensed under the MIT License.
