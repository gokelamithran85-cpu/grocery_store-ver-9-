<?php
session_start();
// Simple admin authentication
$admin_pass = 'admin123'; // Change this in production

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if($_POST['password'] == $admin_pass) {
        $_SESSION['admin'] = true;
    }
}

if(!isset($_SESSION['admin'])): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #4CAF50; color: white; border: none; padding: 10px; width: 100%; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter admin password" required>
            <button type="submit">Login</button>
            <p style="color: #666; font-size: 12px; margin-top: 10px;">Default password: admin123</p>
        </form>
    </div>
</body>
</html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f5f5f5; }
        
        .header { background: #4CAF50; color: white; padding: 20px; display: flex; justify-content: space-between; }
        
        .container { padding: 20px; max-width: 1200px; margin: 0 auto; }
        
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        
        .btn { background: #4CAF50; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-danger { background: #f44336; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <a href="?logout=1" style="color: white;">Logout</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>Product Management</h2>
            
            <form method="POST" action="admin_update.php" style="margin: 20px 0;">
                <h3>Add New Product</h3>
                <input type="text" name="name" placeholder="Product Name" required style="width: 200px; padding: 8px; margin: 5px;">
                <input type="number" step="0.01" name="price" placeholder="Price per 250g" required style="width: 200px; padding: 8px; margin: 5px;">
                <input type="number" step="0.01" name="stock" placeholder="Stock (kg)" required style="width: 200px; padding: 8px; margin: 5px;">
                <button type="submit" name="action" value="add" class="btn">Add Product</button>
            </form>
            
            <h3>Current Products</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price/250g</th>
                    <th>Stock (kg)</th>
                    <th>Actions</th>
                </tr>
                <?php
                require_once 'db_connection.php';
                $sql = "SELECT * FROM products";
                $result = $conn->query($sql);
                
                while($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo $row['product_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td>₹<?php echo $row['price_per_250g']; ?></td>
                    <td><?php echo $row['current_stock']; ?> kg</td>
                    <td>
                        <form method="POST" action="admin_update.php" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <input type="number" step="0.01" name="new_price" placeholder="New Price" style="width: 100px; padding: 5px;">
                            <button type="submit" name="action" value="update_price" class="btn">Update Price</button>
                        </form>
                        
                        <form method="POST" action="admin_update.php" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <input type="number" step="0.01" name="new_stock" placeholder="Add Stock" style="width: 100px; padding: 5px;">
                            <button type="submit" name="action" value="update_stock" class="btn">Update Stock</button>
                        </form>
                        
                        <form method="POST" action="admin_update.php" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <button type="submit" name="action" value="delete" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        
        <div class="card">
            <h2>System Statistics</h2>
            <?php
            $totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
            $totalOrders = 0; // You can add orders functionality later
            ?>
            <div style="display: flex; gap: 20px; margin-top: 20px;">
                <div style="background: #e8f5e8; padding: 20px; border-radius: 10px; flex: 1;">
                    <h3>Total Products</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?php echo $totalProducts; ?></p>
                </div>
                <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; flex: 1;">
                    <h3>Total Orders</h3>
                    <p style="font-size: 24px; font-weight: bold;"><?php echo $totalOrders; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Logout
    if(isset($_GET['logout'])) {
        session_destroy();
        header('Location: admin.php');
        exit;
    }
    $conn->close();
    ?>
</body>
</html>