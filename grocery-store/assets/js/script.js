// ===== MAIN JAVASCRIPT FOR VOICE GROCERY STORE =====

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeSmoothScroll();
    initializeAnimations();
    initializeQuantityControls();
    initializeVoiceRegistration();
    initializeDeliveryCalculator();
    initializeSearch();
    initializeCartUpdates();
});

// ===== SMOOTH SCROLL =====
function initializeSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ===== SCROLL ANIMATIONS =====
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe elements with animation class
    document.querySelectorAll('.category-card, .product-card, .offer-card, .section-title').forEach(el => {
        observer.observe(el);
    });

    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            const scrolled = window.pageYOffset;
            heroSection.style.backgroundPositionY = scrolled * 0.5 + 'px';
        }
    });
}

// ===== QUANTITY CONTROLS =====
function initializeQuantityControls() {
    document.querySelectorAll('.quantity-control').forEach(control => {
        const input = control.querySelector('.quantity-input');
        const decreaseBtn = control.querySelector('.decrease');
        const increaseBtn = control.querySelector('.increase');

        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                let value = parseFloat(input.value);
                const step = parseFloat(input.step) || 0.1;
                const min = parseFloat(input.min) || 0.1;
                
                if (value > min) {
                    value = Math.max(value - step, min);
                    input.value = value.toFixed(1);
                    triggerCartUpdate(input);
                }
            });
        }

        if (increaseBtn) {
            increaseBtn.addEventListener('click', () => {
                let value = parseFloat(input.value);
                const step = parseFloat(input.step) || 0.1;
                const max = parseFloat(input.max) || 5;
                
                if (value < max) {
                    value = Math.min(value + step, max);
                    input.value = value.toFixed(1);
                    triggerCartUpdate(input);
                } else {
                    showNotification(`Maximum quantity is ${max}kg`, 'warning');
                }
            });
        }

        if (input) {
            input.addEventListener('change', function() {
                let value = parseFloat(this.value);
                const min = parseFloat(this.min) || 0.1;
                const max = parseFloat(this.max) || 5;

                if (isNaN(value) || value < min) {
                    this.value = min.toFixed(1);
                } else if (value > max) {
                    this.value = max.toFixed(1);
                    showNotification(`Maximum quantity is ${max}kg`, 'warning');
                } else {
                    this.value = value.toFixed(1);
                }

                triggerCartUpdate(this);
            });
        }
    });
}

// ===== TRIGGER CART UPDATE =====
function triggerCartUpdate(element) {
    const cartId = element.dataset.cartId;
    const quantity = element.value;
    
    if (cartId) {
        updateCartItem(cartId, quantity);
    }
}

// ===== UPDATE CART ITEM =====
function updateCartItem(cartId, quantity) {
    fetch('api/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartId,
            quantity: parseFloat(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartTotal(data.total);
            updateCartCount(data.cart_count);
            showNotification('Cart updated successfully', 'success');
            
            // Update item total display
            const itemTotal = document.querySelector(`.cart-item-${cartId} .total-amount`);
            if (itemTotal) {
                itemTotal.textContent = `₹${data.item_total.toFixed(2)}`;
            }
        } else {
            showNotification(data.message || 'Error updating cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating cart', 'error');
    });
}

// ===== REMOVE FROM CART =====
function removeFromCart(cartId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('api/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_id: cartId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartItem = document.querySelector(`.cart-item-${cartId}`);
                if (cartItem) {
                    cartItem.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => {
                        cartItem.remove();
                        updateCartTotal(data.total);
                        updateCartCount(data.cart_count);
                        showNotification('Item removed from cart', 'success');
                        
                        if (data.cart_empty) {
                            location.reload();
                        }
                    }, 300);
                }
            } else {
                showNotification(data.message || 'Error removing item', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error removing item', 'error');
        });
    }
}

// ===== UPDATE CART TOTAL =====
function updateCartTotal(total) {
    const cartTotalElements = document.querySelectorAll('.cart-total');
    cartTotalElements.forEach(el => {
        el.textContent = `₹${parseFloat(total).toFixed(2)}`;
    });
}

// ===== UPDATE CART COUNT =====
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(el => {
        el.textContent = count;
        
        // Add bounce animation
        el.style.animation = 'none';
        el.offsetHeight; // Trigger reflow
        el.style.animation = 'pulse 0.5s ease';
    });
}

// ===== APPLY DISCOUNT =====
function applyDiscount() {
    const discountCode = document.getElementById('discountCode');
    if (!discountCode) return;
    
    const code = discountCode.value.trim();
    if (!code) {
        showNotification('Please enter a discount code', 'warning');
        return;
    }

    fetch('api/apply_discount.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            discount_code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Discount applied: ${data.discount_percentage}% off!`, 'success');
            updateCartTotal(data.total);
            
            // Update discount display
            const discountElement = document.querySelector('.discount-amount');
            if (discountElement) {
                discountElement.textContent = `-₹${data.discount_amount.toFixed(2)}`;
            }
        } else {
            showNotification(data.message || 'Invalid discount code', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error applying discount', 'error');
    });
}

// ===== NOTIFICATION SYSTEM =====
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => {
        notification.remove();
    });

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    
    notification.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ===== VOICE REGISTRATION =====
function initializeVoiceRegistration() {
    const voiceBtns = document.querySelectorAll('.voice-input-btn');
    voiceBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const field = this.closest('[data-voice]');
            if (field) {
                const fieldId = field.querySelector('input, textarea')?.id;
                if (fieldId) {
                    startVoiceInput(fieldId);
                }
            }
        });
    });
}

function startVoiceInput(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    // Check browser support
    if (!('webkitSpeechRecognition' in window)) {
        showNotification('Voice input is not supported in your browser', 'error');
        return;
    }

    const recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';

    // Update bot message
    const botMessage = document.getElementById('botMessage');
    if (botMessage) {
        botMessage.innerText = `🎤 Listening for ${fieldId.replace(/([A-Z])/g, ' $1').toLowerCase()}...`;
    }

    recognition.onstart = function() {
        field.style.borderColor = 'var(--secondary-color)';
        showNotification('Listening... Speak now', 'info');
    };

    recognition.onresult = function(event) {
        const spokenText = event.results[0][0].transcript;
        field.value = spokenText;
        
        // Trigger change event
        const changeEvent = new Event('change', { bubbles: true });
        field.dispatchEvent(changeEvent);
        
        field.style.borderColor = 'var(--success-color)';
        
        if (botMessage) {
            botMessage.innerText = `✓ ${fieldId.replace(/([A-Z])/g, ' $1')}: "${spokenText}"`;
        }
        
        showNotification('Voice input captured!', 'success');
    };

    recognition.onerror = function(event) {
        console.error('Voice error:', event.error);
        field.style.borderColor = 'var(--danger-color)';
        showNotification('Error: ' + event.error, 'error');
        
        if (botMessage) {
            botMessage.innerText = '❌ Voice input failed. Please try again.';
        }
    };

    recognition.onend = function() {
        setTimeout(() => {
            field.style.borderColor = '';
        }, 2000);
    };

    recognition.start();
}

function startRegistrationVoice() {
    const fields = ['username', 'mobile', 'email', 'address', 'city', 'pincode'];
    let currentField = 0;

    function processNextField() {
        if (currentField < fields.length) {
            const fieldId = fields[currentField];
            const field = document.getElementById(fieldId);
            
            if (field) {
                startVoiceInput(fieldId);
                currentField++;
                
                // Schedule next field
                setTimeout(processNextField, 5000);
            }
        }
    }

    processNextField();
}

// ===== DELIVERY CALCULATOR =====
function initializeDeliveryCalculator() {
    const calculateBtn = document.querySelector('[onclick="calculateDeliveryDistance()"]');
    if (calculateBtn) {
        calculateBtn.addEventListener('click', calculateDeliveryDistance);
    }
}

function calculateDeliveryDistance() {
    if (!navigator.geolocation) {
        showNotification('Geolocation is not supported by your browser', 'error');
        return;
    }

    showNotification('Detecting your location...', 'info');

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            
            // Store location (you can set this dynamically)
            const storeLat = 28.6139; // Example: New Delhi
            const storeLng = 77.2090;
            
            const distance = calculateHaversineDistance(storeLat, storeLng, userLat, userLng);
            
            const deliveryDistanceInput = document.getElementById('deliveryDistance');
            const deliveryMessage = document.getElementById('deliveryDistanceMessage');
            const homeDeliveryOption = document.querySelector('input[value="home_delivery"]');
            
            if (deliveryDistanceInput) {
                deliveryDistanceInput.value = distance.toFixed(2);
            }
            
            if (deliveryMessage) {
                if (distance <= 15) {
                    deliveryMessage.innerHTML = `
                        <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                        Home delivery available (${distance.toFixed(1)}km from store)
                    `;
                    if (homeDeliveryOption) {
                        homeDeliveryOption.disabled = false;
                    }
                } else {
                    deliveryMessage.innerHTML = `
                        <i class="fas fa-times-circle" style="color: var(--danger-color);"></i>
                        Home delivery not available - ${distance.toFixed(1)}km exceeds 15km limit
                    `;
                    if (homeDeliveryOption) {
                        homeDeliveryOption.disabled = true;
                        homeDeliveryOption.checked = false;
                        document.querySelector('input[value="pickup"]').checked = true;
                    }
                }
            }
            
            showNotification(`Distance from store: ${distance.toFixed(1)}km`, 'info');
        },
        function(error) {
            let message = 'Unable to detect location';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Location permission denied';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Location information unavailable';
                    break;
                case error.TIMEOUT:
                    message = 'Location request timeout';
                    break;
            }
            showNotification(message, 'error');
        }
    );
}

// ===== HAVERSINE DISTANCE CALCULATOR =====
function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// ===== SEARCH FUNCTIONALITY =====
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 3) {
            hideSearchResults();
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 500);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            hideSearchResults();
        }
    });
}

function performSearch(query) {
    fetch(`api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

function displaySearchResults(results) {
    const searchResults = document.getElementById('searchResults');
    if (!searchResults) return;

    if (results.length === 0) {
        searchResults.innerHTML = '<div class="search-no-results">No products found</div>';
    } else {
        let html = '';
        results.forEach(product => {
            html += `
                <a href="product.php?id=${product.id}" class="search-result-item">
                    <div class="search-result-image">
                        ${product.image ? 
                            `<img src="uploads/products/${product.image}" alt="${product.name}">` :
                            '<i class="fas fa-box"></i>'
                        }
                    </div>
                    <div class="search-result-info">
                        <h4>${product.name}</h4>
                        <p class="search-result-price">₹${product.price}/${product.unit}</p>
                    </div>
                </a>
            `;
        });
        searchResults.innerHTML = html;
    }

    searchResults.classList.add('show');
}

function hideSearchResults() {
    const searchResults = document.getElementById('searchResults');
    if (searchResults) {
        searchResults.classList.remove('show');
    }
}

// ===== CART UPDATES =====
function initializeCartUpdates() {
    // Update cart count on page load
    fetchCartCount();
}

function fetchCartCount() {
    if (!isUserLoggedIn()) return;

    fetch('api/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.count);
            }
        })
        .catch(error => {
            console.error('Error fetching cart count:', error);
        });
}

function isUserLoggedIn() {
    return document.querySelector('.profile-icon') !== null;
}

// ===== ADD TO CART =====
function addToCart(productId, quantity = 1) {
    if (!isUserLoggedIn()) {
        showNotification('Please login to add items to cart', 'warning');
        setTimeout(() => {
            window.location.href = 'login.php';
        }, 2000);
        return;
    }

    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: parseFloat(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            showNotification('Product added to cart!', 'success');
            
            // Animate the add to cart button
            const button = event?.target?.closest('.add-to-cart-btn');
            if (button) {
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                }, 2000);
            }
        } else {
            showNotification(data.message || 'Error adding product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding product to cart', 'error');
    });
}

// ===== WISHLIST =====
function toggleWishlist(productId) {
    if (!isUserLoggedIn()) {
        showNotification('Please login to add to wishlist', 'warning');
        return;
    }

    fetch('api/toggle_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = event.target.closest('i');
            if (icon) {
                if (data.added) {
                    icon.className = 'fas fa-heart';
                    showNotification('Added to wishlist', 'success');
                } else {
                    icon.className = 'far fa-heart';
                    showNotification('Removed from wishlist', 'info');
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating wishlist', 'error');
    });
}

// ===== LOAD MORE PRODUCTS =====
let loadingProducts = false;
let currentPage = 1;
let hasMoreProducts = true;

function loadMoreProducts() {
    if (loadingProducts || !hasMoreProducts) return;

    loadingProducts = true;
    currentPage++;

    fetch(`api/load_products.php?page=${currentPage}`)
        .then(response => response.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                appendProducts(data.products);
                hasMoreProducts = data.has_more;
                loadingProducts = false;
            } else {
                hasMoreProducts = false;
                loadingProducts = true;
                
                // Show end of products message
                const productsGrid = document.querySelector('.products-grid');
                if (productsGrid) {
                    const endMessage = document.createElement('div');
                    endMessage.className = 'end-message';
                    endMessage.innerHTML = '<p>No more products to load</p>';
                    productsGrid.parentNode.insertBefore(endMessage, productsGrid.nextSibling);
                }
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            loadingProducts = false;
        });
}

function appendProducts(products) {
    const productsGrid = document.querySelector('.products-grid');
    if (!productsGrid) return;

    products.forEach(product => {
        const productHTML = createProductCard(product);
        productsGrid.insertAdjacentHTML('beforeend', productHTML);
    });
}

function createProductCard(product) {
    const discountedPrice = product.discount_percentage > 0 
        ? (product.price * (1 - product.discount_percentage/100)).toFixed(2)
        : product.price;

    return `
        <div class="product-card">
            <div class="product-image">
                ${product.image ? 
                    `<img src="uploads/products/${product.image}" alt="${product.name}">` :
                    '<i class="fas fa-box"></i>'
                }
                ${product.discount_percentage > 0 ? 
                    `<span class="product-discount">${product.discount_percentage}% OFF</span>` :
                    ''
                }
            </div>
            <div class="product-details">
                <h3>${product.name}</h3>
                <p class="product-category">
                    <i class="fas fa-tag"></i> ${product.category_name}
                </p>
                <div class="product-price">
                    <span class="current-price">₹${discountedPrice}</span>
                    ${product.discount_percentage > 0 ? 
                        `<span class="original-price">₹${product.price}</span>` :
                        ''
                    }
                </div>
                <p class="product-stock">
                    <i class="fas fa-boxes"></i> 
                    Stock: ${product.stock_quantity} ${product.unit}
                </p>
                <button class="add-to-cart-btn" onclick="addToCart(${product.id}, '1')">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </div>
        </div>
    `;
}

// ===== INFINITE SCROLL =====
window.addEventListener('scroll', () => {
    if (!document.querySelector('.products-grid')) return;

    const scrollHeight = document.documentElement.scrollHeight;
    const scrollTop = document.documentElement.scrollTop;
    const clientHeight = document.documentElement.clientHeight;
    
    if (scrollTop + clientHeight >= scrollHeight - 200) {
        loadMoreProducts();
    }
});

// ===== PRICE FILTER =====
function initializePriceFilter() {
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');

    if (priceRange && priceValue) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = `₹${this.value}`;
        });

        priceRange.addEventListener('change', function() {
            filterProductsByPrice(this.value);
        });
    }
}

function filterProductsByPrice(maxPrice) {
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const priceElement = product.querySelector('.current-price');
        if (priceElement) {
            const price = parseFloat(priceElement.textContent.replace('₹', ''));
            if (price <= maxPrice) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        }
    });
}

// ===== SORT PRODUCTS =====
function sortProducts(sortBy) {
    const productsGrid = document.querySelector('.products-grid');
    if (!productsGrid) return;

    const products = Array.from(productsGrid.children);

    products.sort((a, b) => {
        switch(sortBy) {
            case 'price_low':
                return getProductPrice(a) - getProductPrice(b);
            case 'price_high':
                return getProductPrice(b) - getProductPrice(a);
            case 'name':
                return getProductName(a).localeCompare(getProductName(b));
            case 'discount':
                return getProductDiscount(b) - getProductDiscount(a);
            default:
                return 0;
        }
    });

    // Clear and re-append sorted products
    productsGrid.innerHTML = '';
    products.forEach(product => productsGrid.appendChild(product));
}

function getProductPrice(product) {
    const priceElement = product.querySelector('.current-price');
    return priceElement ? parseFloat(priceElement.textContent.replace('₹', '')) : 0;
}

function getProductName(product) {
    const nameElement = product.querySelector('h3');
    return nameElement ? nameElement.textContent : '';
}

function getProductDiscount(product) {
    const discountElement = product.querySelector('.product-discount');
    return discountElement ? parseFloat(discountElement.textContent) : 0;
}

// ===== TAB NAVIGATION =====
function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Deactivate all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }

    // Activate clicked button
    const clickedBtn = document.querySelector(`[onclick="switchTab('${tabId}')"]`);
    if (clickedBtn) {
        clickedBtn.classList.add('active');
    }
}

// ===== MOBILE MENU =====
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const menuBtn = document.querySelector('.mobile-menu-btn');

    if (navMenu && menuBtn) {
        navMenu.classList.toggle('show');
        menuBtn.classList.toggle('active');
    }
}

// ===== BACK TO TOP =====
function initializeBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ===== FORM VALIDATION =====
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger-color)';
            isValid = false;
            
            // Add error message
            const errorDiv = input.nextElementSibling?.classList.contains('error-message') 
                ? input.nextElementSibling 
                : document.createElement('div');
            
            errorDiv.className = 'error-message';
            errorDiv.textContent = 'This field is required';
            errorDiv.style.color = 'var(--danger-color)';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.style.marginTop = '0.25rem';
            
            if (!input.nextElementSibling?.classList.contains('error-message')) {
                input.parentNode.insertBefore(errorDiv, input.nextSibling);
            }
        } else {
            input.style.borderColor = '';
            const errorDiv = input.nextElementSibling?.classList.contains('error-message') 
                ? input.nextElementSibling 
                : null;
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    });

    // Password confirmation
    const password = form.querySelector('#password');
    const confirmPassword = form.querySelector('#confirm_password');
    
    if (password && confirmPassword && confirmPassword.value) {
        if (password.value !== confirmPassword.value) {
            confirmPassword.style.borderColor = 'var(--danger-color)';
            showNotification('Passwords do not match', 'error');
            isValid = false;
        }
    }

    // Email validation
    const email = form.querySelector('#email');
    if (email && email.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            email.style.borderColor = 'var(--danger-color)';
            showNotification('Please enter a valid email address', 'error');
            isValid = false;
        }
    }

    // Mobile validation
    const mobile = form.querySelector('#mobile');
    if (mobile && mobile.value) {
        const mobileRegex = /^[0-9]{10}$/;
        if (!mobileRegex.test(mobile.value)) {
            mobile.style.borderColor = 'var(--danger-color)';
            showNotification('Please enter a valid 10-digit mobile number', 'error');
            isValid = false;
        }
    }

    return isValid;
}

// ===== INITIALIZE ON PAGE LOAD =====
document.addEventListener('DOMContentLoaded', function() {
    initializeSmoothScroll();
    initializeAnimations();
    initializeQuantityControls();
    initializeVoiceRegistration();
    initializeDeliveryCalculator();
    initializeSearch();
    initializeCartUpdates();
    initializePriceFilter();
    initializeBackToTop();
    
    // Add animation class to elements
    document.body.classList.add('loaded');
    
    // Show welcome message for voice bot
    const botMessage = document.getElementById('botMessage');
    if (botMessage && botMessage.innerText === 'Hello! I\'m your voice assistant. Click the mic and speak your commands!') {
        setTimeout(() => {
            botMessage.style.animation = 'none';
            botMessage.offsetHeight;
            botMessage.style.animation = 'messagePop 0.3s ease';
        }, 3000);
    }
});

// ===== EXPORT FUNCTIONS FOR GLOBAL USE =====
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.applyDiscount = applyDiscount;
window.updateCartItem = updateCartItem;
window.toggleWishlist = toggleWishlist;
window.startVoiceInput = startVoiceInput;
window.startRegistrationVoice = startRegistrationVoice;
window.calculateDeliveryDistance = calculateDeliveryDistance;
window.sortProducts = sortProducts;
window.switchTab = switchTab;
window.toggleMobileMenu = toggleMobileMenu;
window.validateForm = validateForm;