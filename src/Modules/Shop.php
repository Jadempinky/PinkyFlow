<?php
namespace PinkyFlow\Modules;


use Exception;
class Shop {
    private $db;
    public $user;
    private $product;
    private $cart;
    private $favorite;
    private $wishlist;
    private $category; // Added for categories

    public function __construct($db, $user) {
        $this->db = $db;
        $this->user = $user;
        $this->product = new Product($db);
        $this->cart = new Cart($db, $user);
        $this->favorite = new Favorite($db, $user);
        $this->wishlist = new Wishlist($db, $user);
        $this->category = new Category($db); // Initialize categories
    }

    // Category Management
    public function createCategory($name, $description = '', $image = '', $parent_id = null) {
        return $this->category->createCategory($name, $description, $image, $parent_id);
    }

    public function getCategoryById($id) {
        return $this->category->getCategoryById($id);
    }

    public function getAllCategories() {
        return $this->category->getAllCategories();
    }

    // Update product creation to include category_id
    public function addProduct($name, $description, $price, $stock, $image, $category_id = null) {
        return $this->product->createProduct($name, $description, $price, $stock, $image, $category_id);
    }

    // Add method to get products by category
    public function getProductsByCategory($category_id) {
        return $this->product->getProductsByCategory($category_id);
    }


    public function listProducts() {
        return $this->product->getAllProducts();
    }

    public function getAllProducts() {
        return $this->product->getAllProducts();
    }

    public function viewProduct($product_id) {
        return $this->product->getProduct($product_id);
    }

    public function updateProductStock($product_id, $quantity) {
        $this->product->updateStock($product_id, $quantity);
    }

    public function addToCart($product_id, $quantity) {
        $this->cart->addToCart($product_id, $quantity);
    }

    public function removeFromCart($product_id) {
        $this->cart->removeFromCart($product_id);
    }

    public function viewCart() {
        return $this->cart->getCartItems();
    }

    public function checkout() {
        $cartItems = $this->cart->getCartItems();
        foreach ($cartItems as $item) {
            $new_stock = $item['stock'] - $item['quantity'];

            if ($new_stock < 0) {
                throw new Exception("Insufficient stock for product: " . $item['name']);
            }

            $this->product->updateStock($item['product_id'], $new_stock);
        }
        $this->cart->clearCart();
    }

    // Favorites Management
    public function addToFavorite($product_id) {
        $this->favorite->addToFavorite($product_id);
    }

    public function removeFromFavorite($product_id) {
        $this->favorite->removeFromFavorite($product_id);
    }

    public function getFavoriteItems() {
        return $this->favorite->getFavorites();
    }

    public function clearFavorites() {
        $this->favorite->clearFavorites();
    }

    // Wishlist Management
    public function addToWishlist($product_id) {
        $this->wishlist->addToWishlist($product_id);
    }

    public function removeFromWishlist($product_id) {
        $this->wishlist->removeFromWishlist($product_id);
    }

    public function getWishlistItems() {
        return $this->wishlist->getWishlist();
    }

    public function clearWishlist() {
        $this->wishlist->clearWishlist();
    }

    // Add getter methods if needed
    public function getProductClass() {
        return $this->product;
    }

    public function getCartClass() {
        return $this->cart;
    }

    public function getFavoriteClass() { // Added for favorites
        return $this->favorite;
    }

    public function getWishlistClass() { // Added for wishlist
        return $this->wishlist;
    }

    
    // Make all category commands public
    public function getCategoryByName($name) {
        return $this->category->getCategoryByName($name);
    }

    public function getSubcategories($parent_id) {
        return $this->category->getSubcategories($parent_id);
    }

    public function updateCategory($id, $name, $description = '', $image = '', $parent_id = null) {
        return $this->category->updateCategory($id, $name, $description, $image, $parent_id);
    }

    public function deleteCategory($id) {
        return $this->category->deleteCategory($id);
    }

    public function addCategoryImage($id, $image) {
        return $this->category->addCategoryImage($id, $image);
    }

    public function setCategoryDescription($id, $description) {
        return $this->category->setCategoryDescription($id, $description);
    }
}
?>
