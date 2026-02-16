<?php
session_start();
require_once 'db_connection.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } else {
        // Check if user exists
        $check_sql = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or Email already exists!';
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, email, password, phone, address) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $address);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Please login.';
                // Redirect after 2 seconds
                echo '<script>
                        setTimeout(function(){
                            window.location.href = "login.php";
                        }, 2000);
                      </script>';
            } else {
                $error = 'Registration failed! Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Grocery Voice Bot</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            animation: bounce 1s infinite alternate;
        }
        
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-5px); }
        }
        
        .register-header p {
            opacity: 0.9;
        }
        
        .register-form {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }
        
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            outline: none;
            transform: scale(1.02);
        }
        
        .password-strength {
            height: 4px;
            margin-top: 5px;
            border-radius: 2px;
            transition: width 0.3s;
        }
        
        .strength-weak { background: #ff4444; width: 25%; }
        .strength-medium { background: #ffa726; width: 50%; }
        .strength-strong { background: #4CAF50; width: 100%; }
        
        .btn {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .btn:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        
        .login-link a:hover {
            color: #2E7D32;
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .floating-label {
            position: absolute;
            top: 12px;
            left: 15px;
            color: #999;
            transition: all 0.3s;
            pointer-events: none;
        }
        
        input:focus + .floating-label,
        input:not(:placeholder-shown) + .floating-label {
            top: -20px;
            left: 10px;
            font-size: 12px;
            color: #4CAF50;
        }
        
        @media (max-width: 600px) {
            .register-container {
                margin: 20px;
                border-radius: 15px;
            }
            
            .register-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join our voice-powered grocery shopping experience</p>
        </div>
        
        <div class="register-form">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder=" " 
                           required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <label for="username" class="floating-label">Username</label>
                </div>
                
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder=" " 
                           required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <label for="email" class="floating-label">Email Address</label>
                </div>
                
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder=" " 
                           required onkeyup="checkPasswordStrength(this.value)">
                    <label for="password" class="floating-label">Password</label>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder=" " required onkeyup="checkPasswordMatch()">
                    <label for="confirm_password" class="floating-label">Confirm Password</label>
                    <div id="passwordMatch" style="font-size: 12px; margin-top: 5px;"></div>
                </div>
                
                <div class="form-group">
                    <input type="tel" id="phone" name="phone" placeholder=" " 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    <label for="phone" class="floating-label">Phone Number (Optional)</label>
                </div>
                
                <div class="form-group">
                    <textarea id="address" name="address" placeholder=" " rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    <label for="address" class="floating-label">Delivery Address</label>
                </div>
                
                <button type="submit" class="btn">
                    <span id="btnText">Create Account</span>
                    <div id="loader" style="display: none;">Creating account...</div>
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
    
    <script>
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;
            
            strengthBar.className = 'password-strength ';
            if (strength < 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength < 4) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchText.textContent = '';
                matchText.style.color = '';
            } else if (password === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.style.color = '#4CAF50';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.style.color = '#ff4444';
            }
        }
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('.btn');
            const btnText = document.getElementById('btnText');
            const loader = document.getElementById('loader');
            
            btnText.style.display = 'none';
            loader.style.display = 'block';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        });
        
        // Input validation animations
        const inputs = document.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('invalid', function(e) {
                e.preventDefault();
                this.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    this.style.animation = '';
                }, 500);
            });
        });
    </script>
</body>
</html>