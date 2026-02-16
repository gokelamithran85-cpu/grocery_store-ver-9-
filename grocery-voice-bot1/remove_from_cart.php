<?php
session_start();
header('Content-Type: application/json');

$productId = $_POST['product_id'] ?? 0;

if(isset($_SESSION['cart'][$productId])) {
    unset($_SESSION['cart'][$productId]);
    echo json_encode(['success' => true, 'message' => 'Item removed']);
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
}
?>