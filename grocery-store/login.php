<?php
session_start();
require_once 'includes/db_connection.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            
            // Redirect to previous page or home
            $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php';
            unset($_SESSION['redirect_url']);
            header("Location: $redirect");
            exit();
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'Email not registered';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="form-container">
        <h2 class="form-title">Welcome Back!</h2>
        
        <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm" onsubmit="return validateForm('loginForm')">
            <div class="voice-login-assistant">
                <p><i class="fas fa-microphone"></i> Voice Login Available</p>
                <button type="button" class="voice-assistant-btn" onclick="startVoiceLogin()">
                    <i class="fas fa-microphone"></i> Login with Voice
                </button>
            </div>

            <div class="form-group" data-voice="email">
                <label for="email">Email Address</label>
                <div class="voice-input-group">
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <button type="button" class="voice-input-btn" onclick="startVoiceInput('email')">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
            </div>

            <div class="form-group" data-voice="password">
                <label for="password">Password</label>
                <div class="password-input-group">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group remember-forgot">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember"> Remember me
                    <span class="checkmark"></span>
                </label>
                <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-block">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </form>

        <!-- Voice Command Instructions -->
        <div class="voice-commands-info">
            <h4><i class="fas fa-microphone-alt"></i> Voice Commands</h4>
            <ul>
                <li>"Login with email [your email]"</li>
                <li>"Password [your password]"</li>
                <li>"Login now"</li>
                <li>"Go to register"</li>
            </ul>
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

    // Voice login function
    function startVoiceLogin() {
        if (!('webkitSpeechRecognition' in window)) {
            showNotification('Voice recognition not supported in your browser', 'error');
            return;
        }

        const botMessage = document.getElementById('botMessage');
        if (botMessage) {
            botMessage.innerText = '🎤 Voice login activated. Please speak your email.';
        }

        // Start with email
        setTimeout(() => {
            speakAndFill('email', 'Please speak your email address');
        }, 1000);
    }

    // Enhanced voice input function
    function speakAndFill(fieldId, prompt) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        const botMessage = document.getElementById('botMessage');
        if (botMessage) botMessage.innerText = `🎤 ${prompt}`;

        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            field.style.borderColor = 'var(--secondary-color)';
            showNotification('Listening...', 'info');
        };

        recognition.onresult = function(event) {
            let spokenText = event.results[0][0].transcript.toLowerCase();
            
            // Clean up the text
            spokenText = spokenText.replace(/^my email is |^email |^at |^dot /gi, '');
            spokenText = spokenText.replace(/\s+at\s+/gi, '@');
            spokenText = spokenText.replace(/\s+dot\s+/gi, '.');
            spokenText = spokenText.replace(/\s+/g, '');
            
            field.value = spokenText;
            field.style.borderColor = 'var(--success-color)';
            
            if (botMessage) {
                botMessage.innerText = `✓ Email: ${spokenText}`;
            }
            
            // If email is filled, ask for password
            if (fieldId === 'email') {
                setTimeout(() => {
                    speakAndFill('password', 'Please speak your password');
                }, 1500);
            }
            
            showNotification('Voice input captured!', 'success');
        };

        recognition.onerror = function(event) {
            console.error('Voice error:', event.error);
            showNotification('Voice input failed. Please try again.', 'error');
            if (botMessage) {
                botMessage.innerText = '❌ Voice input failed. Please type manually.';
            }
        };

        recognition.start();
    }

    // Add keyboard shortcut for voice (Ctrl+Shift+L)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'L') {
            e.preventDefault();
            startVoiceLogin();
        }
    });
    </script>

    <style>
    /* Additional styles for login page */
    .voice-login-assistant {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        padding: 1.5rem;
        border-radius: var(--border-radius-lg);
        margin-bottom: 2rem;
        text-align: center;
        border: 2px dashed var(--accent-color);
    }

    .password-input-group {
        display: flex;
        gap: 0.5rem;
        position: relative;
    }

    .toggle-password {
        padding: 0 1rem;
        background: var(--gray-light);
        color: var(--gray-dark);
        border: 2px solid var(--gray-light);
        border-radius: var(--border-radius-md);
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .toggle-password:hover {
        background: var(--gray-color);
        color: white;
    }

    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .checkbox-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        position: relative;
        padding-left: 25px;
    }

    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 18px;
        width: 18px;
        background-color: #eee;
        border-radius: 3px;
    }

    .checkbox-container:hover input ~ .checkmark {
        background-color: #ccc;
    }

    .checkbox-container input:checked ~ .checkmark {
        background-color: var(--primary-color);
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .checkbox-container input:checked ~ .checkmark:after {
        display: block;
    }

    .checkbox-container .checkmark:after {
        left: 6px;
        top: 2px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .forgot-link {
        color: var(--accent-color);
        text-decoration: none;
        font-size: 0.9rem;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    .voice-commands-info {
        margin-top: 2rem;
        padding: 1rem;
        background: var(--light-color);
        border-radius: var(--border-radius-md);
        border-left: 4px solid var(--secondary-color);
    }

    .voice-commands-info h4 {
        margin-bottom: 0.5rem;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .voice-commands-info ul {
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .voice-commands-info li {
        background: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .voice-commands-info li::before {
        content: '🎤';
        font-size: 0.9rem;
    }
    </style>

   <!-- At the bottom of each page, before footer -->
<script src="voice-command.js"></script>
<script src="script.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>