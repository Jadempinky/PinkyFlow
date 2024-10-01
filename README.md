# PinkyFlow PHP Library

## Overview

PinkyFlow is a modular PHP library designed to manage user accounts, shopping carts, wishlists, favorites, product reviews, comments, and database interactions in an ecommerce or similar application. The library supports customizable modules and is extensible to fit various use cases.

---

## Features

### General
- **Modular System:** Enable or disable specific features such as user management, shopping, and comments.
- **Database Management:** Includes a PinkyFlowDB class to handle database connections, table creation, and querying.
- **User Authentication:** Register, login, logout, and manage user sessions, roles, and email updates.
  
### Shopping Module
- **Cart Management:** Add, remove, view, and clear items in the user's cart.
- **Product Management:** Add products to a catalog, update stock levels, and view product details.
- **Favorites:** Users can add items to a favorites list.
- **Wishlist:** Users can add items to a wishlist, separate from favorites.
- **Checkout System:** Check the cart and ensure stock availability before confirming purchases.

### User Module
- **Role Management:** Assign roles to users, which can control access to features like the cart.
- **User Sessions:** Maintain sessions for logged-in users, supporting logout and session clearing.

### Comment Module
- **Product Reviews and Comments:** Add comments or reviews to products, with functionality to edit, delete, or fetch comments.
  
---

## Usage Instructions

### Installation
1. Clone or download the PinkyFlow PHP library.
2. Initialize the database connection by setting up the `PinkyFlowDB` class in your application.
3. Include the necessary modules:
   - `PinkyFlowUser` for user management
   - `PinkyFlowProduct` for product catalog and shopping
   - `PinkyFlowCart` for cart functionalities
   - `PinkyFlowComment` for comment and review management

### Example: Enabling Modules
```php
// Enable specific modules
$enableDatabase = true;
$enableUserModule = true;
$enableShoppingModule = true;
$enableCommentModule = false;

// Initialize the DB connection
$db = new PinkyFlowDB('host', 'user', 'password', 'database_name');

// Enable user functionality
$user = new PinkyFlowUser($db);

// Enable product and shopping functionalities
$shop = new PinkyFlowShop($db, $user);
```

### Example: Adding a Product
```php
$shop->addProduct('Shirt', 'A comfortable cotton shirt.', 25.99, 100, 'shirt_image.jpg');
```

### Example: Adding to Cart
```php
$shop->addToCart('product_id_here', 1);
```

---

## Future Plans
- **Review and Rating System:** Allow users to leave ratings for products, visible in product details.
- **Category and Subcategory Management:** Add categories for better product organization.
- **JavaScript Autocomplete:** Add shop autocompletion features in the frontend.
- **Enhanced Role Management:** Refine role-based access control for specific cart functionalities.

---

## Known Issues & Things to Watch Out For
- **Database Connection Errors:** If the database setup is incorrect or unavailable, it will result in a failed connection. Ensure correct database credentials.
- **Session Management:** Make sure sessions are initialized properly. Otherwise, login and cart features might malfunction.
- **Product Stock Issues:** Ensure the product stock count is accurate, as the checkout process does not handle negative stock situations automatically yet.
- **Duplicate Entries:** Be cautious when adding favorites or wishlist items, as adding duplicates can raise exceptions.
  
If any issues arise or new features are needed, please refer to the library’s documentation or contact the developer.

