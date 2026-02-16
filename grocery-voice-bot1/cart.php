<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Grocery Voice Bot</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        h1 { color: #4CAF50; margin-bottom: 30px; }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .checkout-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 30px;
            width: 100%;
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 Your Shopping Cart</h1>
        
        <?php if(empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Add some products using voice commands!</p>
                <a href="index.php" class="back-btn">← Back to Shopping</a>
            </div>
        <?php else: ?>
            <div id="cart-items">
                <?php 
                $total = 0;
                require_once 'db_connection.php';
                
                foreach($_SESSION['cart'] as $productId => $quantity):
                    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
                    $stmt->bind_param("i", $productId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    
                    if($product):
                        $price = $product['price_per_250g'] * ($quantity / 0.25);
                        $total += $price;
                ?>
                <div class="cart-item">
                    <div>
                        <h3><?php echo $product['name']; ?></h3>
                        <p>Quantity: <?php echo $quantity; ?> kg</p>
                        <p>Price per kg: ₹<?php echo $product['price_per_250g'] * 4; ?></p>
                    </div>
                    <div>
                        <h3>₹<?php echo number_format($price, 2); ?></h3>
                        <button onclick="removeItem(<?php echo $productId; ?>)" 
                                style="background: #ff4444; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                            Remove
                        </button>
                    </div>
                </div>
                <?php 
                    endif;
                endforeach; 
                $conn->close();
                ?>
                
                <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold;">
                        <span>Total:</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
                
                <button class="checkout-btn" onclick="checkout()">
                    Proceed to Checkout
                </button>
            </div>
        <?php endif; ?>
        
        <a href="index.php" class="back-btn">← Continue Shopping</a>
    </div>
    
    <script>
        function removeItem(productId) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }
        
        function checkout() {
            alert('Checkout functionality would be implemented here!');
            // In real project, this would redirect to payment page
        }
    </script>
</body>
</html>