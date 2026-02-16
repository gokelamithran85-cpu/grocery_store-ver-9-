<?php
session_start();
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$product_name = mysqli_real_escape_string($conn, $data['name']);
$quantity = (float)$data['quantity'];
$unit = mysqli_real_escape_string($conn, $data['unit']);

// Search for product
$search_query = "SELECT * FROM products WHERE 
                 LOWER(name) LIKE LOWER('%$product_name%') 
                 AND stock_quantity > 0 
                 LIMIT 1";
$result = $conn->query($search_query);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please login to add items to cart',
            'redirect' => 'login.php'
        ]);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id = $product['id'];
    
    // Check quantity limit
    if ($quantity > $product['max_quantity']) {
        echo json_encode([
            'success' => false,
            'message' => 'Maximum quantity is ' . $product['max_quantity'] . $product['unit']
        ]);
        exit();
    }
    
    // Check stock
    if ($quantity > $product['stock_quantity']) {
        echo json_encode([
            'success' => false,
            'message' => 'Only ' . $product['stock_quantity'] . ' ' . $product['unit'] . ' available'
        ]);
        exit();
    }
    
    // Check if already in cart
    $check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        // Update quantity
        $cart_item = $check_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        if ($new_quantity > $product['max_quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Maximum quantity limit reached'
            ]);
            exit();
        }
        
        $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = {$cart_item['id']}";
        $conn->query($update_query);
    } else {
        // Add new item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity, unit) 
                        VALUES ($user_id, $product_id, $quantity, '{$product['unit']}')";
        $conn->query($insert_query);
    }
    
    // Get updated cart count
    $cart_count = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id")->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => $cart_count,
        'product' => [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'unit' => $product['unit']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found. Please try a different name.'
    ]);
}
?>