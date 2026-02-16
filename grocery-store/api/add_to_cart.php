<?php
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart',
        'redirect' => '../login.php'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Try POST data if JSON decode fails
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 1;
} else {
    $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    $quantity = isset($data['quantity']) ? (float)$data['quantity'] : 1;
}

// Validate inputs
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product'
    ]);
    exit();
}

if ($quantity <= 0) {
    $quantity = 0.1; // Minimum quantity
}

// Get product details
$product_query = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_query);

if (!$product_result || $product_result->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    exit();
}

$product = $product_result->fetch_assoc();

// Check stock
if ($product['stock_quantity'] < $quantity) {
    echo json_encode([
        'success' => false,
        'message' => 'Only ' . $product['stock_quantity'] . ' ' . $product['unit'] . ' available'
    ]);
    exit();
}

// Check max quantity
if ($quantity > $product['max_quantity']) {
    echo json_encode([
        'success' => false,
        'message' => 'Maximum quantity is ' . $product['max_quantity'] . ' ' . $product['unit']
    ]);
    exit();
}

// Check if product already in cart
$check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
$check_result = $conn->query($check_query);

if ($check_result && $check_result->num_rows > 0) {
    // Update existing cart item
    $cart_item = $check_result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + $quantity;
    
    // Check max quantity again
    if ($new_quantity > $product['max_quantity']) {
        echo json_encode([
            'success' => false,
            'message' => 'Maximum quantity limit reached'
        ]);
        exit();
    }
    
    $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = {$cart_item['id']}";
    
    if ($conn->query($update_query)) {
        // Get updated cart count
        $count_query = "SELECT COUNT(*) as count, SUM(quantity) as total_items FROM cart WHERE user_id = $user_id";
        $count_result = $conn->query($count_query);
        $count_data = $count_result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart_count' => $count_data['count'],
            'total_items' => $count_data['total_items'],
            'product' => [
                'id' => $product['id'],
                'name' => $product['name'],
                'quantity' => $new_quantity,
                'unit' => $product['unit']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update cart'
        ]);
    }
} else {
    // Insert new cart item
    $unit = mysqli_real_escape_string($conn, $product['unit']);
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity, unit) 
                     VALUES ($user_id, $product_id, $quantity, '$unit')";
    
    if ($conn->query($insert_query)) {
        // Get updated cart count
        $count_query = "SELECT COUNT(*) as count, SUM(quantity) as total_items FROM cart WHERE user_id = $user_id";
        $count_result = $conn->query($count_query);
        $count_data = $count_result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => $count_data['count'],
            'total_items' => $count_data['total_items'],
            'product' => [
                'id' => $product['id'],
                'name' => $product['name'],
                'quantity' => $quantity,
                'unit' => $product['unit']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add product to cart'
        ]);
    }
}
?>