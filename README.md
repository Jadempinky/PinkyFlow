
# PinkyFlow

PinkyFlow is a modular PHP framework designed to provide a flexible and easy-to-use base for creating web applications. It offers a variety of modules, including user authentication, shopping cart management, comment systems, and more. The framework is designed to be easily configurable, even for users with minimal coding experience.

## Features

- **Modular Design**: Enable or disable modules based on your needs via a simple configuration file.
- **User Authentication**: Register, login, and manage user sessions.
- **Shopping Module**: Manage products, shopping carts, wishlists, and favorites.
- **Comment System**: Add comments and reviews for products.
- **Easy Configuration**: A simple `config.php` file to adjust settings without touching the core code.
- **Automatic Setup**: Automatically generates configuration files and necessary database tables.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Framework Initialization](#framework-initialization)
  - [User Registration](#user-registration)
  - [Product Management](#product-management)
  - [Comments and Reviews](#comments-and-reviews)
- [Contributing](#contributing)
- [License](#license)

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or a compatible database)
- Web server (Apache, Nginx, etc.)
- Composer (recommended for dependency management and autoloading)

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/Jadempinky/PinkyFlow.git
   ```

2. **Navigate to the project directory**

   ```bash
   cd PinkyFlow
   ```

3. **Install dependencies (optional but recommended)**

   ```bash
   composer install
   ```

## Configuration

Update the configuration settings in the `config.php` file, located in the root directory of the project. This file allows you to adjust the database settings and enable or disable modules.

## Usage

### Framework Initialization

To initialize the PinkyFlow framework, simply require the `PinkyFlow.php` file. You no longer need to initialize individual objects, as the framework handles this automatically.

```php
<?php
require_once __DIR__ . '/PinkyFlow.php';
?>
```

Once required, the necessary objects will be automatically loaded, and you can access them through the framework.

Example usage:
```php
<?php
require_once __DIR__ . '/PinkyFlow.php';

// Now you can use $user, $shop, $comment, etc.
?>
```

### User Registration

To register a new user:

```php
<?php
if ($user) {
    try {
        $username = 'newuser';
        $password = 'securepassword123';
        $user->register($username, $password);
        echo 'User registered successfully!';
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>
```

### Product Management

Add a new product:

```php
<?php
if ($shop) {
    $productData = [
        'name' => 'Example Product',
        'description' => 'This is an example product.',
        'price' => 19.99,
    ];
    $shop->addProduct($productData);
    echo 'Product added successfully!';
}
?>
```

### Comments and Reviews

Add a comment and a rating to a product:

```php
<?php
if ($comment && $user && $user->isLoggedIn()) {
    $productId = 'product123';
    $commentText = 'Great product!';
    $rating = 5;
    try {
        $comment->addComment($productId, $commentText, null, $rating);
        echo 'Comment added successfully!';
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>
```

## Contributing

Contributions are welcome! Follow these steps:

1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Make your changes and commit with clear messages.
4. Submit a pull request to the `main` branch.

Ensure your code adheres to the project's coding standards and includes appropriate tests.

## License

This project is licensed under the [MIT License](LICENSE).

---
