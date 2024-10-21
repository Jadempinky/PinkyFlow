<?php
namespace PinkyFlow\Modules;

class Admin {
    private $db;
    public $user;
    private $product;
    private $cart;
    private $favorite;
    private $wishlist;
    private $category;  // For category management

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
        $this->product = new Product($db);
        $this->cart = new Cart($db, $user);
        $this->favorite = new Favorite($db, $user);
        $this->wishlist = new Wishlist($db, $user);
        $this->category = new Category($db); // Initialize categories
    }

    // -------- Product Management --------

    // Add a new product
    public function addProduct($name, $description, $price, $category_id) {
        return $this->product->createProduct($name, $description, $price, $category_id);
    }

    // Edit an existing product
    public function editProduct($id, $name, $description, $price, $category_id) {
        return $this->product->updateProduct($id, $name, $description, $price, $category_id);
    }

    // Delete a product
    public function deleteProduct($id) {
        return $this->product->deleteProduct($id);
    }

    // -------- Category Management --------

    // Add a new category
    public function addCategory($name, $description, $parent_id = null) {
        return $this->category->createCategory($name, $description, '', $parent_id);
    }

    // Edit an existing category
    public function editCategory($id, $name, $description, $parent_id = null) {
        return $this->category->updateCategory($id, $name, $description, '', $parent_id);
    }

    // Delete a category
    public function deleteCategory($id) {
        return $this->category->deleteCategory($id);
    }

    // -------- User Management --------

    // Add a new user (admin creates a user)
    public function addUser($username, $password, $email) {
        return $this->user->register($username, $password, $password, $email);
    }

    // Edit a user (e.g., update email or password)
    public function editUser($uid, $username = null, $password = null, $email = null) {
        $userDetails = [];
        if ($username) $userDetails['username'] = $username;
        if ($password) $userDetails['password'] = $password;
        if ($email) $userDetails['email'] = $email;
        return $this->user->updateUser($uid, $userDetails);
    }

    // Delete a user
    public function deleteUser($uid) {
        return $this->user->deleteUser($uid);
    }
}
