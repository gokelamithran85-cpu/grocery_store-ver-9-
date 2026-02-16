<?php
session_start();
require_once 'includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.gst_percentage, p.discount_percentage 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_result = $conn->query($cart_query);

if ($cart_result->num_rows == 0) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
$total_gst = 0;
$total_discount = 0;

while($item = $cart_result->fetch_assoc()) {
    $item_price = $item['price'];
    $item_discount = $item['discount_percentage'] > 0 ? $item_price * ($item['discount_percentage'] / 100) : 0;
    $item_gst = ($item_price - $item_discount) * ($item['gst_percentage'] / 100);
    
    $subtotal += $item_price * $item['quantity'];
    $total_discount += $item_discount * $item['quantity'];
    $total_gst += $item_gst * $item['quantity'];
}

$final_total = $subtotal - $total_discount + $total_gst;

// Process order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $delivery_method = mysqli_real_escape_string($conn, $_POST['delivery_method']);
    $delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $delivery_distance = mysqli_real_escape_string($conn, $_POST['delivery_distance']);
    
    // Generate order number
    $order_number = 'ORD' . strtoupper(uniqid());
    
    // Insert order
    $order_query = "INSERT INTO orders (user_id, order_number, total_amount, gst_amount, discount_amount, 
                    final_amount, payment_method, delivery_method, delivery_address, delivery_distance) 
                    VALUES ($user_id, '$order_number', $subtotal, $total_gst, $total_discount, 
                    $final_total, '$payment_method', '$delivery_method', '$delivery_address', '$delivery_distance')";
    
    if ($conn->query($order_query)) {
        $order_id = $conn->insert_id;
        
        // Insert order items
        $cart_items = $conn->query("SELECT c.*, p.name, p.price, p.gst_percentage, p.discount_percentage 
                                   FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
        
        while($item = $cart_items->fetch_assoc()) {
            $item_price = $item['price'];
            $item_discount = $item['discount_percentage'] > 0 ? $item_price * ($item['discount_percentage'] / 100) : 0;
            $item_gst = ($item_price - $item_discount) * ($item['gst_percentage'] / 100);
            
            $insert_item = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit, 
                           price, gst_amount, discount_amount) 
                           VALUES ($order_id, {$item['product_id']}, '{$item['name']}', 
                           {$item['quantity']}, '{$item['unit']}', {$item['price']}, 
                           {$item_gst}, {$item_discount})";
            $conn->query($insert_item);
        }
        
        // Clear cart
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");
        
        $_SESSION['order_success'] = true;
        $_SESSION['order_number'] = $order_number;
        
        header('Location: order_success.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h1 class="section-title">Checkout</h1>

        <div class="checkout-container">
            <div class="checkout-form">
                <form method="POST" action="">
                    <div class="checkout-section">
                        <h3>Delivery Address</h3>
                        <div class="form-group">
                            <textarea name="delivery_address" rows="4" required><?php echo $user['address']; ?></textarea>
                        </div>
                        
                        <div class="delivery-distance" id="deliveryDistanceMessage">
                            <button type="button" onclick="calculateDeliveryDistance()" class="btn btn-secondary">
                                <i class="fas fa-location-dot"></i> Calculate Distance
                            </button>
                        </div>
                        <input type="hidden" name="delivery_distance" id="deliveryDistance" value="0">
                    </div>

                    <div class="checkout-section">
                        <h3>Delivery Method</h3>
                        <div class="delivery-options">
                            <label class="delivery-option">
                                <input type="radio" name="delivery_method" value="pickup" checked>
                                <div class="option-content">
                                    <i class="fas fa-store"></i>
                                    <div>
                                        <h4>Pickup from Store</h4>
                                        <p>Free - Collect from our store</p>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="delivery-option">
                                <input type="radio" name="delivery_method" value="home_delivery">
                                <div class="option-content">
                                    <i class="fas fa-truck"></i>
                                    <div>
                                        <h4>Home Delivery</h4>
                                        <p>₹50 - Within 15km radius</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <h3>Payment Method</h3>
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cash" checked>
                                <div class="option-content">
                                    <i class="fas fa-money-bill"></i>
                                    <span>Cash on Delivery</span>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="gpay">
                                <div class="option-content">
                                    <i class="fab fa-google-pay"></i>
                                    <span>Google Pay</span>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="paytm">
                                <div class="option-content">
                                    <i class="fas fa-qrcode"></i>
                                    <span>Paytm</span>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="card">
                                <div class="option-content">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Credit/Debit Card</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-block btn-large">Place Order</button>
                </form>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <div class="order-items">
                    <?php 
                    $cart_items = $conn->query("SELECT c.*, p.name, p.price, p.unit 
                                               FROM cart c JOIN products p ON c.product_id = p.id 
                                               WHERE c.user_id = $user_id");
                    while($item = $cart_items->fetch_assoc()): 
                    ?>
                    <div class="order-item">
                        <span class="item-name"><?php echo $item['name']; ?></span>
                        <span class="item-quantity"><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></span>
                        <span class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="summary-details">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Discount:</span>
                        <span class="discount">-₹<?php echo number_format($total_discount, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>GST:</span>
                        <span>₹<?php echo number_format($total_gst, 2); ?></span>
                    </div>
                    
                    <div class="summary-row delivery-fee">
                        <span>Delivery Fee:</span>
                        <span id="deliveryFee">₹0</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span class="cart-total">₹<?php echo number_format($final_total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Update delivery fee based on method
    document.querySelectorAll('input[name="delivery_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const deliveryFee = this.value === 'home_delivery' ? 50 : 0;
            document.getElementById('deliveryFee').textContent = `₹${deliveryFee}`;
            
            const total = <?php echo $final_total; ?> + deliveryFee;
            document.querySelector('.cart-total').textContent = `₹${total.toFixed(2)}`;
        });
    });
    </script>

   <!-- At the bottom of each page, before footer -->
<script src="voice-command.js"></script>
<script src="script.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
