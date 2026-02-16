<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user details
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get cart items
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product_sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($product_sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if ($product) {
            $price = $product['price_per_250g'] * ($quantity / 0.25);
            $cart_items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => $price
            ];
            $total += $price;
        }
    }
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($cart_items)) {
        $error = 'Your cart is empty!';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $order_sql = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
            $stmt = $conn->prepare($order_sql);
            $stmt->bind_param("id", $user_id, $total);
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Add order items and update stock
            foreach ($cart_items as $item) {
                $product_id = $item['product']['product_id'];
                $quantity = $item['quantity'];
                
                // Add order item
                $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                             VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($item_sql);
                $stmt->bind_param("iidd", $order_id, $product_id, $quantity, $item['price']);
                $stmt->execute();
                
                // Update stock
                $update_sql = "UPDATE products SET current_stock = current_stock - ? 
                               WHERE product_id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("di", $quantity, $product_id);
                $stmt->execute();
            }
            
            // Send email receipt
            $receipt_sent = sendEmailReceipt($user['email'], $order_id, $cart_items, $total, $user);
            
            // Clear cart
            unset($_SESSION['cart']);
            
            $conn->commit();
            
            $success = 'Order placed successfully! Order ID: #' . $order_id;
            if ($receipt_sent) {
                $success .= ' (Receipt sent to your email)';
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Order failed: ' . $e->getMessage();
        }
    }
}

// Email receipt function
function sendEmailReceipt($email, $order_id, $items, $total, $user) {
    $to = $email;
    $subject = "Order Confirmation #$order_id - Grocery Voice Bot";
    
    // Create HTML email content
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
            .container { max-width: 600px; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
            .header { text-align: center; background: #4CAF50; color: white; padding: 20px; border-radius: 10px 10px 0 0; margin: -30px -30px 30px -30px; }
            .order-details { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #4CAF50; color: white; }
            .total { font-size: 20px; font-weight: bold; color: #4CAF50; text-align: right; }
            .footer { text-align: center; color: #666; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🛒 Grocery Voice Bot</h1>
                <h2>Order Confirmation</h2>
            </div>
            
            <h3>Thank you for your order, ' . htmlspecialchars($user['username']) . '!</h3>
            <p>Your order has been received and is being processed.</p>
            
            <div class="order-details">
                <p><strong>Order ID:</strong> #' . $order_id . '</p>
                <p><strong>Order Date:</strong> ' . date('F j, Y g:i A') . '</p>
                <p><strong>Delivery Address:</strong> ' . htmlspecialchars($user['address']) . '</p>
            </div>
            
            <h3>Order Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($items as $item) {
        $message .= '
                    <tr>
                        <td>' . htmlspecialchars($item['product']['name']) . '</td>
                        <td>' . $item['quantity'] . ' kg</td>
                        <td>₹' . number_format($item['product']['price_per_250g'] * 4, 2) . '/kg</td>
                        <td>₹' . number_format($item['price'], 2) . '</td>
                    </tr>';
    }
    
    $message .= '
                </tbody>
            </table>
            
            <div class="total">
                Total Amount: ₹' . number_format($total, 2) . '
            </div>
            
            <div class="footer">
                <p>Thank you for shopping with Grocery Voice Bot!</p>
                <p>Need help? Contact us at support@groceryvoicebot.com</p>
                <p>© 2024 Grocery Voice Bot. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Grocery Voice Bot <noreply@groceryvoicebot.com>" . "\r\n";
    
    // Send email (in production, use PHPMailer or similar)
    // For demo purposes, we'll just simulate email sending
    // In real implementation: mail($to, $subject, $message, $headers);
    
    // Save email to file for demo
    $filename = "receipts/order_$order_id.html";
    file_put_contents($filename, $message);
    
    return true; // Simulate successful email
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Grocery Voice Bot</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .checkout-header {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .checkout-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background: white;
            color: #4CAF50;
        }
        
        .checkout-content {
            display: flex;
            padding: 40px;
            gap: 40px;
        }
        
        .order-summary {
            flex: 2;
        }
        
        .payment-section {
            flex: 1;
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
        }
        
        .cart-items {
            margin-bottom: 30px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: white;
            border: 1px solid #eee;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .cart-item:hover {
            border-color: #4CAF50;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.1);
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-price {
            font-weight: bold;
            color: #4CAF50;
            font-size: 18px;
        }
        
        .total-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .final-total {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
        }
        
        .payment-methods {
            margin: 20px 0;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: #4CAF50;
        }
        
        .payment-method.selected {
            border-color: #4CAF50;
            background: #e8f5e9;
        }
        
        .place-order-btn {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(76, 175, 80, 0.3);
        }
        
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                flex-direction: column;
            }
            
            .checkout-steps {
                flex-wrap: wrap;
                gap: 20px;
            }
        }
        
        .user-details {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .user-details h3 {
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <p>Complete your purchase</p>
            
            <div class="checkout-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div>Cart</div>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div>Delivery</div>
                </div>
                <div class="step active">
                    <div class="step-number">3</div>
                    <div>Payment</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div>Confirmation</div>
                </div>
            </div>
        </div>
        
        <div class="checkout-content">
            <div class="order-summary">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?>
                        <br><br>
                        <a href="orders.php" style="color: #2e7d32; text-decoration: underline;">View Your Orders</a> | 
                        <a href="products.php" style="color: #2e7d32; text-decoration: underline;">Continue Shopping</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!$success): ?>
                <div class="user-details">
                    <h3>👤 Delivery Details</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                    <p style="margin-top: 10px;">
                        <a href="profile.php" style="color: #4CAF50; text-decoration: none;">
                            ✏️ Edit Profile
                        </a>
                    </p>
                </div>
                
                <h2 style="margin-bottom: 20px;">🛒 Order Summary</h2>
                
                <div class="cart-items">
                    <?php if (empty($cart_items)): ?>
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <h3>Your cart is empty</h3>
                            <p>Add some products to proceed with checkout</p>
                            <a href="products.php" style="color: #4CAF50; text-decoration: none; font-weight: bold;">
                                ← Continue Shopping
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <?php 
                                    $icons = [
                                        'carrot' => '🥕',
                                        'tomato' => '🍅',
                                        'potato' => '🥔',
                                        'onion' => '🧅',
                                        'apple' => '🍎',
                                        'banana' => '🍌'
                                    ];
                                    echo $icons[strtolower($item['product']['name'])] ?? '📦';
                                ?>
                            </div>
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['product']['name']); ?></h4>
                                <p>Quantity: <?php echo $item['quantity']; ?> kg</p>
                                <p>Price per kg: ₹<?php echo number_format($item['product']['price_per_250g'] * 4, 2); ?></p>
                            </div>
                            <div class="item-price">
                                ₹<?php echo number_format($item['price'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!$success && !empty($cart_items)): ?>
            <div class="payment-section">
                <h2 style="margin-bottom: 25px;">💳 Payment</h2>
                
                <div class="total-section">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>FREE</span>
                    </div>
                    <div class="total-row">
                        <span>Tax:</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="total-row final-total">
                        <span>Total:</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
                
                <h3 style="margin: 25px 0 15px;">Payment Method</h3>
                <div class="payment-methods">
                    <div class="payment-method selected" onclick="selectPayment(this)">
                        <input type="radio" name="payment" value="cod" checked hidden>
                        <span style="font-size: 24px;">💵</span>
                        <div>
                            <strong>Cash on Delivery</strong>
                            <p style="font-size: 14px; color: #666; margin-top: 5px;">
                                Pay when your order arrives
                            </p>
                        </div>
                    </div>
                    
                    <div class="payment-method" onclick="selectPayment(this)">
                        <input type="radio" name="payment" value="card" hidden>
                        <span style="font-size: 24px;">💳</span>
                        <div>
                            <strong>Credit/Debit Card</strong>
                            <p style="font-size: 14px; color: #666; margin-top: 5px;">
                                Pay securely with your card
                            </p>
                        </div>
                    </div>
                    
                    <div class="payment-method" onclick="selectPayment(this)">
                        <input type="radio" name="payment" value="upi" hidden>
                        <span style="font-size: 24px;">📱</span>
                        <div>
                            <strong>UPI</strong>
                            <p style="font-size: 14px; color: #666; margin-top: 5px;">
                                Pay using UPI apps
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="POST">
                    <button type="submit" class="place-order-btn">
                        Place Order & Receive Email Receipt
                    </button>
                    <p style="text-align: center; margin-top: 15px; color: #666; font-size: 14px;">
                        By placing order, you agree to our Terms & Conditions
                    </p>
                </form>
                
                <div style="margin-top: 30px; padding: 20px; background: #e8f5e9; border-radius: 10px;">
                    <h4>📧 Email Receipt</h4>
                    <p style="margin-top: 10px; font-size: 14px; color: #2e7d32;">
                        A detailed receipt will be sent to: <br>
                        <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function selectPayment(element) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
        }
        
        // Add animation to cart items
        document.querySelectorAll('.cart-item').forEach((item, index) => {
            item.style.animationDelay = (index * 0.1) + 's';
            item.style.animation = 'slideUp 0.5s ease-out forwards';
            item.style.opacity = '0';
        });
        
        // Add some visual feedback
        const placeOrderBtn = document.querySelector('.place-order-btn');
        if (placeOrderBtn) {
            placeOrderBtn.addEventListener('click', function(e) {
                this.innerHTML = 'Processing...';
                this.style.opacity = '0.7';
                this.disabled = true;
                
                // Add loading animation
                const spinner = document.createElement('span');
                spinner.innerHTML = ' ⏳';
                this.appendChild(spinner);
            });
        }
    </script>
    
    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html>
<?php $conn->close(); ?>