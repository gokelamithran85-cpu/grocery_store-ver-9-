<?php
session_start();
header('Content-Type: application/json');

$productId = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (!isset($_SESSION['cart'][$productId])) {
    $_SESSION['cart'][$productId] = $quantity;
} else {
    $_SESSION['cart'][$productId] += $quantity;
}

// Calculate total items
$cartCount = 0;
foreach ($_SESSION['cart'] as $qty) {
    $cartCount++;
}

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $cartCount
]);
?>