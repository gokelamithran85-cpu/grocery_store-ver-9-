<?php
require_once 'includes/db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get order ID if viewing single order
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id > 0) {
    // Fetch single order
    $order_query = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
    $order_result = $conn->query($order_query);
    
    if ($order_result->num_rows == 0) {
        header('Location: orders.php');
        exit();
    }
    
    $order = $order_result->fetch_assoc();
    
    // Fetch order items
    $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
    $items_result = $conn->query($items_query);
} else {
    // Fetch all orders
    $orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
    $orders_result = $conn->query($orders_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .orders-header h1 {
            font-size: 2rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .orders-header h1 i {
            color: var(--primary-color);
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-color);
        }
        
        .order-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .order-date {
            color: var(--gray-color);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .order-status {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed var(--gray-light);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
        }
        
        .item-quantity {
            color: var(--gray-color);
        }
        
        .item-price {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid var(--light-color);
        }
        
        .order-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-view {
            background: var(--accent-color);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }
        
        .btn-reorder {
            background: var(--primary-color);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-reorder:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-back {
            background: var(--gray-color);
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: var(--gray-dark);
            transform: translateX(-5px);
        }
        
        .no-orders {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .no-orders i {
            font-size: 80px;
            color: var(--gray-light);
            margin-bottom: 20px;
        }
        
        .no-orders h2 {
            font-size: 1.8rem;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .no-orders p {
            color: var(--gray-color);
            margin-bottom: 25px;
        }
        
        .single-order {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .order-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .order-info-section {
            background: var(--light-color);
            padding: 20px;
            border-radius: 10px;
        }
        
        .order-info-section h3 {
            margin-bottom: 15px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            width: 120px;
            color: var(--gray-color);
        }
        
        .info-value {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-items-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .order-summary {
            margin-top: 30px;
            padding: 20px;
            background: var(--light-color);
            border-radius: 10px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-row.total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            border-top: 2px solid var(--gray-light);
            margin-top: 10px;
            padding-top: 10px;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .order-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .order-detail-grid {
                grid-template-columns: 1fr;
            }
            
            .order-items-table {
                font-size: 0.9rem;
            }
            
            .orders-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="orders-container">
        <?php if ($order_id > 0 && isset($order)): ?>
            <!-- Single Order View -->
            <div class="orders-header">
                <h1>
                    <i class="fas fa-shopping-bag"></i> 
                    Order #<?php echo $order['order_number']; ?>
                </h1>
                <a href="orders.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>
            
            <div class="single-order">
                <div class="order-detail-grid">
                    <div class="order-info-section">
                        <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                        <div class="info-row">
                            <span class="info-label">Order Date:</span>
                            <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Order Status:</span>
                            <span class="info-value">
                                <span class="order-status status-<?php echo $order['order_status']; ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value"><?php echo ucfirst($order['payment_method']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Delivery Method:</span>
                            <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $order['delivery_method'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-info-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
                        <div class="info-value" style="line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                        </div>
                        <?php if($order['delivery_distance'] > 0): ?>
                            <div style="margin-top: 15px; padding: 10px; background: rgba(33,150,243,0.1); border-radius: 5px;">
                                <i class="fas fa-location-dot"></i> 
                                Distance: <?php echo $order['delivery_distance']; ?> km from store
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h3 style="margin: 30px 0 15px;"><i class="fas fa-box"></i> Order Items</h3>
                
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>GST</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $items_subtotal = 0;
                        while($item = $items_result->fetch_assoc()): 
                            $item_total = ($item['price'] - $item['discount_amount'] + $item['gst_amount']) * $item['quantity'];
                            $items_subtotal += $item_total;
                        ?>
                        <tr>
                            <td><?php echo $item['product_name']; ?></td>
                            <td><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td>-₹<?php echo number_format($item['discount_amount'], 2); ?></td>
                            <td>₹<?php echo number_format($item['gst_amount'], 2); ?></td>
                            <td><strong>₹<?php echo number_format($item_total, 2); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Discount:</span>
                        <span>-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>GST:</span>
                        <span>₹<?php echo number_format($order['gst_amount'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery Fee:</span>
                        <span><?php echo $order['delivery_method'] == 'home_delivery' ? '₹50' : 'Free'; ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <span>₹<?php echo number_format($order['final_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <a href="checkout.php?reorder=<?php echo $order['id']; ?>" class="btn-reorder">
                        <i class="fas fa-redo-alt"></i> Reorder
                    </a>
                    <?php if($order['order_status'] == 'pending'): ?>
                        <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn-back" 
                           onclick="return confirm('Are you sure you want to cancel this order?')">
                            <i class="fas fa-times"></i> Cancel Order
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- All Orders View -->
            <div class="orders-header">
                <h1>
                    <i class="fas fa-shopping-bag"></i> 
                    My Orders
                </h1>
                <a href="profile.php" class="btn-back">
                    <i class="fas fa-user"></i> Back to Profile
                </a>
            </div>
            
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                <?php while($order = $orders_result->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <span class="order-number">Order #<?php echo $order['order_number']; ?></span>
                                <span class="order-date">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?>
                                </span>
                            </div>
                            <span class="order-status status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                        
                        <div class="order-footer">
                            <div>
                                <span style="color: var(--gray-color);">Total Amount:</span>
                                <span class="order-total">₹<?php echo number_format($order['final_amount'], 2); ?></span>
                            </div>
                            <div class="order-actions">
                                <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="checkout.php?reorder=<?php echo $order['id']; ?>" class="btn-reorder">
                                    <i class="fas fa-redo-alt"></i> Reorder
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-box-open"></i>
                    <h2>No Orders Yet</h2>
                    <p>You haven't placed any orders with us yet.</p>
                    <a href="index.php" class="btn-reorder" style="padding: 12px 30px;">
                        <i class="fas fa-shopping-cart"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="voice-command.js"></script>
    <script src="script.js"></script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>