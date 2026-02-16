<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'grocery_store');

// Store location (for delivery distance calculation)
define('STORE_LATITUDE', '28.6139');
define('STORE_LONGITUDE', '77.2090');
define('MAX_DELIVERY_DISTANCE', 15); // 15km limit

// GST default rates
define('DEFAULT_GST', 5.00);

// Voice commands keywords
$voiceCommands = [
    'add' => 'add_to_cart',
    'go to cart' => 'view_cart',
    'cart' => 'view_cart',
    'vegetables' => 'view_category_1',
    'fruits' => 'view_category_2',
    'dairy' => 'view_category_3',
    'bakery' => 'view_category_4',
    'beverages' => 'view_category_5',
    'checkout' => 'go_to_checkout',
    'payment' => 'go_to_payment',
    'logout' => 'user_logout',
    'profile' => 'view_profile',
    'orders' => 'view_orders',
    'home' => 'go_home'
];

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>