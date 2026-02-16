<?php
require_once 'includes/db_connection.php';

echo "<h1>🔍 Cart Access Diagnostic</h1>";

// Check session
echo "<h2>Session Status:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User is logged in (ID: " . $_SESSION['user_id'] . ")</p>";
    
    // Check cart table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'cart'");
    if ($table_check->num_rows > 0) {
        echo "<p style='color: green;'>✅ Cart table exists</p>";
        
        // Check cart contents
        $user_id = $_SESSION['user_id'];
        $cart_query = "SELECT c.*, p.name, p.price FROM cart c 
                       LEFT JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = $user_id";
        $cart_result = $conn->query($cart_query);
        
        if ($cart_result && $cart_result->num_rows > 0) {
            echo "<p style='color: green;'>✅ Cart has " . $cart_result->num_rows . " items</p>";
            echo "<table border='1' cellpadding='10'>";
            echo "<tr><th>ID</th><th>Product</th><th>Quantity</th><th>Price</th></tr>";
            while($row = $cart_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . ($row['name'] ?? 'Unknown') . "</td>";
                echo "<td>" . $row['quantity'] . " " . $row['unit'] . "</td>";
                echo "<td>₹" . ($row['price'] ?? '0') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'>⚠️ Cart is empty</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Cart table does not exist!</p>";
        // Create cart table
        $create_cart = "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
            unit VARCHAR(20) NOT NULL DEFAULT 'kg',
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_product (user_id, product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($create_cart)) {
            echo "<p style='color: green;'>✅ Cart table created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create cart table: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ User is NOT logged in!</p>";
    echo "<p><a href='login.php' style='display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
}

// Check cart.php file exists and is readable
echo "<h2>Cart Page Check:</h2>";
$cart_file = __DIR__ . '/cart.php';
if (file_exists($cart_file)) {
    echo "<p style='color: green;'>✅ cart.php exists</p>";
    if (is_readable($cart_file)) {
        echo "<p style='color: green;'>✅ cart.php is readable</p>";
    } else {
        echo "<p style='color: red;'>❌ cart.php is not readable</p>";
    }
} else {
    echo "<p style='color: red;'>❌ cart.php does not exist!</p>";
}

// Provide fix options
echo "<h2>Quick Fix Options:</h2>";
echo "<ul>";
echo "<li><a href='fix_cart_table.php' style='color: #4CAF50;'>🛒 Fix Cart Table</a></li>";
echo "<li><a href='test_cart.php' style='color: #2196F3;'>🧪 Add Test Items to Cart</a></li>";
echo "<li><a href='clear_sessions.php' style='color: #FF9800;'>🔄 Clear Sessions</a></li>";
echo "<li><a href='cart.php' style='color: #9C27B0;'>📦 Go to Cart Page</a></li>";
echo "</ul>";
?>