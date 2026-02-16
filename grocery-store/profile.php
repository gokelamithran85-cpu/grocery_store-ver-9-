<?php
require_once 'includes/db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Fetch user orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC LIMIT 10";
$orders_result = $conn->query($orders_query);

// Update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
        
        // Check if email already exists for other users
        $check_email = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
        $email_result = $conn->query($check_email);
        
        if ($email_result->num_rows > 0) {
            $error = 'Email already registered with another account';
        } else {
            $update_query = "UPDATE users SET 
                            username = '$username',
                            mobile = '$mobile',
                            email = '$email',
                            address = '$address',
                            city = '$city',
                            pincode = '$pincode'
                            WHERE id = $user_id";
            
            if ($conn->query($update_query)) {
                $success = 'Profile updated successfully';
                $_SESSION['user_name'] = $username;
                $_SESSION['user_email'] = $email;
                
                // Refresh user data
                $user_result = $conn->query($user_query);
                $user = $user_result->fetch_assoc();
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_password = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
                    
                    if ($conn->query($update_password)) {
                        $success = 'Password changed successfully';
                    } else {
                        $error = 'Failed to change password';
                    }
                } else {
                    $error = 'Password must be at least 6 characters';
                }
            } else {
                $error = 'New passwords do not match';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: var(--primary-color);
            border: 4px solid rgba(255,255,255,0.3);
        }
        
        .profile-info h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .profile-info p {
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .profile-card h2 {
            font-size: 1.3rem;
            margin-bottom: 25px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid var(--light-color);
            padding-bottom: 15px;
        }
        
        .profile-card h2 i {
            color: var(--primary-color);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-light);
            border-radius: 8px;
            font-family: var(--font-primary);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            outline: none;
        }
        
        .form-group input[readonly] {
            background: var(--light-color);
            cursor: not-allowed;
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .orders-list {
            margin-top: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid var(--gray-light);
            transition: background 0.3s ease;
        }
        
        .order-item:hover {
            background: var(--light-color);
        }
        
        .order-details h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .order-meta {
            display: flex;
            gap: 15px;
            font-size: 0.9rem;
            color: var(--gray-color);
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-amount {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-color);
        }
        
        .no-orders i {
            font-size: 50px;
            margin-bottom: 15px;
            color: var(--gray-light);
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
        
        .password-requirements {
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-top: 5px;
            padding-left: 15px;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.3);
        }
        
        .profile-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Voice indicator for profile */
        .voice-profile-btn {
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

        .voice-profile-btn:hover {
            transform: scale(1.1);
            background: var(--secondary-dark);
        }

        .voice-profile-btn i {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['mobile']); ?></p>
                <div class="profile-actions">
                    <a href="orders.php" class="btn-save" style="background: var(--accent-color);">
                        <i class="fas fa-shopping-bag"></i> My Orders
                    </a>
                    <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
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

        <div class="profile-grid">
            <!-- Left Column - Profile Settings -->
            <div>
                <!-- Edit Profile Form -->
                <div class="profile-card">
                    <h2>
                        <i class="fas fa-user-edit"></i> Edit Profile
                        <button class="voice-profile-btn" onclick="speakField('profile-form')" title="Voice fill profile">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </h2>
                    
                    <form method="POST" action="" id="profileForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Full Name</label>
                                <div class="voice-input-group">
                                    <input type="text" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    <button type="button" class="voice-input-btn" onclick="speakField('username')">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="mobile">Mobile Number</label>
                                <div class="voice-input-group">
                                    <input type="tel" id="mobile" name="mobile" 
                                           value="<?php echo htmlspecialchars($user['mobile']); ?>" 
                                           pattern="[0-9]{10}" required>
                                    <button type="button" class="voice-input-btn" onclick="speakField('mobile')">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="voice-input-group">
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <button type="button" class="voice-input-btn" onclick="speakField('email')">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Delivery Address</label>
                            <div class="voice-input-group">
                                <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                <button type="button" class="voice-input-btn" onclick="speakField('address')">
                                    <i class="fas fa-microphone"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <div class="voice-input-group">
                                    <input type="text" id="city" name="city" 
                                           value="<?php echo htmlspecialchars($user['city']); ?>" required>
                                    <button type="button" class="voice-input-btn" onclick="speakField('city')">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="pincode">Pincode</label>
                                <div class="voice-input-group">
                                    <input type="text" id="pincode" name="pincode" 
                                           value="<?php echo htmlspecialchars($user['pincode']); ?>" 
                                           pattern="[0-9]{6}" required>
                                    <button type="button" class="voice-input-btn" onclick="speakField('pincode')">
                                        <i class="fas fa-microphone"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn-save">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
                
                <!-- Change Password Form -->
                <div class="profile-card">
                    <h2>
                        <i class="fas fa-lock"></i> Change Password
                    </h2>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="password-input-group">
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <div class="password-input-group">
                                    <input type="password" id="new_password" name="new_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <div class="password-input-group">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="password-requirements">
                            <i class="fas fa-info-circle"></i> Password must be at least 6 characters long
                        </div>
                        
                        <button type="submit" name="change_password" class="btn-save" style="margin-top: 20px;">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Right Column - Recent Orders -->
            <div>
                <div class="profile-card">
                    <h2>
                        <i class="fas fa-shopping-bag"></i> Recent Orders
                    </h2>
                    
                    <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                        <div class="orders-list">
                            <?php while($order = $orders_result->fetch_assoc()): ?>
                                <div class="order-item">
                                    <div class="order-details">
                                        <h4>Order #<?php echo $order['order_number']; ?></h4>
                                        <div class="order-meta">
                                            <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($order['order_date'])); ?></span>
                                            <span><i class="fas fa-rupee-sign"></i> <?php echo number_format($order['final_amount'], 2); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <?php if ($orders_result->num_rows >= 10): ?>
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="orders.php" class="btn-save" style="background: var(--accent-color);">
                                    View All Orders <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <i class="fas fa-box-open"></i>
                            <h3>No Orders Yet</h3>
                            <p>You haven't placed any orders yet.</p>
                            <a href="index.php" class="btn-save" style="margin-top: 20px; display: inline-block;">
                                <i class="fas fa-shopping-cart"></i> Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Account Statistics -->
                <div class="profile-card">
                    <h2>
                        <i class="fas fa-chart-line"></i> Account Statistics
                    </h2>
                    
                    <?php
                    // Get total orders count
                    $total_orders_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = $user_id";
                    $total_orders_result = $conn->query($total_orders_query);
                    $total_orders = $total_orders_result->fetch_assoc()['total'];
                    
                    // Get total spent
                    $total_spent_query = "SELECT SUM(final_amount) as total FROM orders WHERE user_id = $user_id AND order_status != 'cancelled'";
                    $total_spent_result = $conn->query($total_spent_query);
                    $total_spent = $total_spent_result->fetch_assoc()['total'] ?? 0;
                    
                    // Get member since
                    $member_since = date('F Y', strtotime($user['created_at']));
                    ?>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: center;">
                        <div style="padding: 20px; background: var(--light-color); border-radius: 10px;">
                            <i class="fas fa-shopping-bag" style="font-size: 30px; color: var(--primary-color); margin-bottom: 10px;"></i>
                            <h3 style="font-size: 28px; margin-bottom: 5px;"><?php echo $total_orders; ?></h3>
                            <p style="color: var(--gray-color);">Total Orders</p>
                        </div>
                        
                        <div style="padding: 20px; background: var(--light-color); border-radius: 10px;">
                            <i class="fas fa-rupee-sign" style="font-size: 30px; color: var(--primary-color); margin-bottom: 10px;"></i>
                            <h3 style="font-size: 28px; margin-bottom: 5px;">₹<?php echo number_format($total_spent, 0); ?></h3>
                            <p style="color: var(--gray-color);">Total Spent</p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: var(--light-color); border-radius: 10px; display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-calendar-alt" style="font-size: 24px; color: var(--primary-color);"></i>
                        <div>
                            <p style="font-weight: 600; margin-bottom: 3px;">Member Since</p>
                            <p style="color: var(--gray-color);"><?php echo $member_since; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Voice Commands Info -->
                <div class="profile-card" style="background: linear-gradient(135deg, #f5f7fa, #e9ecef);">
                    <h2>
                        <i class="fas fa-microphone-alt"></i> Voice Commands for Profile
                    </h2>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 10px 0; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-microphone" style="color: var(--secondary-color);"></i>
                            "Update my name"
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-microphone" style="color: var(--secondary-color);"></i>
                            "Change my address"
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-microphone" style="color: var(--secondary-color);"></i>
                            "Show my orders"
                        </li>
                        <li style="padding: 10px 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-microphone" style="color: var(--secondary-color);"></i>
                            "Logout"
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = event.currentTarget.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    // Voice input for profile fields
    function speakField(fieldId) {
        if (!('webkitSpeechRecognition' in window)) {
            showNotification('Voice recognition not supported', 'error');
            return;
        }

        const field = document.getElementById(fieldId);
        if (!field) return;

        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            field.style.borderColor = 'var(--secondary-color)';
            showNotification(`Listening for ${fieldId}...`, 'info');
        };

        recognition.onresult = function(event) {
            let spokenText = event.results[0][0].transcript;
            
            // Process text based on field type
            if (fieldId === 'email') {
                spokenText = spokenText.toLowerCase()
                    .replace(/\s+at\s+/gi, '@')
                    .replace(/\s+dot\s+/gi, '.')
                    .replace(/\s+/g, '');
            } else if (fieldId === 'mobile' || fieldId === 'pincode') {
                spokenText = spokenText.replace(/\D/g, '');
            } else if (fieldId === 'username' || fieldId === 'city') {
                spokenText = spokenText.split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            }
            
            field.value = spokenText;
            field.style.borderColor = 'var(--success-color)';
            showNotification('Voice input captured!', 'success');
            
            setTimeout(() => {
                field.style.borderColor = '';
            }, 2000);
        };

        recognition.onerror = function(event) {
            showNotification('Voice input failed: ' + event.error, 'error');
            field.style.borderColor = 'var(--danger-color)';
        };

        recognition.start();
    }

    // Confirm logout
    function confirmLogout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }
    </script>

    <script src="voice-command.js"></script>
    <script src="script.js"></script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>