<?php
require_once 'includes/db_connection.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch category details
$category_query = "SELECT * FROM categories WHERE id = $category_id";
$category_result = $conn->query($category_query);

if ($category_result->num_rows == 0) {
    header('Location: index.php');
    exit();
}

$category = $category_result->fetch_assoc();

// Fetch products in this category - FIXED: Removed duplicate query
$products_query = "SELECT * FROM products WHERE category_id = $category_id AND stock_quantity > 0 ORDER BY name";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .category-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="white" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
            opacity: 0.2;
        }
        
        .category-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .category-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
        }
        
        .sort-select {
            padding: 0.5rem 1rem;
            border: 2px solid var(--gray-light);
            border-radius: var(--border-radius-md);
            font-family: var(--font-primary);
            cursor: pointer;
            background: white;
        }
        
        .product-count {
            color: var(--gray-color);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            margin: 2rem 0;
        }
        
        .no-products i {
            font-size: 4rem;
            color: var(--gray-light);
            margin-bottom: 1rem;
        }
        
        .no-products h3 {
            font-size: 1.5rem;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .no-products p {
            color: var(--gray-color);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Category Header -->
    <div class="category-header">
        <div class="container">
            <div class="category-icon">
                <?php
                $icons = [
                    1 => 'fas fa-carrot',
                    2 => 'fas fa-apple-alt',
                    3 => 'fas fa-milk',
                    4 => 'fas fa-bread-slice',
                    5 => 'fas fa-coffee'
                ];
                $icon = isset($icons[$category_id]) ? $icons[$category_id] : 'fas fa-shopping-basket';
                ?>
                <i class="<?php echo $icon; ?>"></i>
            </div>
            <h1><?php echo htmlspecialchars($category['name']); ?></h1>
            <p><?php echo htmlspecialchars($category['description']); ?></p>
        </div>
    </div>

    <div class="container">
        <!-- Filter and Sort Bar -->
        <div class="filter-bar">
            <div class="product-count">
                <i class="fas fa-box"></i> 
                <?php echo $products_result->num_rows; ?> Products Available
            </div>
            
            <div class="sort-options">
                <label for="sortBy">Sort by:</label>
                <select id="sortBy" class="sort-select" onchange="sortProducts(this.value)">
                    <option value="default">Default</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="name">Name: A to Z</option>
                    <option value="discount">Discount</option>
                </select>
            </div>
        </div>

        <!-- Products Grid - FIXED: Only one products grid -->
        <?php if ($products_result && $products_result->num_rows > 0): ?>
            <div class="products-grid" id="productsGrid">
                <?php while($product = $products_result->fetch_assoc()): ?>
                    <div class="product-card" data-product-id="<?php echo $product['id']; ?>"
                         data-price="<?php echo $product['price']; ?>" 
                         data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                         data-discount="<?php echo $product['discount_percentage']; ?>">
                        <div class="product-image">
                            <?php if($product['product_image']): ?>
                                <img src="uploads/products/<?php echo $product['product_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <i class="<?php echo $icon; ?>" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <?php endif; ?>
                            <?php if($product['discount_percentage'] > 0): ?>
                                <span class="product-discount"><?php echo $product['discount_percentage']; ?>% OFF</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-category">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($category['name']); ?>
                            </p>
                            <div class="product-price">
                                <?php 
                                $price = $product['price'];
                                $discount = $product['discount_percentage'];
                                $final_price = $discount > 0 ? $price * (1 - $discount/100) : $price;
                                ?>
                                <span class="current-price">₹<?php echo number_format($final_price, 2); ?></span>
                                <?php if($discount > 0): ?>
                                    <span class="original-price">₹<?php echo number_format($price, 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="product-stock">
                                <i class="fas fa-boxes"></i> 
                                Stock: <?php echo $product['stock_quantity']; ?> <?php echo $product['unit']; ?>
                            </p>
                            <div class="product-actions">
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn minus" onclick="decrementQuantity(this)">-</button>
                                    <input type="number" class="qty-input" value="1" 
                                           min="0.1" max="<?php echo min(5, $product['stock_quantity']); ?>" 
                                           step="0.1" readonly>
                                    <button type="button" class="qty-btn plus" onclick="incrementQuantity(this)">+</button>
                                    <span class="unit"><?php echo $product['unit']; ?></span>
                                </div>
                                <button type="button" class="add-to-cart-btn" 
                                        onclick="addToCart(<?php echo $product['id']; ?>, this.parentNode.querySelector('.qty-input').value)">
                                    <i class="fas fa-cart-plus"></i> Add
                                </button>
                                <button type="button" class="voice-add-btn" 
                                        onclick="voiceAddToCart('<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['id']; ?>)"
                                        title="Add by voice">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>No Products Available</h3>
                <p>We're currently stocking this section. Please check back later!</p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="vegetables.php" class="btn">Vegetables</a>
                    <a href="fruits.php" class="btn">Fruits</a>
                    <a href="dairy.php" class="btn">Dairy</a>
                    <a href="index.php" class="btn btn-secondary">Home</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Quantity selector functions
    function decrementQuantity(btn) {
        const input = btn.parentNode.querySelector('.qty-input');
        let value = parseFloat(input.value);
        const min = parseFloat(input.min);
        if (value > min) {
            value = Math.max(value - parseFloat(input.step), min);
            input.value = value.toFixed(1);
        }
    }

    function incrementQuantity(btn) {
        const input = btn.parentNode.querySelector('.qty-input');
        let value = parseFloat(input.value);
        const max = parseFloat(input.max);
        if (value < max) {
            value = Math.min(value + parseFloat(input.step), max);
            input.value = value.toFixed(1);
        } else {
            showNotification(`Maximum quantity is ${max}kg`, 'warning');
        }
    }

    // Sort products function
    function sortProducts(sortBy) {
        const grid = document.getElementById('productsGrid');
        if (!grid) return;
        
        const products = Array.from(grid.children);
        
        products.sort((a, b) => {
            switch(sortBy) {
                case 'price_low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price_high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'name':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'discount':
                    return parseFloat(b.dataset.discount) - parseFloat(a.dataset.discount);
                default:
                    return 0;
            }
        });
        
        grid.innerHTML = '';
        products.forEach(product => grid.appendChild(product));
        showNotification(`Sorted by ${sortBy.replace('_', ' ')}`, 'success');
    }

    // Voice add to cart
    function voiceAddToCart(productName, productId) {
        if (window.voiceAssistant) {
            window.voiceAssistant.showToast(`How much ${productName} do you want?`, 'listening');
            
            const recognition = new webkitSpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';
            
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
                        window.voiceAssistant.showToast('Maximum quantity is 5kg', 'error');
                        return;
                    }
                    
                    addToCart(productId, quantity);
                } else {
                    window.voiceAssistant.showToast('Please specify quantity (e.g., "1kg")', 'error');
                }
            };
            
            recognition.start();
        } else {
            alert('Voice assistant not ready');
        }
    }

    // Show notification
    function showNotification(message, type) {
        if (window.voiceAssistant) {
            window.voiceAssistant.showToast(message, type);
        }
    }
    </script>

    <script src="voice-command.js"></script>
    <script src="script.js"></script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 