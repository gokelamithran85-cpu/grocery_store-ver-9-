<?php
require_once 'includes/db_connection.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // FIXED: Proper admin query
    $query = "SELECT * FROM admin WHERE username = '$username' OR email = '$username'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        
        // FIXED: Check if password needs hashing
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'Admin not found';
    }
}

// FIXED: Create default admin if not exists
$check_admin = "SELECT * FROM admin WHERE username = 'admin'";
$admin_exists = $conn->query($check_admin);

if ($admin_exists->num_rows == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO admin (username, password, email) 
                    VALUES ('admin', '$default_password', 'admin@grocerystore.com')";
    $conn->query($insert_admin);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .admin-login-container {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }
        
        .admin-login-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .admin-login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #4CAF50, #FF9800, #2196F3);
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .admin-header i {
            font-size: 50px;
            color: #4CAF50;
            background: #E8F5E9;
            padding: 20px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        
        .admin-header h2 {
            color: #333;
            margin-bottom: 5px;
            font-size: 24px;
        }
        
        .admin-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            outline: none;
        }
        
        .btn-admin {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4CAF50, #388E3C);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(76, 175, 80, 0.3);
        }
        
        .btn-admin i {
            font-size: 18px;
        }
        
        .admin-footer {
            margin-top: 30px;
            text-align: center;
        }
        
        .admin-footer a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .admin-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #FFEBEE;
            color: #C62828;
            border: 1px solid #FFCDD2;
        }
        
        .alert-success {
            background: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #C8E6C9;
        }
        
        .info-box {
            margin-top: 20px;
            padding: 20px;
            background: #E3F2FD;
            border-radius: 10px;
            border-left: 4px solid #2196F3;
        }
        
        .info-box h4 {
            color: #0D47A1;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-box p {
            color: #1565C0;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .info-box strong {
            color: #0D47A1;
        }
        
        @media (max-width: 480px) {
            .admin-login-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-box">
            <div class="admin-header">
                <i class="fas fa-store-alt"></i>
                <h2>Admin Panel Login</h2>
                <p>Voice Grocery Store Management System</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['logged_out'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Successfully logged out
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username or Email" required autofocus>
                </div>
                
                <div class="form-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <button type="submit" class="btn-admin">
                    <i class="fas fa-sign-in-alt"></i>
                    Login to Dashboard
                </button>
                
                <div class="admin-footer">
                    <a href="index.php">
                        <i class="fas fa-arrow-left"></i> Back to Store
                    </a>
                </div>
            </form>
            
            <div class="info-box">
                <h4>
                    <i class="fas fa-info-circle"></i>
                    Default Admin Credentials
                </h4>
                <p><strong>Username:</strong> admin</p>
                <p><strong>Password:</strong> admin123</p>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    <i class="fas fa-shield-alt"></i> Please change password after first login
                </p>
            </div>
        </div>
    </div>
</body>
</html>