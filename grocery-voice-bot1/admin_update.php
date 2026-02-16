<?php
session_start();
require_once 'db_connection.php';

// Check if admin is logged in
if(!isset($_SESSION['admin'])) {
    header('Location: admin.php');
    exit;
}

$action = $_POST['action'] ?? '';
$productId = $_POST['product_id'] ?? 0;

switch($action) {
    case 'add':
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        
        $stmt = $conn->prepare("INSERT INTO products (name, price_per_250g, current_stock) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $name, $price, $stock);
        $stmt->execute();
        break;
        
    case 'update_price':
        $newPrice = $_POST['new_price'] ?? 0;
        $stmt = $conn->prepare("UPDATE products SET price_per_250g = ? WHERE product_id = ?");
        $stmt->bind_param("di", $newPrice, $productId);
        $stmt->execute();
        break;
        
    case 'update_stock':
        $newStock = $_POST['new_stock'] ?? 0;
        $stmt = $conn->prepare("UPDATE products SET current_stock = current_stock + ? WHERE product_id = ?");
        $stmt->bind_param("di", $newStock, $productId);
        $stmt->execute();
        break;
        
    case 'delete':
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        break;
}

$conn->close();
header('Location: admin.php');
?>