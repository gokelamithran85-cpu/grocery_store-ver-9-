// ===== VOICE COMMAND SYSTEM FOR GROCERY STORE =====
// Single, unified voice recognition system

class VoiceGroceryAssistant {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.supported = false;
        this.commandHistory = [];
        this.init();
    }

    init() {
        // Check for browser support
        if ('webkitSpeechRecognition' in window) {
            this.supported = true;
            this.setupRecognition();
            this.createVoiceButton();
            this.createVoiceToast();
            this.setupKeyboardShortcut();
            console.log('✅ Voice assistant initialized');
        } else {
            console.warn('❌ Voice recognition not supported in this browser');
            this.showFallbackMessage();
        }
    }

    setupRecognition() {
        this.recognition = new webkitSpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = false;
        this.recognition.lang = 'en-US';
        this.recognition.maxAlternatives = 1;

        this.recognition.onstart = () => {
            this.isListening = true;
            this.updateButtonState(true);
            this.showToast('🎤 Listening... Speak your command', 'listening');
        };

        this.recognition.onend = () => {
            this.isListening = false;
            this.updateButtonState(false);
            setTimeout(() => this.hideToast(), 2000);
        };

        this.recognition.onresult = (event) => {
            const command = event.results[0][0].transcript.toLowerCase().trim();
            this.commandHistory.push(command);
            console.log('🎯 Voice command:', command);
            this.showToast(`Command: "${command}"`, 'info');
            this.processCommand(command);
        };

        this.recognition.onerror = (event) => {
            console.error('Voice error:', event.error);
            this.isListening = false;
            this.updateButtonState(false);
            
            let message = 'Voice recognition failed';
            switch(event.error) {
                case 'not-allowed':
                    message = '❌ Microphone access denied. Please allow microphone access.';
                    break;
                case 'no-speech':
                    message = '🎤 No speech detected. Please try again.';
                    break;
                case 'network':
                    message = '🌐 Network error. Please check your connection.';
                    break;
                case 'aborted':
                    message = '⏹️ Voice recognition stopped.';
                    break;
                default:
                    message = `❌ Error: ${event.error}`;
            }
            this.showToast(message, 'error');
        };
    }

    createVoiceButton() {
        // Remove existing button if any
        const existingBtn = document.getElementById('voiceAssistantBtn');
        if (existingBtn) existingBtn.remove();

        const btn = document.createElement('button');
        btn.id = 'voiceAssistantBtn';
        btn.className = 'voice-assistant-button';
        btn.innerHTML = `
            <i class="fas fa-microphone"></i>
            <span class="voice-pulse"></span>
        `;
        btn.setAttribute('aria-label', 'Voice Assistant');
        btn.title = 'Click to speak (Ctrl+Shift+V)';
        
        btn.onclick = (e) => {
            e.preventDefault();
            this.toggleListening();
        };

        document.body.appendChild(btn);
        this.addButtonStyles();
    }

    addButtonStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .voice-assistant-button {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 70px;
                height: 70px;
                background: linear-gradient(135deg, #FF9800, #F57C00);
                border: none;
                border-radius: 50%;
                color: white;
                font-size: 32px;
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(255, 152, 0, 0.4);
                z-index: 999999;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                border: 3px solid white;
                animation: voicePulse 2s infinite;
            }

            .voice-assistant-button:hover {
                transform: scale(1.15) rotate(5deg);
                box-shadow: 0 6px 30px rgba(255, 152, 0, 0.6);
            }

            .voice-assistant-button.listening {
                background: linear-gradient(135deg, #4CAF50, #388E3C);
                animation: listeningPulse 1s infinite;
            }

            .voice-pulse {
                position: absolute;
                top: -5px;
                left: -5px;
                right: -5px;
                bottom: -5px;
                border-radius: 50%;
                background: rgba(255, 152, 0, 0.3);
                pointer-events: none;
            }

            .voice-assistant-button.listening .voice-pulse {
                background: rgba(76, 175, 80, 0.4);
            }

            @keyframes voicePulse {
                0% { box-shadow: 0 0 0 0 rgba(255, 152, 0, 0.7); }
                70% { box-shadow: 0 0 0 20px rgba(255, 152, 0, 0); }
                100% { box-shadow: 0 0 0 0 rgba(255, 152, 0, 0); }
            }

            @keyframes listeningPulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }

            .voice-toast {
                position: fixed;
                bottom: 120px;
                right: 30px;
                background: rgba(33, 33, 33, 0.95);
                backdrop-filter: blur(10px);
                color: white;
                padding: 16px 24px;
                border-radius: 50px;
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 999998;
                transform: translateY(100px);
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                border-left: 5px solid #FF9800;
                font-size: 16px;
                font-weight: 500;
                max-width: 350px;
                min-width: 280px;
            }

            .voice-toast.show {
                transform: translateY(0);
                opacity: 1;
            }

            .voice-toast i {
                font-size: 24px;
                animation: spin 2s infinite;
            }

            .voice-toast.listening {
                border-left-color: #4CAF50;
            }

            .voice-toast.success {
                border-left-color: #4CAF50;
            }

            .voice-toast.error {
                border-left-color: #f44336;
            }

            .voice-toast.info {
                border-left-color: #2196F3;
            }

            .voice-toast.warning {
                border-left-color: #FF9800;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .voice-help-modal {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 30px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                z-index: 1000000;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                border: 2px solid #4CAF50;
            }

            .voice-help-modal h3 {
                color: #2c3e50;
                margin-bottom: 20px;
                font-size: 1.5rem;
                display: flex;
                align-items: center;
                gap: 10px;
                border-bottom: 2px solid #eee;
                padding-bottom: 15px;
            }

            .voice-help-modal .close-btn {
                position: absolute;
                top: 15px;
                right: 20px;
                background: none;
                border: none;
                font-size: 28px;
                cursor: pointer;
                color: #666;
            }

            .voice-help-modal .close-btn:hover {
                color: #f44336;
            }

            .command-section {
                margin-bottom: 25px;
            }

            .command-section h4 {
                color: #4CAF50;
                margin-bottom: 10px;
                font-size: 1.1rem;
            }

            .command-list {
                list-style: none;
                padding: 0;
            }

            .command-list li {
                padding: 10px 15px;
                background: #f8f9fa;
                margin-bottom: 8px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 10px;
                border-left: 4px solid #FF9800;
            }

            .command-list li i {
                color: #4CAF50;
                width: 20px;
            }

            .shortcut-info {
                background: #e3f2fd;
                padding: 15px;
                border-radius: 10px;
                margin-top: 20px;
                display: flex;
                align-items: center;
                gap: 15px;
            }

            @media (max-width: 768px) {
                .voice-assistant-button {
                    bottom: 20px;
                    right: 20px;
                    width: 60px;
                    height: 60px;
                    font-size: 28px;
                }
                
                .voice-toast {
                    left: 20px;
                    right: 20px;
                    width: auto;
                    min-width: auto;
                    bottom: 100px;
                }
            }
        `;
        document.head.appendChild(style);
    }

    createVoiceToast() {
        // Remove existing toast
        const existingToast = document.getElementById('voiceToast');
        if (existingToast) existingToast.remove();

        const toast = document.createElement('div');
        toast.id = 'voiceToast';
        toast.className = 'voice-toast';
        toast.innerHTML = `
            <i class="fas fa-microphone"></i>
            <span id="voiceToastMessage">Voice assistant ready</span>
        `;
        document.body.appendChild(toast);
        this.toast = toast;
        this.toastMessage = document.getElementById('voiceToastMessage');
    }

    setupKeyboardShortcut() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+Shift+V to toggle voice
            if (e.ctrlKey && e.shiftKey && e.key === 'V') {
                e.preventDefault();
                this.toggleListening();
            }
            // Esc to stop listening
            if (e.key === 'Escape' && this.isListening) {
                this.stopListening();
            }
        });
    }

    toggleListening() {
        if (!this.supported) {
            this.showToast('❌ Voice recognition not supported in your browser', 'error');
            return;
        }

        if (this.isListening) {
            this.stopListening();
        } else {
            this.startListening();
        }
    }

    startListening() {
        try {
            this.recognition.start();
        } catch (e) {
            console.error('Failed to start:', e);
            this.showToast('❌ Failed to start voice recognition', 'error');
        }
    }

    stopListening() {
        try {
            this.recognition.stop();
            this.isListening = false;
            this.updateButtonState(false);
            this.showToast('⏹️ Voice stopped', 'info');
            setTimeout(() => this.hideToast(), 1500);
        } catch (e) {
            console.error('Failed to stop:', e);
        }
    }

    updateButtonState(isListening) {
        const btn = document.getElementById('voiceAssistantBtn');
        if (btn) {
            if (isListening) {
                btn.classList.add('listening');
                btn.innerHTML = '<i class="fas fa-microphone-alt"></i><span class="voice-pulse"></span>';
            } else {
                btn.classList.remove('listening');
                btn.innerHTML = '<i class="fas fa-microphone"></i><span class="voice-pulse"></span>';
            }
        }
    }

    showToast(message, type = 'info') {
        if (this.toast && this.toastMessage) {
            this.toast.className = `voice-toast ${type}`;
            this.toastMessage.textContent = message;
            this.toast.classList.add('show');
        }
    }

    hideToast() {
        if (this.toast) {
            this.toast.classList.remove('show');
        }
    }

    showFallbackMessage() {
        const msg = document.createElement('div');
        msg.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #f44336;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 999999;
            box-shadow: 0 4px 15px rgba(244,67,54,0.3);
        `;
        msg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Voice recognition not supported. Please use Chrome, Edge, or Safari.';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 5000);
    }

    processCommand(command) {
        console.log('Processing command:', command);
        
        // ===== NAVIGATION COMMANDS =====
        if (command.includes('home') || command.includes('main page')) {
            this.navigateTo('index.php');
            this.showToast('🏠 Going to home page', 'success');
            return;
        }
        
        if (command.includes('vegetable') || command.includes('veg')) {
            this.navigateTo('vegetables.php');
            this.showToast('🥕 Opening vegetables section', 'success');
            return;
        }
        
        if (command.includes('fruit')) {
            this.navigateTo('fruits.php');
            this.showToast('🍎 Opening fruits section', 'success');
            return;
        }
        
        if (command.includes('dairy') || command.includes('milk')) {
            this.navigateTo('dairy.php');
            this.showToast('🥛 Opening dairy section', 'success');
            return;
        }
        
        if (command.includes('bakery') || command.includes('bread')) {
            this.navigateTo('bakery.php');
            this.showToast('🥖 Opening bakery section', 'success');
            return;
        }
        
        if (command.includes('beverage') || command.includes('drink') || command.includes('coffee')) {
            this.navigateTo('beverages.php');
            this.showToast('☕ Opening beverages section', 'success');
            return;
        }
        
        if (command.includes('cart') || command.includes('shopping cart')) {
            this.navigateTo('cart.php');
            this.showToast('🛒 Opening cart', 'success');
            return;
        }
        
        if (command.includes('checkout') || command.includes('payment')) {
            this.navigateTo('checkout.php');
            this.showToast('💳 Going to checkout', 'success');
            return;
        }
        
        if (command.includes('offer') || command.includes('deal') || command.includes('discount')) {
            this.navigateTo('offers.php');
            this.showToast('🏷️ Opening offers page', 'success');
            return;
        }
        
        if (command.includes('profile') || command.includes('account')) {
            this.navigateTo('profile.php');
            this.showToast('👤 Opening profile', 'success');
            return;
        }
        
        if (command.includes('login')) {
            this.navigateTo('login.php');
            this.showToast('🔐 Going to login page', 'success');
            return;
        }
        
        if (command.includes('register') || command.includes('sign up')) {
            this.navigateTo('register.php');
            this.showToast('📝 Going to registration page', 'success');
            return;
        }
        
        if (command.includes('logout') || command.includes('sign out')) {
            if (confirm('Are you sure you want to logout?')) {
                this.navigateTo('logout.php');
                this.showToast('👋 Logging out', 'success');
            }
            return;
        }

        // ===== ADD TO CART COMMANDS =====
        const addMatch = command.match(/(?:add|buy|get)\s*(?:(\d+(?:\.\d+)?)\s*)?(kg|g|gram|kilo|kilogram|litre|l|ml|dozen|pack|piece)?\s*(.+)/i);
        
        if (addMatch) {
            this.handleAddToCart(addMatch);
            return;
        }

        // ===== SEARCH COMMANDS =====
        if (command.includes('search for') || command.includes('find')) {
            const searchTerm = command.replace(/search for|find/i, '').trim();
            this.performSearch(searchTerm);
            return;
        }

        // ===== HELP COMMAND =====
        if (command.includes('help') || command.includes('what can i say')) {
            this.showHelpModal();
            return;
        }

        // ===== CART MANAGEMENT =====
        if (command.includes('clear cart')) {
            if (confirm('Clear your entire cart?')) {
                this.navigateTo('cart.php?clear=1');
            }
            return;
        }

        if (command.includes('apply discount') || command.includes('coupon')) {
            const code = command.match(/code\s+(\w+)/i);
            if (code) {
                this.applyDiscountCode(code[1]);
            } else {
                this.showToast('Please say the coupon code', 'warning');
            }
            return;
        }

        // Command not recognized
        this.showToast('❌ Command not recognized. Say "Help" for available commands.', 'error');
    }

    handleAddToCart(match) {
        let quantity = match[1] ? parseFloat(match[1]) : 1;
        let unit = match[2] || 'kg';
        const productName = match[3].trim();
        
        // Convert units
        if (unit === 'g' || unit === 'gram') {
            quantity = quantity / 1000;
            unit = 'kg';
        } else if (unit === 'kilo' || unit === 'kilogram') {
            unit = 'kg';
        } else if (unit === 'l' || unit === 'ml') {
            if (unit === 'ml') {
                quantity = quantity / 1000;
            }
            unit = 'litre';
        }
        
        // Check quantity limit
        if (quantity > 5) {
            this.showToast('❌ Maximum quantity is 5kg', 'error');
            return;
        }
        
        this.showToast(`🔍 Searching for ${productName}...`, 'listening');
        
        // Try to find product on current page first
        const productFound = this.findAndAddProductOnPage(productName, quantity, unit);
        
        if (!productFound) {
            // Search via AJAX
            this.searchProductAPI(productName, quantity, unit);
        }
    }

    findAndAddProductOnPage(productName, quantity, unit) {
        const productCards = document.querySelectorAll('.product-card');
        let found = false;
        
        for (const card of productCards) {
            const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
            if (title.includes(productName.toLowerCase())) {
                found = true;
                
                // Set quantity
                const qtyInput = card.querySelector('.qty-input');
                if (qtyInput) {
                    qtyInput.value = quantity.toFixed(1);
                }
                
                // Click add to cart button
                const addBtn = card.querySelector('.add-to-cart-btn');
                if (addBtn) {
                    addBtn.click();
                    this.showToast(`✅ Added ${quantity.toFixed(1)}${unit} ${title} to cart`, 'success');
                    
                    // Animate button
                    addBtn.innerHTML = '<i class="fas fa-check"></i> Added!';
                    setTimeout(() => {
                        addBtn.innerHTML = '<i class="fas fa-cart-plus"></i> Add';
                    }, 2000);
                }
                break;
            }
        }
        
        return found;
    }

    searchProductAPI(productName, quantity, unit) {
        fetch('api/search_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: productName,
                quantity: quantity,
                unit: unit
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast(`✅ Added ${quantity.toFixed(1)}${unit} ${data.product.name} to cart`, 'success');
                this.updateCartCount(data.cart_count);
                
                // Reload cart page if we're on it
                if (window.location.pathname.includes('cart.php')) {
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                if (data.redirect) {
                    this.showToast('⚠️ Please login to add items', 'warning');
                    setTimeout(() => this.navigateTo(data.redirect), 2000);
                } else {
                    this.showToast(`❌ ${data.message}`, 'error');
                }
            }
        })
        .catch(error => {
            console.error('API Error:', error);
            this.showToast('❌ Error adding product. Please try again.', 'error');
        });
    }

    performSearch(searchTerm) {
        this.showToast(`🔍 Searching for: ${searchTerm}`, 'listening');
        
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = searchTerm;
            searchInput.dispatchEvent(new Event('input'));
            
            const searchForm = document.getElementById('searchForm');
            if (searchForm) {
                searchForm.submit();
            } else {
                this.navigateTo(`search.php?q=${encodeURIComponent(searchTerm)}`);
            }
        }
    }

    applyDiscountCode(code) {
        const discountInput = document.getElementById('discount_code');
        if (discountInput) {
            discountInput.value = code;
            const applyBtn = document.querySelector('.btn-apply');
            if (applyBtn) {
                applyBtn.click();
                this.showToast(`✅ Coupon ${code} applied!`, 'success');
            }
        } else {
            this.showToast('⚠️ Not on checkout page', 'warning');
        }
    }

    updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(el => {
            el.textContent = count;
            el.style.animation = 'pulse 0.5s ease';
            setTimeout(() => el.style.animation = '', 500);
        });
    }

    navigateTo(url) {
        window.location.href = url;
    }

    showHelpModal() {
        // Remove existing modal
        const existingModal = document.querySelector('.voice-help-modal');
        if (existingModal) existingModal.remove();

        const modal = document.createElement('div');
        modal.className = 'voice-help-modal';
        modal.innerHTML = `
            <button class="close-btn" onclick="this.closest('.voice-help-modal').remove()">&times;</button>
            <h3><i class="fas fa-microphone-alt" style="color: #4CAF50;"></i> Voice Commands Guide</h3>
            
            <div class="command-section">
                <h4>🛒 Shopping</h4>
                <ul class="command-list">
                    <li><i class="fas fa-microphone"></i> "Add 1kg carrots"</li>
                    <li><i class="fas fa-microphone"></i> "Add 500g apples"</li>
                    <li><i class="fas fa-microphone"></i> "Buy 2 litres milk"</li>
                    <li><i class="fas fa-microphone"></i> "Add 3 dozen eggs"</li>
                </ul>
            </div>
            
            <div class="command-section">
                <h4>🧭 Navigation</h4>
                <ul class="command-list">
                    <li><i class="fas fa-microphone"></i> "Go to vegetables"</li>
                    <li><i class="fas fa-microphone"></i> "Open fruits section"</li>
                    <li><i class="fas fa-microphone"></i> "Show dairy products"</li>
                    <li><i class="fas fa-microphone"></i> "Go to bakery"</li>
                    <li><i class="fas fa-microphone"></i> "Open beverages"</li>
                    <li><i class="fas fa-microphone"></i> "Go to cart"</li>
                    <li><i class="fas fa-microphone"></i> "Go to checkout"</li>
                    <li><i class="fas fa-microphone"></i> "Show offers"</li>
                    <li><i class="fas fa-microphone"></i> "Go to home"</li>
                </ul>
            </div>
            
            <div class="command-section">
                <h4>👤 Account</h4>
                <ul class="command-list">
                    <li><i class="fas fa-microphone"></i> "Go to profile"</li>
                    <li><i class="fas fa-microphone"></i> "My orders"</li>
                    <li><i class="fas fa-microphone"></i> "Login"</li>
                    <li><i class="fas fa-microphone"></i> "Logout"</li>
                    <li><i class="fas fa-microphone"></i> "Register"</li>
                </ul>
            </div>
            
            <div class="command-section">
                <h4>🎁 Offers & Discounts</h4>
                <ul class="command-list">
                    <li><i class="fas fa-microphone"></i> "Show deals"</li>
                    <li><i class="fas fa-microphone"></i> "Apply coupon SAVE20"</li>
                    <li><i class="fas fa-microphone"></i> "Clear cart"</li>
                </ul>
            </div>
            
            <div class="shortcut-info">
                <i class="fas fa-keyboard" style="font-size: 24px; color: #2196F3;"></i>
                <div>
                    <strong>Keyboard Shortcut:</strong> Press <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>V</kbd> to toggle microphone<br>
                    <small>Press <kbd>Esc</kbd> to stop listening</small>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close on outside click
        setTimeout(() => {
            window.addEventListener('click', function closeModal(e) {
                if (e.target === modal) {
                    modal.remove();
                    window.removeEventListener('click', closeModal);
                }
            });
        }, 100);
    }
}

// Initialize voice assistant when page loads
let voiceAssistant;

// Wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVoiceAssistant);
} else {
    initVoiceAssistant();
}

function initVoiceAssistant() {
    // Remove any existing instances
    if (window.voiceAssistant) {
        delete window.voiceAssistant;
    }
    
    // Create new instance
    voiceAssistant = new VoiceGroceryAssistant();
    window.voiceAssistant = voiceAssistant;
    
    console.log('🎤 Voice assistant ready!');
}

// Global functions for backward compatibility
function startVoiceRecognition() {
    if (window.voiceAssistant) {
        window.voiceAssistant.startListening();
    }
}

function stopVoiceRecognition() {
    if (window.voiceAssistant) {
        window.voiceAssistant.stopListening();
    }
}

function toggleVoiceRecognition() {
    if (window.voiceAssistant) {
        window.voiceAssistant.toggleListening();
    }
}

// Export for global use
window.startVoiceRecognition = startVoiceRecognition;
window.stopVoiceRecognition = stopVoiceRecognition;
window.toggleVoiceRecognition = toggleVoiceRecognition;