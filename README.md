
# PinkyFlow

PinkyFlow is a modular PHP framework designed to provide a flexible and easy-to-use base for creating web applications. It offers a variety of modules, including user authentication, shopping cart management, comment systems, and more.

## Installation

You can install PinkyFlow using Composer. Run the following command in your project directory:

```bash
composer require jadempinky/pinkyflow
```

## Usage

After installing PinkyFlow via Composer, you can use it in your project as follows:

1. Include the Composer autoloader in your PHP file:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
```

2. Initialize the PinkyFlow framework:

```php
use PinkyFlow\PinkyFlow;

$pinkyFlow = new PinkyFlow();
```

3. Now you can use the various modules and features of PinkyFlow:

```php
// Example: User registration
$user = $pinkyFlow->getUser();
try {
    $username = 'newuser';
    $password = 'securepassword123';
    $user->register($username, $password);
    echo 'User registered successfully!';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

// Example: Adding a product
$shop = $pinkyFlow->getShop();
$productData = [
    'name' => 'Example Product',
    'description' => 'This is an example product.',
    'price' => 19.99,
];
$shop->addProduct($productData);
echo 'Product added successfully!';

// Example: Adding a comment
$comment = $pinkyFlow->getComment();
if ($user->isLoggedIn()) {
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
```

## Configuration

PinkyFlow can be configured by creating a `config.php` file in your project root. Refer to the PinkyFlow documentation for available configuration options.

## Documentation

For more detailed information on how to use PinkyFlow and its various modules, please refer to the [official documentation](https://github.com/Jadempinky/PinkyFlow/wiki).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.