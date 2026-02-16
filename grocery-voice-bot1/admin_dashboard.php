<?php
session_start();
require_once 'db_connection.php';

// Admin authentication
$admin_password = 'admin123'; // Change this in production

if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['password'] == $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login</title>
            <style>
                body {
                    font-family: Arial;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                
                .login-box {
                    background: white;
                    padding: 40px;
                    border-radius: 15px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
                    width: 350px;
                    text-align: center;
                    animation: slideDown 0.6s ease-out;
                }
                
                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translateY(-50px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .login-box h2 {
                    color: #333;
                    margin-bottom: 30px;
                }
                
                .login-box input {
                    width: 100%;
                    padding: 12px;
                    margin: 10px 0;
                    border: 2px solid #ddd;
                    border-radius: 8px;
                    font-size: 16px;
                    transition: all 0.3s;
                }
                
                .login-box input:focus {
                    border-color: #667eea;
                    outline: none;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }
                
                .login-box button {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border: none;
                    padding: 12px;
                    width: 100%;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: bold;
                    cursor: pointer;
                    margin-top: 20px;
                    transition: all 0.3s;
                }
                
                .login-box button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>🔐 Admin Login</h2>
                <form method="POST">
                    <input type="password" name="password" placeholder="Enter Admin Password" required>
                    <button type="submit">Login</button>
                    <p style="margin-top: 15px; color: #666; font-size: 14px;">
                        Default password: admin123
                    </p>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_product':
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category = $_POST['category'];
            $image = $_POST['image_url'];
            
            $sql = "INSERT INTO products (name, description, price_per_250g, current_stock, category, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssddss", $name, $description, $price, $stock, $category, $image);
            $stmt->execute();
            break;
            
        case 'update_product':
            $product_id = $_POST['product_id'];
            $field = $_POST['field'];
            $value = $_POST['value'];
            
            $sql = "UPDATE products SET $field = ? WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $value, $product_id);
            $stmt->execute();
            break;
            
        case 'delete_product':
            $product_id = $_POST['product_id'];
            
            $sql = "DELETE FROM products WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            break;
    }
}

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_stock = $conn->query("SELECT SUM(current_stock) as total FROM products")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'];

// Get products for display
$products = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Grocery Voice Bot</title>
    <style>
        :root {
            --primary: #4CAF50;
            --primary-dark: #2E7D32;
            --secondary: #2196F3;
            --danger: #f44336;
            --warning: #ff9800;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 250px;
            background: var(--dark);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .logo {
            padding: 0 20px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo h2 {
            color: var(--primary);
            font-size: 24px;
        }
        
        .nav-links {
            list-style: none;
            padding: 20px 0;
        }
        
        .nav-links li {
            margin: 5px 0;
        }
        
        .nav-links a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-links a.active {
            background: var(--primary);
            color: white;
            border-left: 4px solid white;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .header h1 {
            color: var(--dark);
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s;
            border-top: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: var(--dark);
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: var(--primary);
            color: white;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlide 0.3s ease-out;
        }
        
        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2>🛒 Admin Panel</h2>
        </div>
        
        <ul class="nav-links">
            <li><a href="#" class="active">📊 Dashboard</a></li>
            <li><a href="#products">📦 Products</a></li>
            <li><a href="#orders">📋 Orders</a></li>
            <li><a href="#users">👥 Users</a></li>
            <li><a href="#reports">📈 Reports</a></li>
            <li><a href="#settings">⚙️ Settings</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <a href="?logout=1" class="logout-btn">🚪 Logout</a>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card" style="border-color: var(--primary);">
                <div class="stat-icon">📦</div>
                <h3>Total Products</h3>
                <div class="stat-number"><?php echo $total_products; ?></div>
            </div>
            
            <div class="stat-card" style="border-color: var(--secondary);">
                <div class="stat-icon">📊</div>
                <h3>Total Stock</h3>
                <div class="stat-number"><?php echo number_format($total_stock ?? 0, 1); ?> kg</div>
            </div>
            
            <div class="stat-card" style="border-color: var(--warning);">
                <div class="stat-icon">📋</div>
                <h3>Total Orders</h3>
                <div class="stat-number"><?php echo $total_orders; ?></div>
            </div>
            
            <div class="stat-card" style="border-color: var(--danger);">
                <div class="stat-icon">💰</div>
                <h3>Total Revenue</h3>
                <div class="stat-number">₹<?php echo number_format($revenue ?? 0, 2); ?></div>
            </div>
        </div>
        
        <!-- Products Management -->
        <div id="products">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>📦 Product Management</h2>
                <button class="btn btn-primary" onclick="openAddProductModal()">+ Add New Product</button>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price/250g</th>
                            <th>Stock (kg)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo ucfirst($product['category']); ?></td>
                            <td>₹<?php echo $product['price_per_250g']; ?></td>
                            <td><?php echo $product['current_stock']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" 
                                        onclick="editProduct(<?php echo $product['product_id']; ?>)">
                                    ✏️ Edit
                                </button>
                                <button class="btn btn-sm btn-danger" 
                                        onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: 20px;">➕ Add New Product</h2>
            <form id="addProductForm" method="POST">
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Price per 250g (₹)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Stock (kg)</label>
                    <input type="number" step="0.01" name="stock" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control" required>
                        <option value="vegetables">🥦 Vegetables</option>
                        <option value="fruits">🍎 Fruits</option>
                        <option value="snacks">🍿 Snacks</option>
                        <option value="dairy">🥛 Dairy</option>
                        <option value="beverages">🥤 Beverages</option>
                        <option value="household">🏠 Household</option>
                        <option value="personal-care">🧴 Personal Care</option>
                        <option value="frozen-foods">🧊 Frozen Foods</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Image URL (Optional)</label>
                    <input type="url" name="image_url" class="form-control" 
                           placeholder="https://example.com/image.jpg">
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        Add Product
                    </button>
                    <button type="button" class="btn" onclick="closeModal('addProductModal')" 
                            style="background: #ddd; flex: 1;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal Functions
        function openAddProductModal() {
            document.getElementById('addProductModal').style.display = 'flex';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        // Product Management
        function editProduct(productId) {
            alert('Edit product ' + productId + ' - Implementation needed');
            // In real implementation, show edit modal with product data
        }
        
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="${productId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Form submission feedback
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = 'Adding...';
            btn.disabled = true;
        });
        
        // Real-time updates (simulated)
        setInterval(() => {
            // Update stats in real-time
            document.querySelectorAll('.stat-number').forEach(stat => {
                const value = parseInt(stat.textContent.replace(/,/g, ''));
                if (!isNaN(value)) {
                    const change = Math.floor(Math.random() * 10) - 5;
                    stat.textContent = (value + change).toLocaleString();
                }
            });
        }, 5000);
    </script>
</body>
</html>
<?php $conn->close(); ?>