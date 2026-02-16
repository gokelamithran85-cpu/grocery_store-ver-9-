<?php
session_start();
require_once 'db_connection.php';

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password!';
    } else {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                $success = 'Login successful! Redirecting...';
                echo '<script>
                        setTimeout(function(){
                            window.location.href = "index.php";
                        }, 1500);
                      </script>';
            } else {
                $error = 'Invalid password!';
            }
        } else {
            $error = 'No account found with this email!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Grocery Voice Bot</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-100px) rotate(-5deg);
            }
            to {
                opacity: 1;
                transform: translateX(0) rotate(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .login-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from { text-shadow: 0 0 5px #fff; }
            to { text-shadow: 0 0 10px #fff, 0 0 20px #ff4da6; }
        }
        
        .login-form {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 30px;
            position: relative;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: none;
            border-bottom: 2px solid #ddd;
            background: #f8f9fa;
            font-size: 16px;
            transition: all 0.3s;
            border-radius: 5px;
        }
        
        .form-input:focus {
            border-bottom-color: #667eea;
            background: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }
        
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
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
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(-1px);
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .alert-error {
            background: linear-gradient(135deg, #ffafbd, #ffc3a0);
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #a1ffce, #faffd1);
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .social-login {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        
        .social-login p {
            margin-bottom: 15px;
            color: #666;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
        }
        
        .google { background: #db4437; }
        .facebook { background: #4267B2; }
        .twitter { background: #1DA1F2; }
        
        @media (max-width: 500px) {
            .login-container {
                margin: 20px;
                border-radius: 15px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p style="opacity: 0.9; position: relative; z-index: 1;">Login to your voice shopping account</p>
        </div>
        
        <div class="login-form">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <div class="input-with-icon">
                        <span class="input-icon">📧</span>
                        <input type="email" class="form-input" name="email" 
                               placeholder="Email Address" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-with-icon">
                        <span class="input-icon">🔒</span>
                        <input type="password" class="form-input" name="password" 
                               placeholder="Password" required>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                </div>
                
                <button type="submit" class="login-btn pulse">
                    <span id="btnText">Login to Account</span>
                    <div id="loader" style="display: none;">Logging in...</div>
                </button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Create one now</a>
            </div>
            
            <div class="social-login">
                <p>Or login with</p>
                <div class="social-icons">
                    <a href="#" class="social-icon google">G</a>
                    <a href="#" class="social-icon facebook">f</a>
                    <a href="#" class="social-icon twitter">𝕏</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('.login-btn');
            const btnText = document.getElementById('btnText');
            const loader = document.getElementById('loader');
            
            btn.classList.remove('pulse');
            btnText.style.display = 'none';
            loader.style.display = 'block';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        });
        
        // Add typing effect to inputs
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>