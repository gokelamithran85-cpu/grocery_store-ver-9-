<?php
session_start();
require_once 'db_connection.php';

// Fetch all categories
$categories_sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL";
$categories_result = $conn->query($categories_sql);

// Fetch featured products
$featured_sql = "SELECT * FROM products WHERE current_stock > 0 ORDER BY RAND() LIMIT 8";
$featured_result = $conn->query($featured_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Grocery Voice Bot</title>
    <style>
        :root {
            --primary: #4CAF50;
            --primary-dark: #2E7D32;
            --secondary: #FF9800;
            --accent: #2196F3;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Amazon Ember', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        /* Navigation */
        .navbar {
            background: white;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-links a:hover {
            color: var(--primary);
            background: #f0f8f0;
        }
        
        .nav-links a.active {
            color: var(--primary);
            background: #e8f5e9;
            font-weight: bold;
        }
        
        /* Category Navigation */
        .category-nav {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 70px;
            z-index: 999;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .category-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding: 0 20px;
            scrollbar-width: thin;
        }
        
        .category-tab {
            padding: 10px 20px;
            background: #f8f9fa;
            border-radius: 25px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
            border: 2px solid transparent;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .category-tab:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .category-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary-dark);
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
        }
        
        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 20px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            animation: fadeInUp 1s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-content h1 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        /* Categories Grid */
        .categories-section {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .section-title {
            font-size: 28px;
            margin-bottom: 30px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .category-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .category-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 20px;
            color: white;
        }
        
        .category-card h3 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        
        .category-card p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        /* Products Grid */
        .products-section {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            margin-bottom: 40px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .product-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .product-card:hover {
            border-color: var(--primary);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .product-image {
            height: 150px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #ddd;
        }
        
        .product-title {
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 500;
            height: 40px;
            overflow: hidden;
        }
        
        .product-price {
            color: var(--primary-dark);
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .add-to-cart {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .add-to-cart:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }
        
        .wishlist-btn {
            width: 40px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .wishlist-btn:hover {
            background: #ffebee;
            border-color: #ff4444;
            color: #ff4444;
        }
        
        /* Voice Assistant Panel */
        .voice-panel {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .voice-btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .voice-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);
        }
        
        .voice-btn.listening {
            animation: pulse 1.5s infinite;
            background: #ff4444;
        }
        
        .voice-feedback {
            position: absolute;
            bottom: 70px;
            right: 0;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            min-width: 300px;
            display: none;
        }
        
        /* Footer */
        .footer {
            background: #232f3e;
            color: white;
            padding: 50px 20px;
            margin-top: 60px;
        }
        
        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
        }
        
        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #ff9900;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero-content h1 {
                font-size: 32px;
            }
            
            .categories-grid {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span>🛒</span>
                Grocery Voice Bot
            </div>
            
            <ul class="nav-links">
                <li><a href="index.php">🏠 Home</a></li>
                <li><a href="products.php" class="active">🛍️ All Products</a></li>
                <li><a href="categories.php">📁 Categories</a></li>
                <li><a href="cart.php">🛒 Cart <span id="cart-count">0</span></a></li>
                <li><a href="orders.php">📦 Orders</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">👤 Profile</a></li>
                    <li><a href="logout.php">🚪 Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">🔑 Login</a></li>
                    <li><a href="register.php">📝 Register</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="search-box">
                <input type="text" placeholder="Search products..." id="searchInput">
                <button onclick="searchProducts()">🔍</button>
            </div>
        </div>
    </nav>
    
    <!-- Category Navigation -->
    <div class="category-nav">
        <div class="category-container">
            <div class="category-tab active" data-category="all">🌟 All Products</div>
            <?php while($category = $categories_result->fetch_assoc()): ?>
                <div class="category-tab" data-category="<?php echo $category['category']; ?>">
                    <?php 
                        $icons = [
                            'vegetables' => '🥦',
                            'fruits' => '🍎',
                            'snacks' => '🍿',
                            'dairy' => '🥛',
                            'beverages' => '🥤',
                            'household' => '🏠',
                            'personal-care' => '🧴',
                            'frozen-foods' => '🧊'
                        ];
                        echo $icons[$category['category']] ?? '📦';
                    ?>
                    <?php echo ucfirst(str_replace('-', ' ', $category['category'])); ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1>Fresh Groceries Delivered to Your Door</h1>
            <p style="font-size: 18px; margin-bottom: 30px;">Shop from 1000+ products with voice commands</p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <button onclick="startVoiceShopping()" class="voice-btn" style="padding: 15px 30px; border-radius: 25px; font-size: 16px;">
                    🎤 Start Voice Shopping
                </button>
                <button onclick="scrollToCategories()" style="background: transparent; border: 2px solid white; color: white; padding: 15px 30px; border-radius: 25px; font-size: 16px; cursor: pointer;">
                    👇 Browse Categories
                </button>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories-section">
        <h2 class="section-title">🏪 Shop by Category</h2>
        
        <div class="categories-grid">
            <!-- Vegetables -->
            <div class="category-card" onclick="filterCategory('vegetables')">
                <div class="category-image" style="background: linear-gradient(135deg, #a8e6cf, #dcedc1);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🥦🥕🍅
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Fresh Vegetables</h3>
                    <p>Organic veggies delivered fresh daily</p>
                </div>
            </div>
            
            <!-- Fruits -->
            <div class="category-card" onclick="filterCategory('fruits')">
                <div class="category-image" style="background: linear-gradient(135deg, #ffd3b6, #ffaaa5);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🍎🍌🍊
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Seasonal Fruits</h3>
                    <p>Fresh fruits packed with vitamins</p>
                </div>
            </div>
            
            <!-- Snacks -->
            <div class="category-card" onclick="filterCategory('snacks')">
                <div class="category-image" style="background: linear-gradient(135deg, #ffaaa5, #ff8b94);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🍿🍫🍪
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Snacks & Treats</h3>
                    <p>Perfect for your cravings</p>
                </div>
            </div>
            
            <!-- Dairy -->
            <div class="category-card" onclick="filterCategory('dairy')">
                <div class="category-image" style="background: linear-gradient(135deg, #d4e4ff, #a8c6ff);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🥛🧀🥚
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Dairy Products</h3>
                    <p>Fresh milk, cheese & eggs</p>
                </div>
            </div>
            
            <!-- Beverages -->
            <div class="category-card" onclick="filterCategory('beverages')">
                <div class="category-image" style="background: linear-gradient(135deg, #a8e6cf, #6cc4a1);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🥤☕🍷
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Beverages</h3>
                    <p>Juices, coffee & soft drinks</p>
                </div>
            </div>
            
            <!-- Household -->
            <div class="category-card" onclick="filterCategory('household')">
                <div class="category-image" style="background: linear-gradient(135deg, #ffd3b6, #ffb385);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🏠🧹🕯️
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Household Essentials</h3>
                    <p>Cleaning & home care items</p>
                </div>
            </div>
            
            <!-- Personal Care -->
            <div class="category-card" onclick="filterCategory('personal-care')">
                <div class="category-image" style="background: linear-gradient(135deg, #e0bbff, #c77dff);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🧴🪒🧼
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Personal Care</h3>
                    <p>Health & beauty products</p>
                </div>
            </div>
            
            <!-- Frozen Foods -->
            <div class="category-card" onclick="filterCategory('frozen-foods')">
                <div class="category-image" style="background: linear-gradient(135deg, #a8e6ff, #6cb4ee);">
                    <div style="font-size: 80px; display: flex; align-items: center; justify-content: center; height: 100%;">
                        🧊🍦🥟
                    </div>
                </div>
                <div class="category-overlay">
                    <h3>Frozen Foods</h3>
                    <p>Ready-to-eat frozen items</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Products Section -->
    <section class="products-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 class="section-title">🛒 Featured Products</h2>
            <div style="display: flex; gap: 10px;">
                <select id="sortBy" onchange="sortProducts()" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                    <option value="newest">Newest First</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>
        </div>
        
        <div class="products-grid" id="productsContainer">
            <?php while($product = $featured_result->fetch_assoc()): ?>
                <div class="product-card" data-category="<?php echo $product['category']; ?>">
                    <?php if($product['current_stock'] < 5): ?>
                        <div class="product-badge">Low Stock</div>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <?php 
                            $productIcons = [
                                'carrot' => '🥕',
                                'tomato' => '🍅',
                                'potato' => '🥔',
                                'onion' => '🧅',
                                'apple' => '🍎',
                                'banana' => '🍌'
                            ];
                            echo $productIcons[strtolower($product['name'])] ?? '📦';
                        ?>
                    </div>
                    
                    <div class="product-title"><?php echo $product['name']; ?></div>
                    <div class="product-price">₹<?php echo number_format($product['price_per_250g'] * 4, 2); ?>/kg</div>
                    
                    <div style="font-size: 14px; color: #666; margin-bottom: 10px;">
                        <?php echo $product['description']; ?>
                    </div>
                    
                    <div style="font-size: 14px; color: #888; margin-bottom: 15px;">
                        Stock: <?php echo $product['current_stock']; ?> kg
                    </div>
                    
                    <div class="product-actions">
                        <button class="add-to-cart" onclick="addToCart(<?php echo $product['product_id']; ?>, 1)">
                            🛒 Add to Cart
                        </button>
                        <button class="wishlist-btn" onclick="addToWishlist(<?php echo $product['product_id']; ?>)">
                            ♡
                        </button>
                    </div>
                    
                    <div style="margin-top: 10px; display: flex; gap: 5px;">
                        <button onclick="quickAdd(<?php echo $product['product_id']; ?>, 0.25)" 
                                style="padding: 5px 10px; font-size: 12px; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;">
                            250g
                        </button>
                        <button onclick="quickAdd(<?php echo $product['product_id']; ?>, 0.5)" 
                                style="padding: 5px 10px; font-size: 12px; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;">
                            500g
                        </button>
                        <button onclick="quickAdd(<?php echo $product['product_id']; ?>, 1)" 
                                style="padding: 5px 10px; font-size: 12px; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;">
                            1kg
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    
    <!-- Voice Assistant Panel -->
    <div class="voice-panel">
        <button class="voice-btn" id="voiceBtn" onclick="toggleVoiceAssistant()">
            🎤
        </button>
        <div class="voice-feedback" id="voiceFeedback">
            <strong>Voice Assistant</strong>
            <div id="voiceStatus">Click mic to start</div>
            <div id="voiceCommand"></div>
            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                <div>Try saying:</div>
                <div>"Add 1kg carrots"</div>
                <div>"Show vegetables"</div>
                <div>"Go to checkout"</div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Grocery Voice Bot</h3>
                <p>Your voice-powered shopping assistant for fresh groceries delivered to your door.</p>
            </div>
            
            <div class="footer-section">
                <h3>Shop</h3>
                <ul class="footer-links">
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="deals.php">Today's Deals</a></li>
                    <li><a href="new-arrivals.php">New Arrivals</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Help & Settings</h3>
                <ul class="footer-links">
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <ul class="footer-links">
                    <li><a href="https://facebook.com">Facebook</a></li>
                    <li><a href="https://twitter.com">Twitter</a></li>
                    <li><a href="https://instagram.com">Instagram</a></li>
                    <li><a href="https://youtube.com">YouTube</a></li>
                </ul>
            </div>
        </div>
    </footer>
    
    <script>
        // Category Filtering
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.dataset.category;
                filterProducts(category);
            });
        });
        
        function filterCategory(category) {
            document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
            document.querySelector(`[data-category="${category}"]`).classList.add('active');
            filterProducts(category);
        }
        
        function filterProducts(category) {
            const products = document.querySelectorAll('.product-card');
            products.forEach(product => {
                if (category === 'all' || product.dataset.category === category) {
                    product.style.display = 'block';
                    product.style.animation = 'fadeInUp 0.5s ease-out';
                } else {
                    product.style.display = 'none';
                }
            });
        }
        
        function scrollToCategories() {
            document.querySelector('.categories-section').scrollIntoView({ behavior: 'smooth' });
        }
        
        // Cart Functions
        function addToCart(productId, quantity) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.cart_count);
                    showNotification('✅ Product added to cart!', 'success');
                }
            });
        }
        
        function quickAdd(productId, quantity) {
            addToCart(productId, quantity);
        }
        
        function updateCartCount(count) {
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = count;
                // Animation
                cartCount.style.transform = 'scale(1.5)';
                setTimeout(() => {
                    cartCount.style.transform = 'scale(1)';
                }, 300);
            }
        }
        
        // Voice Assistant
        let recognition = null;
        let isListening = false;
        
        function toggleVoiceAssistant() {
            const voiceBtn = document.getElementById('voiceBtn');
            const voiceFeedback = document.getElementById('voiceFeedback');
            
            if (!isListening) {
                startVoiceRecognition();
                voiceBtn.classList.add('listening');
                voiceFeedback.style.display = 'block';
            } else {
                stopVoiceRecognition();
                voiceBtn.classList.remove('listening');
                voiceFeedback.style.display = 'none';
            }
        }
        
        function startVoiceRecognition() {
            if (!('webkitSpeechRecognition' in window)) {
                alert('Voice recognition not supported in this browser. Try Chrome.');
                return;
            }
            
            recognition = new webkitSpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.lang = 'en-US';
            
            recognition.onstart = function() {
                isListening = true;
                document.getElementById('voiceStatus').textContent = '🎤 Listening...';
            };
            
            recognition.onresult = function(event) {
                let transcript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    transcript += event.results[i][0].transcript;
                }
                document.getElementById('voiceCommand').textContent = transcript;
                processVoiceCommand(transcript);
            };
            
            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
            };
            
            recognition.onend = function() {
                isListening = false;
                document.getElementById('voiceStatus').textContent = 'Click mic to start';
                document.getElementById('voiceBtn').classList.remove('listening');
            };
            
            recognition.start();
        }
        
        function stopVoiceRecognition() {
            if (recognition) {
                recognition.stop();
            }
        }
        
        function processVoiceCommand(transcript) {
            transcript = transcript.toLowerCase();
            
            // Product addition commands
            if (transcript.includes('add') || transcript.includes('buy')) {
                const products = {
                    'carrot': 1, 'carrots': 1,
                    'tomato': 2, 'tomatoes': 2,
                    'potato': 3, 'potatoes': 3,
                    'onion': 4, 'onions': 4,
                    'apple': 5, 'apples': 5,
                    'banana': 6, 'bananas': 6
                };
                
                let quantity = 1; // Default 1kg
                if (transcript.includes('250') || transcript.includes('quarter')) quantity = 0.25;
                if (transcript.includes('500') || transcript.includes('half')) quantity = 0.5;
                
                for (const [keyword, productId] of Object.entries(products)) {
                    if (transcript.includes(keyword)) {
                        addToCart(productId, quantity);
                        showNotification(`✅ Added ${quantity}kg ${keyword}`, 'success');
                        break;
                    }
                }
            }
            // Navigation commands
            else if (transcript.includes('cart') || transcript.includes('basket')) {
                window.location.href = 'cart.php';
            }
            else if (transcript.includes('checkout')) {
                window.location.href = 'checkout.php';
            }
            else if (transcript.includes('home')) {
                window.location.href = 'index.php';
            }
            // Category commands
            else if (transcript.includes('vegetable') || transcript.includes('veggie')) {
                filterCategory('vegetables');
                showNotification('Showing vegetables', 'info');
            }
            else if (transcript.includes('fruit')) {
                filterCategory('fruits');
                showNotification('Showing fruits', 'info');
            }
            else if (transcript.includes('snack')) {
                filterCategory('snacks');
                showNotification('Showing snacks', 'info');
            }
        }
        
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === 'success' ? '#4CAF50' : '#2196F3'};
                color: white;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideInRight 0.3s ease-out;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Initialize cart count
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                updateCartCount(data.count);
            });
    </script>
    
    <style>
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .search-box {
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 25px;
            width: 300px;
            font-size: 16px;
        }
        
        .search-box button {
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
        }
    </style>
</body>
</html>