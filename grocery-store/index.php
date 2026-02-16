<?php
// Remove session_start() from here - it's already in config.php
require_once 'includes/db_connection.php';

// Fetch categories for display
$categories_query = "SELECT * FROM categories ORDER BY display_order";
$categories_result = $conn->query($categories_query);

// Fetch active offers
$offers_query = "SELECT * FROM offers WHERE is_active = 1 
                 AND valid_to >= CURDATE() 
                 ORDER BY created_at DESC LIMIT 5";
$offers_result = $conn->query($offers_query);

// Fetch discounted products
$discounted_query = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.discount_percentage > 0 
                    AND (p.discount_start_date <= CURDATE() OR p.discount_start_date IS NULL)
                    AND (p.discount_end_date >= CURDATE() OR p.discount_end_date IS NULL)
                    ORDER BY p.discount_percentage DESC LIMIT 8";
$discounted_result = $conn->query($discounted_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voice Integrated Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section with Offers -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Fresh Groceries Delivered</h1>
                <p>Voice-enabled shopping made easy!</p>
                <div class="voice-demo">
                    <span class="demo-text">
                        <i class="fas fa-microphone"></i> 
                        Try saying: "Add 1kg carrots" or "Go to vegetables"
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Offers Carousel -->
    <?php if ($offers_result && $offers_result->num_rows > 0): ?>
    <section class="offers-section">
        <div class="container">
            <h2 class="section-title">Today's Offers</h2>
            <div class="offers-carousel">
                <?php while($offer = $offers_result->fetch_assoc()): ?>
                <div class="offer-card">
                    <?php if($offer['offer_image']): ?>
                    <img src="uploads/offers/<?php echo $offer['offer_image']; ?>" alt="<?php echo $offer['title']; ?>">
                    <?php endif; ?>
                    <div class="offer-details">
                        <h3><?php echo $offer['title']; ?></h3>
                        <p><?php echo $offer['description']; ?></p>
                        <span class="discount-badge"><?php echo $offer['discount_percentage']; ?>% OFF</span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categories/Sections -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="categories-grid">
                <?php 
                if ($categories_result) {
                    $categories_result->data_seek(0);
                    while($category = $categories_result->fetch_assoc()): 
                ?>
                <a href="category.php?id=<?php echo $category['id']; ?>" class="category-card" 
                   data-voice="view_category_<?php echo $category['id']; ?>">
                    <span class="voice-indicator-card" title="Voice command enabled">
                        <i class="fas fa-microphone"></i>
                    </span>
                    <div class="category-image">
                        <?php if($category['section_image']): ?>
                        <img src="uploads/categories/<?php echo $category['section_image']; ?>" 
                             alt="<?php echo $category['name']; ?>">
                        <?php else: 
                            $icons = [
                                1 => 'fas fa-carrot',
                                2 => 'fas fa-apple-alt',
                                3 => 'fas fa-milk',
                                4 => 'fas fa-bread-slice',
                                5 => 'fas fa-coffee'
                            ];
                            $icon = isset($icons[$category['id']]) ? $icons[$category['id']] : 'fas fa-shopping-basket';
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo $category['name']; ?></h3>
                    <p><?php echo $category['description']; ?></p>
                    <span class="voice-command-hint">Say "Go to <?php echo strtolower($category['name']); ?>"</span>
                </a>
                <?php 
                    endwhile;
                } 
                ?>
            </div>
        </div>
    </section>

    <!-- Discounted Products -->
    <?php if ($discounted_result && $discounted_result->num_rows > 0): ?>
    <section class="discounted-section">
        <div class="container">
            <h2 class="section-title">Special Discounts</h2>
            <div class="products-grid">
                <?php while($product = $discounted_result->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if($product['product_image']): ?>
                        <img src="uploads/products/<?php echo $product['product_image']; ?>" 
                             alt="<?php echo $product['name']; ?>">
                        <?php else: ?>
                        <i class="fas fa-box"></i>
                        <?php endif; ?>
                        <?php if($product['discount_percentage'] > 0): ?>
                        <span class="product-discount"><?php echo $product['discount_percentage']; ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-category">
                            <i class="fas fa-tag"></i> <?php echo $product['category_name']; ?>
                        </p>
                        <div class="product-price">
                            <?php 
                            $final_price = $product['price'];
                            if($product['discount_percentage'] > 0) {
                                $final_price = $product['price'] * (1 - $product['discount_percentage']/100);
                            }
                            ?>
                            <span class="current-price">₹<?php echo number_format($final_price, 2); ?></span>
                            <?php if($product['discount_percentage'] > 0): ?>
                            <span class="original-price">₹<?php echo number_format($product['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="product-stock">
                            <i class="fas fa-boxes"></i> 
                            Stock: <?php echo $product['stock_quantity']; ?> <?php echo $product['unit']; ?>
                        </p>
                        <div class="product-actions">
                            <div class="quantity-selector">
                                <button class="qty-btn minus" onclick="decrementQuantity(this)">-</button>
                                <input type="number" class="qty-input" value="1" min="0.1" max="<?php echo min(5, $product['stock_quantity']); ?>" step="0.1">
                                <button class="qty-btn plus" onclick="incrementQuantity(this)">+</button>
                                <span class="unit"><?php echo $product['unit']; ?></span>
                            </div>
                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, this.parentNode.querySelector('.qty-input').value)">
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                            <span class="voice-indicator-product" onclick="voiceAddToCart('<?php echo $product['name']; ?>', <?php echo $product['id']; ?>)">
                                <i class="fas fa-microphone"></i> Add by voice
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

   <!-- At the bottom of each page, before footer -->
<script src="voice-command.js"></script>
<script src="script.js"></script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>