<?php
// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$cart_count = 0;

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $cart_result = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id");
    $cart_count = $cart_result->fetch_assoc()['count'];
}
?>

<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <h1>Voice<span>Grocery</span></h1>
            </div>
            
            <nav class="nav-menu">
                <ul>
                    <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-home"></i> Home
                    </a></li>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropbtn">
                            <i class="fas fa-th-large"></i> Categories <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-content">
                            <a href="vegetables.php" data-voice-category="vegetables">
                                <i class="fas fa-carrot"></i> Vegetables
                                <span class="voice-indicator-small" title="Voice command: 'Go to vegetables'">
                                    <i class="fas fa-microphone"></i>
                                </span>
                            </a>
                            <a href="fruits.php" data-voice-category="fruits">
                                <i class="fas fa-apple-alt"></i> Fruits
                                <span class="voice-indicator-small" title="Voice command: 'Go to fruits'">
                                    <i class="fas fa-microphone"></i>
                                </span>
                            </a>
                            <a href="dairy.php" data-voice-category="dairy">
                                <i class="fas fa-milk"></i> Dairy
                                <span class="voice-indicator-small" title="Voice command: 'Go to dairy'">
                                    <i class="fas fa-microphone"></i>
                                </span>
                            </a>
                            <a href="bakery.php" data-voice-category="bakery">
                                <i class="fas fa-bread-slice"></i> Bakery
                                <span class="voice-indicator-small" title="Voice command: 'Go to bakery'">
                                    <i class="fas fa-microphone"></i>
                                </span>
                            </a>
                            <a href="beverages.php" data-voice-category="beverages">
                                <i class="fas fa-coffee"></i> Beverages
                                <span class="voice-indicator-small" title="Voice command: 'Go to beverages'">
                                    <i class="fas fa-microphone"></i>
                                </span>
                            </a>
                        </div>
                    </li>
                    <li><a href="offers.php">
                        <i class="fas fa-tags"></i> Offers
                    </a></li>
                    <li><a href="contact.php">
                        <i class="fas fa-envelope"></i> Contact
                    </a></li>
                </ul>
            </nav>
            
            <div class="header-actions">
                <!-- Global Voice Command Button -->
                <button class="global-voice-btn" onclick="toggleVoiceMenu()" id="globalVoiceBtn">
                    <i class="fas fa-microphone"></i>
                    <span class="voice-pulse"></span>
                </button>
                
                <?php if($is_logged_in): ?>
                    <a href="profile.php" class="profile-icon">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn btn-secondary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Voice Command Menu (Floating) -->
<div class="voice-command-menu" id="voiceCommandMenu">
    <div class="voice-menu-header">
        <i class="fas fa-microphone-alt"></i>
        <h3>Voice Commands</h3>
        <button class="close-menu" onclick="toggleVoiceMenu()">×</button>
    </div>
    <div class="voice-menu-content">
        <div class="voice-command-categories">
            <h4>Sections</h4>
            <ul>
                <li onclick="executeVoiceCommand('vegetables')">
                    <i class="fas fa-carrot"></i> Vegetables
                    <span class="command-hint">"Go to vegetables"</span>
                </li>
                <li onclick="executeVoiceCommand('fruits')">
                    <i class="fas fa-apple-alt"></i> Fruits
                    <span class="command-hint">"Go to fruits"</span>
                </li>
                <li onclick="executeVoiceCommand('dairy')">
                    <i class="fas fa-milk"></i> Dairy
                    <span class="command-hint">"Go to dairy"</span>
                </li>
                <li onclick="executeVoiceCommand('bakery')">
                    <i class="fas fa-bread-slice"></i> Bakery
                    <span class="command-hint">"Go to bakery"</span>
                </li>
                <li onclick="executeVoiceCommand('beverages')">
                    <i class="fas fa-coffee"></i> Beverages
                    <span class="command-hint">"Go to beverages"</span>
                </li>
            </ul>
        </div>
        <div class="voice-command-actions">
            <h4>Actions</h4>
            <ul>
                <li onclick="executeVoiceCommand('cart')">
                    <i class="fas fa-shopping-cart"></i> View Cart
                    <span class="command-hint">"Go to cart"</span>
                </li>
                <li onclick="executeVoiceCommand('checkout')">
                    <i class="fas fa-credit-card"></i> Checkout
                    <span class="command-hint">"Go to checkout"</span>
                </li>
                <li onclick="executeVoiceCommand('home')">
                    <i class="fas fa-home"></i> Home
                    <span class="command-hint">"Go to home"</span>
                </li>
                <?php if($is_logged_in): ?>
                <li onclick="executeVoiceCommand('profile')">
                    <i class="fas fa-user"></i> Profile
                    <span class="command-hint">"Go to profile"</span>
                </li>
                <li onclick="executeVoiceCommand('logout')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                    <span class="command-hint">"Logout"</span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="voice-menu-footer">
        <button class="start-listening-btn" onclick="startGlobalVoiceRecognition()">
            <i class="fas fa-microphone"></i> Start Listening
        </button>
        <div class="voice-status" id="globalVoiceStatus">
            <span class="status-indicator"></span>
            <span class="status-text">Ready</span>
        </div>
    </div>
</div>

<!-- Voice Command Toast Notification -->
<div id="voiceToast" class="voice-toast">
    <i class="fas fa-microphone"></i>
    <span id="voiceToastMessage">Listening...</span>
</div>

<style>
/* Global Voice Button */
.global-voice-btn {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, var(--secondary-color), var(--secondary-dark));
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 20px;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
    border: 2px solid white;
}

.global-voice-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
}

.voice-pulse {
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    border-radius: 50%;
    background: rgba(255, 152, 0, 0.3);
    animation: voiceRipple 2s infinite;
    pointer-events: none;
}

.listening .voice-pulse {
    animation: listeningPulse 1s infinite;
    background: rgba(76, 175, 80, 0.4);
}

@keyframes voiceRipple {
    0% {
        transform: scale(1);
        opacity: 0.5;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

@keyframes listeningPulse {
    0% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.3); opacity: 0.4; }
    100% { transform: scale(1); opacity: 0.8; }
}

/* Voice Indicator Small */
.voice-indicator-small {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: var(--primary-light);
    border-radius: 50%;
    margin-left: 8px;
    color: var(--primary-dark);
    font-size: 12px;
    transition: all 0.3s ease;
}

.dropdown-content a:hover .voice-indicator-small {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
}

/* Voice Command Menu */
.voice-command-menu {
    position: fixed;
    top: 80px;
    right: 20px;
    width: 350px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 9999;
    display: none;
    overflow: hidden;
    border: 2px solid var(--primary-light);
    animation: slideInRight 0.3s ease;
}

.voice-command-menu.show {
    display: block;
}

.voice-menu-header {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.voice-menu-header h3 {
    margin: 0;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close-menu {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.close-menu:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.voice-menu-content {
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.voice-command-categories,
.voice-command-actions {
    margin-bottom: 20px;
}

.voice-command-categories h4,
.voice-command-actions h4 {
    color: var(--dark-color);
    font-size: 1rem;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid var(--primary-light);
}

.voice-command-categories ul,
.voice-command-actions ul {
    list-style: none;
    padding: 0;
}

.voice-command-categories li,
.voice-command-actions li {
    padding: 10px 15px;
    margin-bottom: 5px;
    background: var(--light-color);
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.voice-command-categories li:hover,
.voice-command-actions li:hover {
    background: var(--primary-light);
    transform: translateX(5px);
}

.voice-command-categories li i,
.voice-command-actions li i {
    width: 20px;
    color: var(--primary-color);
}

.command-hint {
    margin-left: auto;
    font-size: 0.8rem;
    color: var(--gray-color);
    font-style: italic;
}

.voice-menu-footer {
    padding: 15px 20px;
    background: var(--light-color);
    border-top: 1px solid var(--gray-light);
}

.start-listening-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.start-listening-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.start-listening-btn i {
    animation: pulse 2s infinite;
}

.voice-status {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
    padding: 8px;
    background: white;
    border-radius: 20px;
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--gray-color);
}

.status-indicator.active {
    background: var(--success-color);
    animation: pulse 1s infinite;
}

.status-indicator.listening {
    background: var(--secondary-color);
    animation: listeningPulse 1s infinite;
}

/* Voice Toast */
.voice-toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: rgba(0,0,0,0.9);
    color: white;
    padding: 12px 25px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 10000;
    transition: transform 0.3s ease;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.voice-toast.show {
    transform: translateX(-50%) translateY(0);
}

.voice-toast i {
    color: var(--secondary-color);
    animation: pulse 1s infinite;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .voice-command-menu {
        width: 300px;
        right: 10px;
        top: 70px;
    }
    
    .command-hint {
        display: none;
    }
}
</style>

<script>
// Voice Command Menu Toggle
function toggleVoiceMenu() {
    const menu = document.getElementById('voiceCommandMenu');
    menu.classList.toggle('show');
    
    // Update button state
    const btn = document.getElementById('globalVoiceBtn');
    if (menu.classList.contains('show')) {
        btn.style.background = 'linear-gradient(135deg, var(--danger-color), var(--danger-dark))';
        btn.classList.add('active');
    } else {
        btn.style.background = 'linear-gradient(135deg, var(--secondary-color), var(--secondary-dark))';
        btn.classList.remove('active');
    }
}

// Execute Voice Command
function executeVoiceCommand(command) {
    console.log('Executing command:', command);
    
    // Show toast
    const toast = document.getElementById('voiceToast');
    const toastMessage = document.getElementById('voiceToastMessage');
    toastMessage.textContent = `Navigating to ${command}...`;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
    
    // Execute navigation
    switch(command) {
        case 'vegetables':
            window.location.href = 'vegetables.php';
            break;
        case 'fruits':
            window.location.href = 'fruits.php';
            break;
        case 'dairy':
            window.location.href = 'dairy.php';
            break;
        case 'bakery':
            window.location.href = 'bakery.php';
            break;
        case 'beverages':
            window.location.href = 'beverages.php';
            break;
        case 'cart':
            window.location.href = 'cart.php';
            break;
        case 'checkout':
            window.location.href = 'checkout.php';
            break;
        case 'home':
            window.location.href = 'index.php';
            break;
        case 'profile':
            window.location.href = 'profile.php';
            break;
        case 'logout':
            window.location.href = 'logout.php';
            break;
        default:
            toastMessage.textContent = 'Command not recognized';
    }
}

// Global Voice Recognition
function startGlobalVoiceRecognition() {
    if (!('webkitSpeechRecognition' in window)) {
        alert('Voice recognition is not supported in your browser. Please use Chrome, Edge, or Safari.');
        return;
    }
    
    const statusIndicator = document.querySelector('.status-indicator');
    const statusText = document.querySelector('.status-text');
    const toast = document.getElementById('voiceToast');
    const toastMessage = document.getElementById('voiceToastMessage');
    const globalBtn = document.getElementById('globalVoiceBtn');
    
    statusIndicator.classList.add('listening');
    statusText.textContent = 'Listening...';
    globalBtn.classList.add('listening');
    
    toastMessage.textContent = 'Listening for commands...';
    toast.classList.add('show');
    
    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    
    recognition.onresult = function(event) {
        const command = event.results[0][0].transcript.toLowerCase();
        console.log('Voice command:', command);
        
        toastMessage.textContent = `Command: "${command}"`;
        
        // Parse command
        if (command.includes('vegetable') || command.includes('veg')) {
            executeVoiceCommand('vegetables');
        }
        else if (command.includes('fruit')) {
            executeVoiceCommand('fruits');
        }
        else if (command.includes('dairy') || command.includes('milk')) {
            executeVoiceCommand('dairy');
        }
        else if (command.includes('bakery') || command.includes('bread')) {
            executeVoiceCommand('bakery');
        }
        else if (command.includes('beverage') || command.includes('drink') || command.includes('coffee')) {
            executeVoiceCommand('beverages');
        }
        else if (command.includes('cart')) {
            executeVoiceCommand('cart');
        }
        else if (command.includes('checkout') || command.includes('payment')) {
            executeVoiceCommand('checkout');
        }
        else if (command.includes('home') || command.includes('main')) {
            executeVoiceCommand('home');
        }
        else if (command.includes('profile') || command.includes('account')) {
            executeVoiceCommand('profile');
        }
        else if (command.includes('logout') || command.includes('sign out')) {
            executeVoiceCommand('logout');
        }
        else {
            toastMessage.textContent = 'Command not recognized';
        }
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2000);
    };
    
    recognition.onerror = function(event) {
        console.error('Voice error:', event.error);
        statusIndicator.classList.remove('listening');
        statusText.textContent = 'Error: ' + event.error;
        globalBtn.classList.remove('listening');
        
        toastMessage.textContent = 'Voice recognition failed';
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2000);
    };
    
    recognition.onend = function() {
        statusIndicator.classList.remove('listening');
        statusText.textContent = 'Ready';
        globalBtn.classList.remove('listening');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 1000);
    };
    
    recognition.start();
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('voiceCommandMenu');
    const btn = document.getElementById('globalVoiceBtn');
    
    if (!menu.contains(event.target) && !btn.contains(event.target) && menu.classList.contains('show')) {
        menu.classList.remove('show');
        btn.style.background = 'linear-gradient(135deg, var(--secondary-color), var(--secondary-dark))';
    }
});

// Keyboard shortcut (Ctrl+Shift+V)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'V') {
        e.preventDefault();
        toggleVoiceMenu();
    }
});

// Initialize voice indicators on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add voice indicators to category cards
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach((card, index) => {
        const voiceIcon = document.createElement('span');
        voiceIcon.className = 'voice-indicator-card';
        voiceIcon.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceIcon.title = 'Voice command enabled';
        card.appendChild(voiceIcon);
    });
    
    // Add voice indicators to product cards
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        const addToCartBtn = card.querySelector('.add-to-cart-btn');
        if (addToCartBtn) {
            const voiceIcon = document.createElement('span');
            voiceIcon.className = 'voice-indicator-product';
            voiceIcon.innerHTML = '<i class="fas fa-microphone"></i> Add by voice';
            voiceIcon.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const productName = card.querySelector('h3').textContent;
                startProductVoiceAdd(productName);
            };
            addToCartBtn.parentNode.insertBefore(voiceIcon, addToCartBtn.nextSibling);
        }
    });
});

// Voice add product
function startProductVoiceAdd(productName) {
    if (!('webkitSpeechRecognition' in window)) {
        alert('Voice recognition not supported');
        return;
    }
    
    const toast = document.getElementById('voiceToast');
    const toastMessage = document.getElementById('voiceToastMessage');
    
    toastMessage.textContent = `How much ${productName} do you want?`;
    toast.classList.add('show');
    
    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    
    recognition.onresult = function(event) {
        const command = event.results[0][0].transcript.toLowerCase();
        toastMessage.textContent = `Adding: ${command}`;
        
        // Parse quantity
        const match = command.match(/(\d+(?:\.\d+)?)\s*(kg|g|gram|kilo)/i);
        if (match) {
            let quantity = parseFloat(match[1]);
            const unit = match[2].toLowerCase();
            
            if (unit === 'g' || unit === 'gram') {
                quantity = quantity / 1000;
            }
            
            if (quantity > 5) {
                toastMessage.textContent = 'Maximum quantity is 5kg';
                setTimeout(() => toast.classList.remove('show'), 2000);
                return;
            }
            
            // Find and click add to cart button
            const addBtn = Array.from(document.querySelectorAll('.add-to-cart-btn')).find(btn => 
                btn.closest('.product-card')?.querySelector('h3')?.textContent === productName
            );
            
            if (addBtn) {
                const qtyInput = addBtn.closest('.product-actions')?.querySelector('.qty-input');
                if (qtyInput) {
                    qtyInput.value = quantity.toFixed(1);
                }
                addBtn.click();
                toastMessage.textContent = `Added ${quantity}kg ${productName}`;
            }
        } else {
            toastMessage.textContent = 'Please specify quantity (e.g., "1kg")';
        }
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    };
    
    recognition.start();
}<div class="header-actions">
    <!-- Global Voice Command Button -->
    <button class="global-voice-btn" onclick="toggleVoiceRecognition()" id="globalVoiceBtn">
        <i class="fas fa-microphone"></i>
        <span class="voice-pulse"></span>
    </button>
    
    <?php if($is_logged_in): ?>
        <div class="user-menu">
            <a href="profile.php" class="profile-icon">
                <i class="fas fa-user-circle"></i>
                <span class="user-name"><?php echo $_SESSION['user_name']; ?></span>
            </a>
            <div class="user-dropdown">
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a>
                <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        <a href="cart.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count"><?php echo $cart_count; ?></span>
        </a>
    <?php else: ?>
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn btn-secondary">Register</a>
    <?php endif; ?>
</div>

<style>
/* User Menu Styles */
.user-menu {
    position: relative;
    display: inline-block;
}

.profile-icon {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--dark-color);
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 30px;
    transition: all 0.3s ease;
}

.profile-icon:hover {
    background: var(--light-color);
    color: var(--primary-color);
}

.profile-icon i {
    font-size: 1.5rem;
}

.user-name {
    font-size: 0.95rem;
    font-weight: 500;
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 200px;
    border-radius: 10px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    padding: 10px 0;
    z-index: 1000;
    display: none;
    margin-top: 10px;
}

.user-menu:hover .user-dropdown {
    display: block;
    animation: fadeIn 0.3s ease;
}

.user-dropdown a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: var(--dark-color);
    text-decoration: none;
    transition: all 0.3s ease;
}

.user-dropdown a:hover {
    background: var(--light-color);
    color: var(--primary-color);
    padding-left: 25px;
}

.user-dropdown i {
    width: 20px;
    color: var(--gray-color);
}

.user-dropdown a:hover i {
    color: var(--primary-color);
}

.dropdown-divider {
    height: 1px;
    background: var(--gray-light);
    margin: 8px 0;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .user-name {
        display: none;
    }
    
    .user-dropdown {
        right: -50px;
    }
}
</style>
</script><?php
// DO NOT START SESSION HERE - It's already started in config.php

// Get database connection if not already available
if (!isset($conn)) {
    require_once dirname(__FILE__) . '/db_connection.php';
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$cart_count = 0;
$cart_total = 0;

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    
    // Get cart count and total items
    $cart_query = "SELECT COUNT(*) as item_count, COALESCE(SUM(quantity), 0) as total_items 
                   FROM cart 
                   WHERE user_id = $user_id";
    $cart_result = $conn->query($cart_query);
    
    if ($cart_result && $cart_result->num_rows > 0) {
        $cart_data = $cart_result->fetch_assoc();
        $cart_count = $cart_data['item_count'];
        $cart_total_items = $cart_data['total_items'];
    }
}
?>