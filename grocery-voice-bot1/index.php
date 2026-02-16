<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Voice Bot - Home</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f8ff; }
        
        /* Navigation */
        nav {
            background: #4CAF50;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { color: white; font-size: 24px; font-weight: bold; }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 16px;
        }
        
        /* Voice Button */
        .voice-btn {
            background: white;
            color: #4CAF50;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Hero Section */
        .hero {
            text-align: center;
            padding: 50px 20px;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
        }
        .hero h1 { font-size: 36px; margin-bottom: 20px; }
        
        /* Products */
        .products {
            padding: 40px;
            text-align: center;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        .product-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Voice Section */
        .voice-section {
            padding: 40px;
            background: white;
            margin: 20px;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Grocery Voice Bot</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="cart.php">Cart (<span id="cart-count">0</span>)</a>
            <a href="admin.php">Admin</a>
        </div>
        <button id="voice-btn" class="voice-btn">🎤 Voice Command</button>
    </nav>

    <section class="hero">
        <h1>Welcome to AI Voice Grocery Store</h1>
        <p>Shop using voice commands - Just say what you want!</p>
        <p style="margin-top: 20px;">
            <a href="#voice-section" style="background: white; color: #4CAF50; padding: 10px 20px; border-radius: 25px; text-decoration: none;">
                Try Voice Shopping →
            </a>
        </p>
    </section>

    <section class="voice-section" id="voice-section">
        <h2>Voice Shopping Assistant</h2>
        <div id="voice-status" style="margin: 20px 0; color: #666;">
            Click the microphone button and speak
        </div>
        <div id="voice-feedback" style="min-height: 60px; padding: 20px; background: #f8f9fa; border-radius: 10px; margin: 20px 0;">
            Your commands will appear here
        </div>
        
        <h3>Try saying:</h3>
        <div style="display: flex; justify-content: center; gap: 15px; margin-top: 20px; flex-wrap: wrap;">
            <div style="background: #e8f5e8; padding: 10px 20px; border-radius: 20px;">"Add 1kg carrots"</div>
            <div style="background: #e8f5e8; padding: 10px 20px; border-radius: 20px;">"Add half kg tomatoes"</div>
            <div style="background: #e8f5e8; padding: 10px 20px; border-radius: 20px;">"Show cart"</div>
            <div style="background: #e8f5e8; padding: 10px 20px; border-radius: 20px;">"Checkout"</div>
        </div>
    </section>

    <section class="products">
        <h2>Available Products</h2>
        <div class="product-grid" id="product-list">
            <!-- Products will load here -->
            Loading products...
        </div>
    </section>

    <script>
        // Simple Voice Bot
        class SimpleVoiceBot {
            constructor() {
                this.init();
            }

            init() {
                const voiceBtn = document.getElementById('voice-btn');
                if (voiceBtn) {
                    voiceBtn.addEventListener('click', () => this.startListening());
                }
                this.loadProducts();
            }

            startListening() {
                if (!('webkitSpeechRecognition' in window)) {
                    alert('Voice recognition not supported. Use Chrome browser.');
                    return;
                }

                const recognition = new webkitSpeechRecognition();
                recognition.lang = 'en-US';
                recognition.continuous = false;
                recognition.interimResults = false;

                recognition.onstart = () => {
                    document.getElementById('voice-status').textContent = '🎤 Listening... Speak now!';
                    document.getElementById('voice-btn').innerHTML = '🔴 Stop Listening';
                };

                recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript.toLowerCase();
                    document.getElementById('voice-feedback').innerHTML = 
                        `<strong>You said:</strong> "${transcript}"`;
                    this.processCommand(transcript);
                };

                recognition.onerror = (event) => {
                    document.getElementById('voice-status').textContent = 'Error occurred. Try again.';
                };

                recognition.onend = () => {
                    document.getElementById('voice-status').textContent = 'Ready to listen';
                    document.getElementById('voice-btn').innerHTML = '🎤 Voice Command';
                };

                recognition.start();
            }

            async loadProducts() {
                try {
                    const response = await fetch('get_products.php');
                    const products = await response.json();
                    this.displayProducts(products);
                } catch (error) {
                    console.error('Error loading products:', error);
                }
            }

            displayProducts(products) {
                const container = document.getElementById('product-list');
                if (!container) return;

                let html = '';
                products.forEach(product => {
                    html += `
                        <div class="product-card">
                            <h3>${product.name}</h3>
                            <p>₹${product.price_per_250g} per 250g</p>
                            <button onclick="addToCart(${product.product_id}, 1)" 
                                    style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; margin-top: 10px; cursor: pointer;">
                                Add 1kg to Cart
                            </button>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }

            processCommand(transcript) {
                // Simple command processing
                if (transcript.includes('add')) {
                    if (transcript.includes('carrot')) {
                        this.addProduct(1, this.getQuantity(transcript));
                    } else if (transcript.includes('tomato')) {
                        this.addProduct(2, this.getQuantity(transcript));
                    } else if (transcript.includes('potato')) {
                        this.addProduct(3, this.getQuantity(transcript));
                    }
                } else if (transcript.includes('cart') || transcript.includes('show')) {
                    window.location.href = 'cart.php';
                } else if (transcript.includes('checkout')) {
                    window.location.href = 'checkout.php';
                }
            }

            getQuantity(transcript) {
                if (transcript.includes('half') || transcript.includes('500')) return 0.5;
                if (transcript.includes('250')) return 0.25;
                return 1; // Default 1kg
            }

            addProduct(productId, quantity) {
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
                        document.getElementById('cart-count').textContent = data.cart_count;
                        document.getElementById('voice-feedback').innerHTML += 
                            `<br>✅ Added ${quantity}kg to cart!`;
                    }
                });
            }
        }

        // Initialize when page loads
        window.addEventListener('DOMContentLoaded', () => {
            window.voiceBot = new SimpleVoiceBot();
        });

        // Global function for buttons
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
                    document.getElementById('cart-count').textContent = data.cart_count;
                    alert('Added to cart!');
                }
            });
        }
    </script>
</body>
</html>