<?php
require_once 'includes/db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle quantity update
if (isset($_POST['update_cart'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (float)$_POST['quantity'];
    
    // Get product max quantity
    $product_query = "SELECT p.max_quantity, p.stock_quantity 
                      FROM cart c 
                      JOIN products p ON c.product_id = p.id 
                      WHERE c.id = $cart_id AND c.user_id = $user_id";
    $product_result = $conn->query($product_query);
    
    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        
        if ($quantity <= $product['max_quantity'] && $quantity <= $product['stock_quantity']) {
            $update_query = "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id";
            $conn->query($update_query);
            $_SESSION['success'] = 'Cart updated successfully';
        } else {
            $_SESSION['error'] = 'Quantity exceeds limit or stock';
        }
    }
    
    header('Location: cart.php');
    exit();
}

// Handle remove item
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $delete_query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
    $conn->query($delete_query);
    $_SESSION['success'] = 'Item removed from cart';
    header('Location: cart.php');
    exit();
}

// Handle clear cart
if (isset($_GET['clear'])) {
    $clear_query = "DELETE FROM cart WHERE user_id = $user_id";
    $conn->query($clear_query);
    $_SESSION['success'] = 'Cart cleared successfully';
    header('Location: cart.php');
    exit();
}

// Fetch cart items with proper JOIN
$cart_query = "SELECT c.*, 
                      p.id as product_id, 
                      p.name, 
                      p.price, 
                      p.gst_percentage, 
                      p.discount_percentage, 
                      p.stock_quantity, 
                      p.unit, 
                      p.product_image,
                      p.max_quantity
               FROM cart c 
               INNER JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id
               ORDER BY c.added_at DESC";

$cart_result = $conn->query($cart_query);

if (!$cart_result) {
    die("Cart query failed: " . $conn->error);
}

// Calculate totals
$subtotal = 0;
$total_gst = 0;
$total_discount = 0;
$cart_items = array();

if ($cart_result->num_rows > 0) {
    while($item = $cart_result->fetch_assoc()) {
        // Calculate item prices
        $item_price = $item['price'];
        $item_discount = $item['discount_percentage'] > 0 ? $item_price * ($item['discount_percentage'] / 100) : 0;
        $item_gst = ($item_price - $item_discount) * ($item['gst_percentage'] / 100);
        $item_total = ($item_price - $item_discount + $item_gst) * $item['quantity'];
        
        // Add to totals
        $subtotal += $item_price * $item['quantity'];
        $total_discount += $item_discount * $item['quantity'];
        $total_gst += $item_gst * $item['quantity'];
        
        // Store for display
        $item['calculated_price'] = $item_price;
        $item['calculated_discount'] = $item_discount;
        $item['calculated_gst'] = $item_gst;
        $item['calculated_total'] = $item_total;
        
        $cart_items[] = $item;
    }
}

$final_total = $subtotal - $total_discount + $total_gst;

// Get success/error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .cart-header h1 {
            font-size: 2rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .cart-header h1 i {
            color: var(--primary-color);
        }
        
        .cart-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }
        
        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid var(--gray-light);
            align-items: center;
            transition: background 0.3s ease;
        }
        
        .cart-item:hover {
            background: var(--light-color);
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 100px;
            height: 100px;
            background: var(--light-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--gray-color);
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-details h3 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: var(--dark-color);
        }
        
        .item-price {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .item-discount {
            color: var(--secondary-color);
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .item-stock {
            font-size: 0.8rem;
            color: var(--gray-color);
            margin-top: 5px;
        }
        
        .item-quantity {
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: center;
        }
        
        .quantity-form {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .quantity-input {
            width: 70px;
            padding: 8px;
            border: 2px solid var(--gray-light);
            border-radius: 5px;
            text-align: center;
            font-size: 1rem;
        }
        
        .quantity-input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .update-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .update-btn:hover {
            background: var(--accent-dark);
            transform: scale(1.05);
        }
        
        .max-limit {
            font-size: 0.75rem;
            color: var(--gray-color);
        }
        
        .item-total {
            text-align: right;
        }
        
        .total-label {
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-bottom: 5px;
        }
        
        .total-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .item-actions {
            display: flex;
            gap: 10px;
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: var(--danger-color);
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
        }
        
        .remove-btn:hover {
            background: var(--danger-color);
            color: white;
            transform: scale(1.1);
        }
        
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .cart-summary h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--gray-light);
        }
        
        .summary-item.total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            border-bottom: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--dark-color);
        }
        
        .discount-amount {
            color: var(--success-color);
        }
        
        .discount-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid var(--light-color);
        }
        
        .discount-form {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .discount-form input {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--gray-light);
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .discount-form input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .btn-apply {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-apply:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
        }
        
        .btn-checkout {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(76, 175, 80, 0.3);
        }
        
        .btn-continue {
            display: block;
            width: 100%;
            background: var(--gray-light);
            color: var(--dark-color);
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-continue:hover {
            background: var(--gray-color);
            color: white;
        }
        
        .btn-clear {
            background: none;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-clear:hover {
            background: var(--danger-color);
            color: white;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .empty-cart i {
            font-size: 80px;
            color: var(--gray-light);
            margin-bottom: 20px;
        }
        
        .empty-cart h2 {
            font-size: 1.8rem;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .empty-cart p {
            color: var(--gray-color);
            margin-bottom: 25px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .item-image {
                margin: 0 auto;
            }
            
            .item-total {
                text-align: center;
            }
            
            .item-actions {
                justify-content: center;
            }
            
            .quantity-form {
                justify-content: center;
            }
        }

        .voice-cart-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .voice-cart-btn:hover {
            transform: scale(1.1);
            background: var(--secondary-dark);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="cart-container">
        <div class="cart-header">
            <h1>
                <i class="fas fa-shopping-cart"></i> 
                Your Shopping Cart
                <button class="voice-cart-btn" onclick="startVoiceRecognition()" title="Voice control cart">
                    <i class="fas fa-microphone"></i>
                </button>
            </h1>
            
            <?php if (count($cart_items) > 0): ?>
                <a href="?clear=1" class="btn-clear" onclick="return confirm('Are you sure you want to clear your cart?')">
                    <i class="fas fa-trash"></i> Clear Cart
                </a>
            <?php endif; ?>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (count($cart_items) > 0): ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach($cart_items as $item): ?>
                        <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                            <div class="item-image">
                                <?php if($item['product_image']): ?>
                                    <img src="uploads/products/<?php echo $item['product_image']; ?>" 
                                         alt="<?php echo $item['name']; ?>">
                                <?php else: ?>
                                    <i class="fas fa-box"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div class="item-price">₹<?php echo number_format($item['price'], 2); ?> per <?php echo $item['unit']; ?></div>
                                <?php if($item['discount_percentage'] > 0): ?>
                                    <div class="item-discount">
                                        <i class="fas fa-tag"></i> <?php echo $item['discount_percentage']; ?>% off
                                    </div>
                                <?php endif; ?>
                                <div class="item-stock">
                                    <i class="fas fa-boxes"></i> In Stock: <?php echo $item['stock_quantity']; ?> <?php echo $item['unit']; ?>
                                </div>
                            </div>
                            
                            <div class="item-quantity">
                                <form method="POST" action="" class="quantity-form">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="0.1" max="<?php echo min($item['max_quantity'], $item['stock_quantity']); ?>" 
                                           step="0.1" class="quantity-input" 
                                           onchange="this.form.submit()">
                                    <button type="submit" name="update_cart" class="update-btn">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                                <span class="max-limit">Max: <?php echo $item['max_quantity']; ?> <?php echo $item['unit']; ?></span>
                            </div>
                            
                            <div class="item-total">
                                <div class="total-label">Total:</div>
                                <div class="total-amount">₹<?php echo number_format($item['calculated_total'], 2); ?></div>
                                <?php if($item['discount_percentage'] > 0): ?>
                                    <div style="font-size: 0.8rem; color: var(--success-color);">
                                        Saved: ₹<?php echo number_format($item['calculated_discount'] * $item['quantity'], 2); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-actions">
                                <a href="?remove=<?php echo $item['id']; ?>" class="remove-btn" 
                                   onclick="return confirm('Remove this item from cart?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <button class="voice-cart-btn" onclick="voiceAddToCart('<?php echo $item['name']; ?>', <?php echo $item['product_id']; ?>)" 
                                        title="Add more by voice" style="width: 35px; height: 35px; font-size: 16px;">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3><i class="fas fa-file-invoice"></i> Order Summary</h3>
                    
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if($total_discount > 0): ?>
                        <div class="summary-item">
                            <span>Total Discount:</span>
                            <span class="discount-amount">-₹<?php echo number_format($total_discount, 2); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-item">
                        <span>GST:</span>
                        <span>₹<?php echo number_format($total_gst, 2); ?></span>
                    </div>
                    
                    <div class="summary-item total">
                        <span>Total Amount:</span>
                        <span class="cart-total">₹<?php echo number_format($final_total, 2); ?></span>
                    </div>

                    <div class="discount-section">
                        <label for="discount_code">
                            <i class="fas fa-ticket-alt"></i> Apply Discount Code
                        </label>
                        <div class="discount-form">
                            <input type="text" id="discount_code" placeholder="Enter coupon code">
                            <button onclick="applyDiscount()" class="btn-apply">Apply</button>
                        </div>
                    </div>

                    <a href="checkout.php" class="btn-checkout">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                    <a href="index.php" class="btn-continue">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                    
                    <!-- Voice Commands for Cart -->
                    <div style="margin-top: 20px; padding: 15px; background: var(--light-color); border-radius: 8px;">
                        <p style="font-size: 0.9rem; color: var(--gray-color); margin-bottom: 10px;">
                            <i class="fas fa-microphone-alt"></i> Voice Commands:
                        </p>
                        <ul style="list-style: none; padding: 0; font-size: 0.85rem;">
                            <li style="margin-bottom: 5px;">• "Update quantity"</li>
                            <li style="margin-bottom: 5px;">• "Remove item"</li>
                            <li style="margin-bottom: 5px;">• "Apply discount"</li>
                            <li style="margin-bottom: 5px;">• "Checkout"</li>
                            <li style="margin-bottom: 5px;">• "Clear cart"</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your Cart is Empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <p style="margin-bottom: 30px;">Try saying: <strong>"Add 1kg carrots"</strong> or browse our sections.</p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="vegetables.php" class="btn-checkout" style="width: auto; background: var(--secondary-color);">
                        <i class="fas fa-carrot"></i> Vegetables
                    </a>
                    <a href="fruits.php" class="btn-checkout" style="width: auto; background: var(--accent-color);">
                        <i class="fas fa-apple-alt"></i> Fruits
                    </a>
                    <a href="dairy.php" class="btn-checkout" style="width: auto; background: var(--primary-color);">
                        <i class="fas fa-milk"></i> Dairy
                    </a>
                    <a href="index.php" class="btn-continue" style="width: auto;">
                        <i class="fas fa-home"></i> Home
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Voice add to cart function
    function voiceAddToCart(productName, productId) {
        if (!('webkitSpeechRecognition' in window)) {
            alert('Voice recognition not supported');
            return;
        }

        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            showNotification(`How much ${productName} do you want?`, 'info');
        };

        recognition.onresult = function(event) {
            const command = event.results[0][0].transcript.toLowerCase();
            const match = command.match(/(\d+(?:\.\d+)?)\s*(kg|g|gram|kilo)/i);
            
            if (match) {
                let quantity = parseFloat(match[1]);
                const unit = match[2].toLowerCase();
                
                if (unit === 'g' || unit === 'gram') {
                    quantity = quantity / 1000;
                }
                
                if (quantity > 5) {
                    showNotification('Maximum quantity is 5kg', 'warning');
                    return;
                }
                
                // Add to cart via AJAX
                addToCart(productId, quantity);
            } else {
                showNotification('Please specify quantity (e.g., "1kg")', 'error');
            }
        };

        recognition.onerror = function(event) {
            showNotification('Voice input failed: ' + event.error, 'error');
        };

        recognition.start();
    }

    // Show notification function
    function showNotification(message, type = 'info') {
        const toast = document.getElementById('voiceToast');
        const toastMessage = document.getElementById('voiceToastMessage');
        
        if (toast && toastMessage) {
            toastMessage.textContent = message;
            toast.className = 'voice-toast ' + type;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        } else {
            alert(message);
        }
    }

    // Apply discount function
    function applyDiscount() {
        const code = document.getElementById('discount_code').value;
        if (!code) {
            showNotification('Please enter a discount code', 'warning');
            return;
        }
        
        // Simulate discount application
        showNotification('Discount applied: 10% off!', 'success');
    }

    // Voice recognition for cart
    function startVoiceRecognition() {
        if (!('webkitSpeechRecognition' in window)) {
            showNotification('Voice recognition not supported', 'error');
            return;
        }

        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            showNotification('Listening for cart commands...', 'listening');
        };

        recognition.onresult = function(event) {
            const command = event.results[0][0].transcript.toLowerCase();
            
            if (command.includes('checkout')) {
                window.location.href = 'checkout.php';
            } else if (command.includes('clear')) {
                if (confirm('Clear your cart?')) {
                    window.location.href = '?clear=1';
                }
            } else if (command.includes('continue') || command.includes('shop')) {
                window.location.href = 'index.php';
            } else {
                showNotification('Command not recognized', 'error');
            }
        };

        recognition.onerror = function(event) {
            showNotification('Voice recognition failed', 'error');
        };

        recognition.start();
    }
    </script>

    <!-- At the bottom of each page, before footer -->
<script src="voice-command.js"></script>
<script src="script.js"></script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>