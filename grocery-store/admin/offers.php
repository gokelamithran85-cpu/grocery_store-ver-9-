<?php
require_once 'includes/db_connection.php';

// Fetch all active offers
$offers_query = "SELECT * FROM offers 
                 WHERE is_active = 1 
                 AND valid_to >= CURDATE() 
                 ORDER BY valid_from DESC";
$offers_result = $conn->query($offers_query);

// Fetch products with special discounts
$discounted_products_query = "SELECT p.*, c.name as category_name, 
                             c.id as category_id,
                             o.title as offer_title,
                             o.discount_percentage as offer_discount
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             LEFT JOIN offers o ON o.discount_percentage = p.discount_percentage
                             WHERE p.discount_percentage > 0 
                             AND p.stock_quantity > 0
                             AND (p.discount_start_date <= CURDATE() OR p.discount_start_date IS NULL)
                             AND (p.discount_end_date >= CURDATE() OR p.discount_end_date IS NULL)
                             ORDER BY p.discount_percentage DESC 
                             LIMIT 20";
$discounted_products_result = $conn->query($discounted_products_query);

// Fetch flash deals (products with highest discount)
$flash_deals_query = "SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.discount_percentage >= 30 
                     AND p.stock_quantity > 0
                     AND (p.discount_end_date >= CURDATE() OR p.discount_end_date IS NULL)
                     ORDER BY p.discount_percentage DESC 
                     LIMIT 6";
$flash_deals_result = $conn->query($flash_deals_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers & Discounts - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Offers Page Specific Styles */
        .offers-hero {
            background: linear-gradient(135deg, #FF416C, #FF4B2B);
            color: white;
            padding: 60px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .offers-hero::before {
            content: '🎉';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .offers-hero::after {
            content: '🏷️';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .offers-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }

        .offers-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .offer-countdown {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .countdown-item {
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 10px;
            backdrop-filter: blur(5px);
        }

        .countdown-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .countdown-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .flash-deals-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .flash-deals-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            color: white;
        }

        .flash-deals-header h2 {
            font-size: 2rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .flash-deals-header h2 i {
            animation: flash 1.5s infinite;
        }

        @keyframes flash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .timer {
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .offer-card-large {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            position: relative;
        }

        .offer-card-large:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .offer-image {
            width: 40%;
            position: relative;
            overflow: hidden;
        }

        .offer-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .offer-card-large:hover .offer-image img {
            transform: scale(1.1);
        }

        .offer-content {
            width: 60%;
            padding: 30px;
            position: relative;
        }

        .offer-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: linear-gradient(135deg, #FF416C, #FF4B2B);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.3);
        }

        .offer-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .offer-description {
            color: var(--gray-color);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .offer-validity {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray-dark);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .offer-validity i {
            color: var(--primary-color);
        }

        .offer-code {
            background: var(--light-color);
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .code {
            font-family: monospace;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            letter-spacing: 2px;
        }

        .copy-btn {
            background: none;
            border: none;
            color: var(--gray-color);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .copy-btn:hover {
            color: var(--primary-color);
        }

        .offer-cta {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .offer-cta:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .discount-badge-large {
            position: absolute;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #FF416C, #FF4B2B);
            color: white;
            padding: 15px;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(255, 65, 108, 0.3);
            z-index: 10;
        }

        .discount-percent {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .discount-off {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .category-offers {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .category-offer-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .category-offer-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.3);
        }

        .category-offer-card i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .category-offer-card h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .category-offer-card .discount-text {
            font-size: 2rem;
            font-weight: 700;
            margin: 15px 0;
        }

        .category-offer-card .shop-now {
            color: white;
            text-decoration: none;
            display: inline-block;
            padding: 10px 25px;
            background: rgba(255,255,255,0.2);
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .category-offer-card .shop-now:hover {
            background: white;
            color: var(--primary-color);
        }

        .coupon-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-top: 60px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .coupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .coupon-card {
            background: linear-gradient(135deg, #f5f7fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            position: relative;
            overflow: hidden;
        }

        .coupon-card::before {
            content: '🎫';
            position: absolute;
            font-size: 100px;
            opacity: 0.1;
            right: 10px;
            bottom: 10px;
            transform: rotate(-15deg);
        }

        .coupon-code {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .coupon-desc {
            color: var(--gray-dark);
            margin-bottom: 15px;
        }

        .coupon-expiry {
            font-size: 0.9rem;
            color: var(--gray-color);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .copy-coupon {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .copy-coupon:hover {
            background: var(--primary-color);
            color: white;
        }

        .no-offers {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .no-offers i {
            font-size: 4rem;
            color: var(--gray-light);
            margin-bottom: 20px;
        }

        .no-offers h3 {
            font-size: 1.8rem;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .no-offers p {
            color: var(--gray-color);
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .offer-card-large {
                flex-direction: column;
            }

            .offer-image,
            .offer-content {
                width: 100%;
            }

            .offer-image {
                height: 200px;
            }

            .offers-hero h1 {
                font-size: 2rem;
            }

            .offer-countdown {
                flex-wrap: wrap;
            }

            .discount-badge-large {
                width: 60px;
                height: 60px;
                padding: 10px;
            }

            .discount-percent {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Offers Hero Section -->
    <section class="offers-hero">
        <div class="container">
            <h1><i class="fas fa-tags"></i> Special Offers & Deals</h1>
            <p>Save big on your favorite groceries with our exclusive discounts</p>
            
            <!-- Countdown Timer for Mega Sale -->
            <div class="offer-countdown" id="offerCountdown">
                <div class="countdown-item">
                    <span class="countdown-number" id="days">00</span>
                    <span class="countdown-label">Days</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="hours">00</span>
                    <span class="countdown-label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="minutes">00</span>
                    <span class="countdown-label">Minutes</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="seconds">00</span>
                    <span class="countdown-label">Seconds</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Flash Deals Section -->
    <?php if ($flash_deals_result && $flash_deals_result->num_rows > 0): ?>
    <section class="flash-deals-section">
        <div class="container">
            <div class="flash-deals-header">
                <h2>
                    <i class="fas fa-bolt"></i> 
                    Flash Deals
                </h2>
                <div class="timer">
                    <i class="fas fa-clock"></i>
                    <span id="flashTimer">Ends in 23:59:59</span>
                </div>
            </div>

            <div class="products-grid">
                <?php while($product = $flash_deals_result->fetch_assoc()): ?>
                <div class="product-card flash-deal">
                    <div class="discount-badge-large">
                        <span class="discount-percent"><?php echo $product['discount_percentage']; ?>%</span>
                        <span class="discount-off">OFF</span>
                    </div>
                    <div class="product-image">
                        <?php if($product['product_image']): ?>
                        <img src="uploads/products/<?php echo $product['product_image']; ?>" 
                             alt="<?php echo $product['name']; ?>">
                        <?php else: ?>
                        <i class="fas fa-box"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-details">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-category">
                            <i class="fas fa-tag"></i> <?php echo $product['category_name']; ?>
                        </p>
                        <div class="product-price">
                            <?php 
                            $final_price = $product['price'] * (1 - $product['discount_percentage']/100);
                            ?>
                            <span class="current-price">₹<?php echo number_format($final_price, 2); ?></span>
                            <span class="original-price">₹<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <div class="stock-warning">
                            <i class="fas fa-fire"></i> 
                            Only <?php echo $product['stock_quantity']; ?> left in stock
                        </div>
                        <div class="product-actions">
                            <div class="quantity-selector">
                                <button class="qty-btn minus" onclick="decrementQuantity(this)">-</button>
                                <input type="number" class="qty-input" value="1" min="0.1" max="<?php echo min(5, $product['stock_quantity']); ?>" step="0.1">
                                <button class="qty-btn plus" onclick="incrementQuantity(this)">+</button>
                                <span class="unit"><?php echo $product['unit']; ?></span>
                            </div>
                            <button class="add-to-cart-btn flash-btn" onclick="addToCart(<?php echo $product['id']; ?>, this.parentNode.querySelector('.qty-input').value)">
                                <i class="fas fa-bolt"></i> Grab Now
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Offers Section -->
    <section class="offers-section" style="background: white; padding: 60px 0;">
        <div class="container">
            <h2 class="section-title">
                <span><i class="fas fa-gift"></i> Exclusive Offers</span>
            </h2>

            <?php if ($offers_result && $offers_result->num_rows > 0): ?>
            <div class="offers-grid">
                <?php while($offer = $offers_result->fetch_assoc()): ?>
                <div class="offer-card-large" data-voice="offer_<?php echo $offer['id']; ?>">
                    <div class="offer-image">
                        <?php if($offer['offer_image']): ?>
                        <img src="uploads/offers/<?php echo $offer['offer_image']; ?>" 
                             alt="<?php echo $offer['title']; ?>">
                        <?php else: ?>
                        <div style="background: linear-gradient(135deg, #FF416C, #FF4B2B); height: 100%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-gift" style="font-size: 5rem; color: white;"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="offer-content">
                        <span class="offer-badge">-<?php echo $offer['discount_percentage']; ?>%</span>
                        <h3 class="offer-title"><?php echo $offer['title']; ?></h3>
                        <p class="offer-description"><?php echo $offer['description']; ?></p>
                        <div class="offer-validity">
                            <i class="fas fa-calendar-alt"></i>
                            Valid until: <?php echo date('d M Y', strtotime($offer['valid_to'])); ?>
                        </div>
                        <?php if(isset($offer['discount_code'])): ?>
                        <div class="offer-code">
                            <span class="code"><?php echo $offer['discount_code']; ?></span>
                            <button class="copy-btn" onclick="copyCouponCode('<?php echo $offer['discount_code']; ?>')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <?php endif; ?>
                        <a href="category.php?id=1" class="offer-cta">
                            <i class="fas fa-shopping-bag"></i> Shop Now
                        </a>
                        <span class="voice-indicator-product" style="margin-top: 15px;" 
                              onclick="voiceActivateOffer('<?php echo $offer['title']; ?>')">
                            <i class="fas fa-microphone"></i> Activate offer by voice
                        </span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="no-offers">
                <i class="fas fa-tag"></i>
                <h3>No Active Offers</h3>
                <p>Check back soon for exciting discounts and deals!</p>
                <a href="index.php" class="btn">
                    <i class="fas fa-home"></i> Continue Shopping
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Category Wise Offers -->
    <section style="padding: 60px 0; background: var(--light-color);">
        <div class="container">
            <h2 class="section-title">
                <span><i class="fas fa-th-large"></i> Category Offers</span>
            </h2>

            <div class="category-offers">
                <div class="category-offer-card" style="background: linear-gradient(135deg, #43A047, #2E7D32);">
                    <i class="fas fa-carrot"></i>
                    <h3>Vegetables</h3>
                    <div class="discount-text">20% OFF</div>
                    <p>On all fresh vegetables</p>
                    <a href="vegetables.php" class="shop-now">
                        <i class="fas fa-arrow-right"></i> Shop Now
                    </a>
                </div>

                <div class="category-offer-card" style="background: linear-gradient(135deg, #FF9800, #F57C00);">
                    <i class="fas fa-apple-alt"></i>
                    <h3>Fruits</h3>
                    <div class="discount-text">15% OFF</div>
                    <p>On premium fruits</p>
                    <a href="fruits.php" class="shop-now">
                        <i class="fas fa-arrow-right"></i> Shop Now
                    </a>
                </div>

                <div class="category-offer-card" style="background: linear-gradient(135deg, #2196F3, #1976D2);">
                    <i class="fas fa-milk"></i>
                    <h3>Dairy</h3>
                    <div class="discount-text">10% OFF</div>
                    <p>On dairy products</p>
                    <a href="dairy.php" class="shop-now">
                        <i class="fas fa-arrow-right"></i> Shop Now
                    </a>
                </div>

                <div class="category-offer-card" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);">
                    <i class="fas fa-bread-slice"></i>
                    <h3>Bakery</h3>
                    <div class="discount-text">25% OFF</div>
                    <p>Freshly baked goods</p>
                    <a href="bakery.php" class="shop-now">
                        <i class="fas fa-arrow-right"></i> Shop Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Discounted Products Grid -->
    <?php if ($discounted_products_result && $discounted_products_result->num_rows > 0): ?>
    <section style="padding: 60px 0; background: white;">
        <div class="container">
            <h2 class="section-title">
                <span><i class="fas fa-percent"></i> More Discounted Items</span>
            </h2>

            <div class="products-grid">
                <?php while($product = $discounted_products_result->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if($product['product_image']): ?>
                        <img src="uploads/products/<?php echo $product['product_image']; ?>" 
                             alt="<?php echo $product['name']; ?>">
                        <?php else: ?>
                        <i class="fas fa-box"></i>
                        <?php endif; ?>
                        <span class="product-discount"><?php echo $product['discount_percentage']; ?>% OFF</span>
                    </div>
                    <div class="product-details">
                        <h3><?php echo $product['name']; ?></h3>
                        <p class="product-category">
                            <i class="fas fa-tag"></i> <?php echo $product['category_name']; ?>
                        </p>
                        <div class="product-price">
                            <?php 
                            $final_price = $product['price'] * (1 - $product['discount_percentage']/100);
                            ?>
                            <span class="current-price">₹<?php echo number_format($final_price, 2); ?></span>
                            <span class="original-price">₹<?php echo number_format($product['price'], 2); ?></span>
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
                                <i class="fas fa-microphone"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Coupon Codes Section -->
    <section style="padding: 60px 0; background: var(--light-color);">
        <div class="container">
            <div class="coupon-section">
                <h2 style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-ticket-alt"></i> Coupon Codes
                </h2>
                <p style="text-align: center; color: var(--gray-color); margin-bottom: 40px;">
                    Apply these codes at checkout for extra savings
                </p>

                <div class="coupon-grid">
                    <div class="coupon-card">
                        <div class="coupon-code">WELCOME20</div>
                        <div class="coupon-desc">20% off on your first order</div>
                        <div class="coupon-expiry">
                            <i class="fas fa-clock"></i> Valid till: 31 Dec 2024
                        </div>
                        <button class="copy-coupon" onclick="copyCouponCode('WELCOME20')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>

                    <div class="coupon-card">
                        <div class="coupon-code">SAVE15</div>
                        <div class="coupon-desc">15% off on orders above ₹500</div>
                        <div class="coupon-expiry">
                            <i class="fas fa-clock"></i> Valid till: 31 Dec 2024
                        </div>
                        <button class="copy-coupon" onclick="copyCouponCode('SAVE15')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>

                    <div class="coupon-card">
                        <div class="coupon-code">FREESHIP</div>
                        <div class="coupon-desc">Free delivery on orders above ₹999</div>
                        <div class="coupon-expiry">
                            <i class="fas fa-clock"></i> Valid till: 31 Dec 2024
                        </div>
                        <button class="copy-coupon" onclick="copyCouponCode('FREESHIP')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>

                    <div class="coupon-card">
                        <div class="coupon-code">VEG25</div>
                        <div class="coupon-desc">25% off on vegetables</div>
                        <div class="coupon-expiry">
                            <i class="fas fa-clock"></i> Valid till: 31 Dec 2024
                        </div>
                        <button class="copy-coupon" onclick="copyCouponCode('VEG25')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Voice Commands Info -->
    <section style="padding: 40px 0; background: white;">
        <div class="container">
            <div class="voice-commands-info" style="margin: 0;">
                <h4><i class="fas fa-microphone-alt"></i> Voice Commands for Offers</h4>
                <ul>
                    <li>"Show me offers"</li>
                    <li>"Flash deals"</li>
                    <li>"Apply coupon WELCOME20"</li>
                    <li>"Add 1kg apples with discount"</li>
                    <li>"Best deals"</li>
                </ul>
            </div>
        </div>
    </section>

    <script>
    // Countdown Timer
    function startCountdown() {
        const countDownDate = new Date();
        countDownDate.setDate(countDownDate.getDate() + 7); // 7 days from now
        
        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = countDownDate - now;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').innerHTML = days.toString().padStart(2, '0');
            document.getElementById('hours').innerHTML = hours.toString().padStart(2, '0');
            document.getElementById('minutes').innerHTML = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').innerHTML = seconds.toString().padStart(2, '0');
            
            if (distance < 0) {
                clearInterval(x);
                document.getElementById('offerCountdown').innerHTML = '<h3>Offer Ended!</h3>';
            }
        }, 1000);
    }

    // Flash Timer
    function startFlashTimer() {
        let hours = 23;
        let minutes = 59;
        let seconds = 59;
        
        const timer = setInterval(function() {
            if (seconds > 0) {
                seconds--;
            } else {
                if (minutes > 0) {
                    minutes--;
                    seconds = 59;
                } else {
                    if (hours > 0) {
                        hours--;
                        minutes = 59;
                        seconds = 59;
                    } else {
                        clearInterval(timer);
                        document.getElementById('flashTimer').innerHTML = 'Deal Ended!';
                    }
                }
            }
            
            document.getElementById('flashTimer').innerHTML = 
                `Ends in ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
    }

    // Copy Coupon Code
    function copyCouponCode(code) {
        navigator.clipboard.writeText(code).then(function() {
            showNotification(`Coupon ${code} copied to clipboard!`, 'success');
        }, function() {
            showNotification('Failed to copy coupon code', 'error');
        });
    }

    // Voice Activate Offer
    function voiceActivateOffer(offerTitle) {
        if (!('webkitSpeechRecognition' in window)) {
            showNotification('Voice recognition not supported', 'error');
            return;
        }

        const toast = document.getElementById('voiceToast');
        const toastMessage = document.getElementById('voiceToastMessage');
        
        toastMessage.textContent = `Activating offer: ${offerTitle}`;
        toast.classList.add('show');
        
        // Simulate offer activation
        setTimeout(() => {
            showNotification(`Offer ${offerTitle} activated!`, 'success');
            toast.classList.remove('show');
        }, 2000);
    }

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

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        startCountdown();
        startFlashTimer();
    });
    </script>

    <script src="voice-command.js"></script>
    <script src="script.js"></script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>