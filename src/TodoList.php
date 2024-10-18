<?php

/*
Note to self, to add
////////////////////////////////////////////////////////////////////////////////////////////////////
DONE ----> Add a favourite option
DONE ----> Make Wish list != Favourite
DONE ----> Make a wish list for items
DONE ----> Add a role optiion to the user
DOING ---> Add reviews, comments, and rating
DOING ---> Add a cart and change certain functions to check for role

Check the Category Module
Automaticly sort imported image for categories, shop items, etc neatly inside folder of their own category name
Admin class with easy management, access to everything, TVA, Taxes, Profit, users, etc
($admin->usertable)
Add global taxe variable that affects everything for the shop

Censor for comments and reviews (Check for links and same messages over and over again)
Flash sales with time countdown
Add easy discount function
Get the best rated items with a function getting $num of them
When doing the js file, link it to the DB for shop autocompletion
Edit the shop db to properly incorporate users and other needed things
Add categories, and subcategories
And, add an option for certain subcategories to show different options
For example (Clothes > Shirt > Large, Medium, Small)
Add a function to add / Get and edit categories, subcategories, etc

////////////////////////////////////////////////////////////////////////////////////////////////////



DO NOT FORGET TO FINISH COMMENT SECTION

ADD CATEGORY TO SHOP ITEMS




////////////////////////////////////////////////////////////////////////////////////////////////////
*/

// You can add other configuration variables as needed

// Configuration Class
// Include config.php to get configuration variables



// // Update conditional checks accordingly
// if (Config::$enableUserModule) {
//     Config::$enableDatabase = true; // Ensure database is enabled if user functionality is required
// }
// if (Config::$enableShoppingModule) {
//     Config::$enableDatabase = true;
//     Config::$enableUserModule = true;
// }
// if (Config::$enableCommentModule) {
//     Config::$enableDatabase = true;
//     Config::$enableUserModule = true;
// }

// if (Config::$enableDatabase) {
//     if (!class_exists('PinkyFlowDB')) {

//     }
// }

// if (Config::$enableCommentModule) {
//     if (!class_exists('PinkyFlowComment')) {

//     }
// }

// if (Config::$enableUserModule) {
//     if (!class_exists('PinkyFlowUser')) {

//     }
// }

// if (Config::$enableShoppingModule) {
//     if (!class_exists('Product')) {

//     }

//     // Cart Class
//     if (!class_exists('Cart')) {

//     }

//     // PinkyFlowShop Class
//     if (!class_exists('PinkyFlowShop')) {


//         if (!class_exists('Category')) {

        
//     }

//     // Favorite Class
//     if (!class_exists('Favorite')) {

//     }

//     // Wishlist Class
//     if (!class_exists('Wishlist')) {

//     }

//     // PinkyFlowShopFavorite Class (Removed as separate class since favorites are now managed by Favorite)
//     // Similarly, PinkyFlowShopWishlist Class is not needed as Wishlist handles wishlist functionality.
// }


/*


Make something like this at the end 

$header('home','shop','user',$burger);
if (!$userid) {
  $loginform;
} else {
  $log-outbtn;
}
...

Pre create div, sections etc, for easy pre use, like $registerform

Would build a registerform, where the coder placed it


*/