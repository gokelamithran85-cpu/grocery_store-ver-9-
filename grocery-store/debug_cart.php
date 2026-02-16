<?php
require_once 'includes/db_connection.php';

echo "<h1>Cart Debug</h1>";

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "<p>User ID: $user_id</p>";
    
    // Check cart table structure
    echo "<h2>Cart Table Structure</h2>";
    $structure_query = "DESCRIBE cart";
    $structure_result = $conn->query($structure_query);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check cart contents
    echo "<h2>Cart Contents</h2>";
    $cart_query = "SELECT c.*, p.name, p.price 
                   FROM cart c 
                   LEFT JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = $user_id";
    $cart_result = $conn->query($cart_query);
    
    if ($cart_result && $cart_result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Product ID</th><th>Product Name</th><th>Quantity</th><th>Unit</th><th>Price</th></tr>";
        while($row = $cart_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['product_id'] . "</td>";
            echo "<td>" . ($row['name'] ?? 'Unknown') . "</td>";
            echo "<td>" . $row['quantity'] . "</td>";
            echo "<td>" . $row['unit'] . "</td>";
            echo "<td>" . ($row['price'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No items in cart!</p>";
    }
    
    // Test adding an item
    echo "<h2>Test Add to Cart</h2>";
    echo "<button onclick='testAddToCart()'>Test Add Carrots</button>";
    
} else {
    echo "<p style='color: red;'>Not logged in!</p>";
    echo "<a href='login.php'>Login</a>";
}
?>

<script>
function testAddToCart() {
    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: 1,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        alert(data.message);
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error);
    });
}
</script>

<a href="index.php">Back to Home</a>