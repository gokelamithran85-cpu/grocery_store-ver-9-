<?php
require_once 'includes/db_connection.php';

echo "<h1>Fixing Duplicate Products</h1>";

// Find and delete duplicate products
$find_duplicates = "SELECT name, COUNT(*) as count 
                    FROM products 
                    GROUP BY name 
                    HAVING count > 1";
$duplicates = $conn->query($find_duplicates);

if ($duplicates->num_rows > 0) {
    echo "<h2>Found duplicate products:</h2>";
    echo "<ul>";
    
    while($row = $duplicates->fetch_assoc()) {
        echo "<li>" . $row['name'] . " - " . $row['count'] . " copies</li>";
        
        // Keep the first one, delete others
        $delete_query = "DELETE FROM products 
                        WHERE name = '" . $row['name'] . "' 
                        AND id NOT IN (
                            SELECT id FROM (
                                SELECT MIN(id) as id 
                                FROM products 
                                WHERE name = '" . $row['name'] . "'
                            ) as temp
                        )";
        $conn->query($delete_query);
        echo "<li style='color: green;'>✓ Fixed: " . $row['name'] . "</li>";
    }
    
    echo "</ul>";
    echo "<p style='color: green; font-weight: bold;'>✅ Duplicates removed successfully!</p>";
} else {
    echo "<p style='color: green;'>✅ No duplicate products found!</p>";
}

echo "<p><a href='vegetables.php' style='display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>View Vegetables Section</a></p>";
echo "<p><a href='admin_login.php' style='display: inline-block; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
?>
