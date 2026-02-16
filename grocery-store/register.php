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
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    
    // Validation
    $errors = [];
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Invalid mobile number. Must be 10 digits";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!preg_match("/^[0-9]{6}$/", $pincode)) {
        $errors[] = "Invalid pincode. Must be 6 digits";
    }
    
    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = '$email'";
    $email_result = $conn->query($check_email);
    if ($email_result->num_rows > 0) {
        $errors[] = "Email already registered";
    }
    
    // Check if mobile already exists
    $check_mobile = "SELECT id FROM users WHERE mobile = '$mobile'";
    $mobile_result = $conn->query($check_mobile);
    if ($mobile_result->num_rows > 0) {
        $errors[] = "Mobile number already registered";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (username, mobile, email, password, address, city, pincode) 
                        VALUES ('$username', '$mobile', '$email', '$hashed_password', '$address', '$city', '$pincode')";
        
        if ($conn->query($insert_query)) {
            $success = 'Registration successful! Redirecting to login...';
            echo '<meta http-equiv="refresh" content="3;url=login.php">';
        } else {
            $error = 'Registration failed. Please try again.';
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Voice Grocery Store</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional styles for voice registration */
        .voice-progress {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--light-color);
            border-radius: var(--border-radius-md);
            display: none;
        }
        
        .voice-progress.active {
            display: block;
            animation: slideIn 0.3s ease;
        }
        
        .progress-step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: var(--gray-color);
        }
        
        .progress-step.completed {
            color: var(--success-color);
        }
        
        .progress-step.active {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .progress-step i {
            width: 20px;
        }
        
        .voice-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: var(--secondary-light);
            border-radius: var(--border-radius-sm);
            margin-top: 0.5rem;
        }
        
        .listening-animation {
            display: inline-block;
            width: 12px;
            height: 12px;
            background: var(--secondary-color);
            border-radius: 50%;
            animation: listeningPulse 1s infinite;
        }
        
        @keyframes listeningPulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .voice-shortcuts {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .voice-shortcut-btn {
            padding: 0.5rem 1rem;
            background: white;
            border: 2px solid var(--primary-light);
            border-radius: 25px;
            color: var(--primary-dark);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .voice-shortcut-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .voice-shortcut-btn i {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="form-container">
        <h2 class="form-title">Create Your Account</h2>
        
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

        <!-- Voice Registration Assistant -->
        <div class="voice-register-assistant">
            <p><i class="fas fa-microphone"></i> <strong>Voice Registration Available</strong></p>
            <p style="font-size: 0.9rem; margin-bottom: 1rem;">Click the mic button next to any field or use the voice assistant below</p>
            
            <button type="button" class="voice-assistant-btn" onclick="startFullVoiceRegistration()">
                <i class="fas fa-microphone"></i> Start Voice Registration
            </button>
            
            <button type="button" class="voice-assistant-btn" style="background: var(--accent-color);" onclick="testMicrophone()">
                <i class="fas fa-ear-listen"></i> Test Microphone
            </button>
        </div>

        <!-- Voice Progress Tracker -->
        <div id="voiceProgress" class="voice-progress">
            <h4><i class="fas fa-microphone-alt"></i> Voice Registration Progress</h4>
            <div id="voiceStatus" class="voice-status">
                <span class="listening-animation"></span>
                <span id="statusText">Ready for voice input...</span>
            </div>
            <div id="progressSteps" style="margin-top: 1rem;"></div>
        </div>

        <!-- Voice Shortcuts -->
        <div class="voice-shortcuts">
            <button type="button" class="voice-shortcut-btn" onclick="speakField('username')">
                <i class="fas fa-user"></i> Name
            </button>
            <button type="button" class="voice-shortcut-btn" onclick="speakField('mobile')">
                <i class="fas fa-mobile-alt"></i> Mobile
            </button>
            <button type="button" class="voice-shortcut-btn" onclick="speakField('email')">
                <i class="fas fa-envelope"></i> Email
            </button>
            <button type="button" class="voice-shortcut-btn" onclick="speakField('address')">
                <i class="fas fa-map-marker-alt"></i> Address
            </button>
            <button type="button" class="voice-shortcut-btn" onclick="speakField('city')">
                <i class="fas fa-city"></i> City
            </button>
            <button type="button" class="voice-shortcut-btn" onclick="speakField('pincode')">
                <i class="fas fa-mail-bulk"></i> Pincode
            </button>
        </div>

        <form method="POST" action="" id="registerForm" onsubmit="return validateForm('registerForm')">
            <div class="form-group" data-voice="username">
                <label for="username">Full Name</label>
                <div class="voice-input-group">
                    <input type="text" id="username" name="username" placeholder="Enter your full name" required>
                    <button type="button" class="voice-input-btn" onclick="speakField('username')" title="Click to speak your name">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
                <small style="color: var(--gray-color);">Example: "John Doe" or "My name is John"</small>
            </div>

            <div class="form-group" data-voice="mobile">
                <label for="mobile">Mobile Number</label>
                <div class="voice-input-group">
                    <input type="tel" id="mobile" name="mobile" placeholder="10 digit mobile number" pattern="[0-9]{10}" required>
                    <button type="button" class="voice-input-btn" onclick="speakField('mobile')" title="Click to speak your mobile number">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
                <small style="color: var(--gray-color);">Example: "9876543210" or "nine eight seven six..."</small>
            </div>

            <div class="form-group" data-voice="email">
                <label for="email">Email Address</label>
                <div class="voice-input-group">
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    <button type="button" class="voice-input-btn" onclick="speakField('email')" title="Click to speak your email">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
                <small style="color: var(--gray-color);">Example: "john@example.com" or "john at example dot com"</small>
            </div>

            <div class="form-row">
                <div class="form-group" data-voice="password">
                    <label for="password">Password</label>
                    <div class="password-input-group">
                        <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group" data-voice="confirm_password">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-input-group">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group" data-voice="address">
                <label for="address">Delivery Address</label>
                <div class="voice-input-group">
                    <textarea id="address" name="address" rows="3" placeholder="Enter your complete address" required></textarea>
                    <button type="button" class="voice-input-btn" onclick="speakField('address')" title="Click to speak your address">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" data-voice="city">
                    <label for="city">City</label>
                    <div class="voice-input-group">
                        <input type="text" id="city" name="city" placeholder="Enter your city" required>
                        <button type="button" class="voice-input-btn" onclick="speakField('city')" title="Click to speak your city">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group" data-voice="pincode">
                    <label for="pincode">Pincode</label>
                    <div class="voice-input-group">
                        <input type="text" id="pincode" name="pincode" placeholder="6 digit pincode" pattern="[0-9]{6}" required>
                        <button type="button" class="voice-input-btn" onclick="speakField('pincode')" title="Click to speak your pincode">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group terms-group">
                <label class="checkbox-container">
                    <input type="checkbox" name="terms" required>
                    <span class="checkmark"></span>
                    I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a>
                </label>
            </div>

            <button type="submit" class="btn btn-block">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
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

    // Test microphone function
    function testMicrophone() {
        if (!('webkitSpeechRecognition' in window)) {
            showNotification('Voice recognition is not supported in your browser', 'error');
            return;
        }

        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            showNotification('Microphone is working! Say something...', 'info');
        };

        recognition.onresult = function(event) {
            const text = event.results[0][0].transcript;
            showNotification(`Microphone test successful! You said: "${text}"`, 'success');
        };

        recognition.onerror = function(event) {
            showNotification('Microphone error: ' + event.error, 'error');
        };

        recognition.start();
    }

    // Enhanced voice input function
    function speakField(fieldId) {
        if (!('webkitSpeechRecognition' in window)) {
            showNotification('Voice recognition is not supported in your browser. Please type manually.', 'error');
            return;
        }

        const field = document.getElementById(fieldId);
        if (!field) return;

        // Show progress tracker
        const voiceProgress = document.getElementById('voiceProgress');
        const statusText = document.getElementById('statusText');
        if (voiceProgress) {
            voiceProgress.classList.add('active');
            statusText.innerHTML = `Listening for ${fieldId.replace(/([A-Z])/g, ' $1').toLowerCase()}... <span class="listening-animation"></span>`;
        }

        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            field.style.borderColor = 'var(--secondary-color)';
            field.style.backgroundColor = '#fff8e7';
            showNotification(`Listening for ${fieldId}... Speak now`, 'info');
            
            // Update bot message
            const botMessage = document.getElementById('botMessage');
            if (botMessage) {
                botMessage.innerText = `🎤 Listening for your ${fieldId.replace(/([A-Z])/g, ' $1').toLowerCase()}...`;
            }
        };

        recognition.onresult = function(event) {
            let spokenText = event.results[0][0].transcript;
            console.log('Raw spoken text:', spokenText);
            
            // Process text based on field type
            if (fieldId === 'email') {
                // Convert "at" to @ and "dot" to .
                spokenText = spokenText.toLowerCase()
                    .replace(/\s+at\s+/gi, '@')
                    .replace(/\s+dot\s+/gi, '.')
                    .replace(/\s+/g, '')
                    .replace(/[^a-z0-9@._-]/g, '');
            } else if (fieldId === 'mobile' || fieldId === 'pincode') {
                // Extract only numbers
                spokenText = spokenText.replace(/\D/g, '');
            } else if (fieldId === 'username' || fieldId === 'city') {
                // Capitalize first letter of each word
                spokenText = spokenText.split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                    .join(' ');
            }
            
            field.value = spokenText;
            field.style.borderColor = 'var(--success-color)';
            field.style.backgroundColor = '#f0fff0';
            
            // Trigger change event
            const changeEvent = new Event('change', { bubbles: true });
            field.dispatchEvent(changeEvent);
            
            // Update status
            if (statusText) {
                statusText.innerHTML = `✓ Captured: "${spokenText}"`;
            }
            
            // Update bot message
            const botMessage = document.getElementById('botMessage');
            if (botMessage) {
                botMessage.innerText = `✓ ${fieldId}: "${spokenText}"`;
            }
            
            showNotification('Voice input captured successfully!', 'success');
            
            // Auto move to next field
            setTimeout(() => {
                moveToNextField(fieldId);
            }, 1500);
        };

        recognition.onerror = function(event) {
            console.error('Voice error:', event.error);
            field.style.borderColor = 'var(--danger-color)';
            
            if (statusText) {
                statusText.innerHTML = `❌ Error: ${event.error}. Please try again or type manually.`;
            }
            
            const botMessage = document.getElementById('botMessage');
            if (botMessage) {
                botMessage.innerText = '❌ Voice input failed. Please try again or type manually.';
            }
            
            if (event.error === 'not-allowed') {
                showNotification('Microphone access denied. Please allow microphone access and try again.', 'error');
            } else if (event.error === 'no-speech') {
                showNotification('No speech detected. Please try again.', 'warning');
            } else {
                showNotification('Voice input failed: ' + event.error, 'error');
            }
        };

        recognition.onend = function() {
            setTimeout(() => {
                field.style.borderColor = '';
                field.style.backgroundColor = '';
            }, 2000);
        };

        try {
            recognition.start();
        } catch (e) {
            console.error('Recognition start error:', e);
            showNotification('Failed to start voice recognition. Please try again.', 'error');
        }
    }

    // Move to next field after voice input
    function moveToNextField(currentFieldId) {
        const fields = ['username', 'mobile', 'email', 'address', 'city', 'pincode'];
        const currentIndex = fields.indexOf(currentFieldId);
        
        if (currentIndex < fields.length - 1) {
            const nextFieldId = fields[currentIndex + 1];
            const nextField = document.getElementById(nextFieldId);
            
            if (nextField && !nextField.value) {
                // Ask user if they want to fill next field
                const botMessage = document.getElementById('botMessage');
                if (botMessage) {
                    botMessage.innerText = `Would you like to fill ${nextFieldId}? Click the mic button or say "yes" to continue.`;
                }
                
                // Highlight next field
                nextField.style.borderColor = 'var(--accent-color)';
                nextField.style.borderWidth = '2px';
                
                // Flash the field
                setTimeout(() => {
                    nextField.style.borderColor = '';
                    nextField.style.borderWidth = '';
                }, 3000);
            }
        } else {
            // All fields completed
            const botMessage = document.getElementById('botMessage');
            if (botMessage) {
                botMessage.innerText = '✅ All fields completed! Click Create Account to finish registration.';
            }
            
            // Hide progress tracker
            const voiceProgress = document.getElementById('voiceProgress');
            if (voiceProgress) {
                setTimeout(() => {
                    voiceProgress.classList.remove('active');
                }, 3000);
            }
        }
    }

    // Start full voice registration
    function startFullVoiceRegistration() {
        const voiceProgress = document.getElementById('voiceProgress');
        const statusText = document.getElementById('statusText');
        const progressSteps = document.getElementById('progressSteps');
        
        voiceProgress.classList.add('active');
        statusText.innerHTML = 'Starting voice registration...';
        
        // Create progress steps
        const fields = ['username', 'mobile', 'email', 'address', 'city', 'pincode'];
        const fieldLabels = {
            'username': 'Full Name',
            'mobile': 'Mobile Number',
            'email': 'Email Address',
            'address': 'Address',
            'city': 'City',
            'pincode': 'Pincode'
        };
        
        let stepsHtml = '';
        fields.forEach((field, index) => {
            stepsHtml += `
                <div id="step-${field}" class="progress-step">
                    <i class="fas fa-circle"></i>
                    <span>${fieldLabels[field]}</span>
                </div>
            `;
        });
        progressSteps.innerHTML = stepsHtml;
        
        // Start with first field
        let currentIndex = 0;
        
        function processNextField() {
            if (currentIndex < fields.length) {
                const fieldId = fields[currentIndex];
                const step = document.getElementById(`step-${fieldId}`);
                
                // Mark as active
                document.querySelectorAll('.progress-step').forEach(s => s.classList.remove('active'));
                step.classList.add('active');
                step.querySelector('i').className = 'fas fa-microphone';
                
                statusText.innerHTML = `Please speak your ${fieldLabels[fieldId]}...`;
                
                // Speak the field
                setTimeout(() => {
                    speakField(fieldId);
                }, 500);
                
                currentIndex++;
            } else {
                // All fields completed
                statusText.innerHTML = '✅ Registration complete! Please review and submit the form.';
                document.querySelectorAll('.progress-step').forEach(s => {
                    s.classList.add('completed');
                    s.querySelector('i').className = 'fas fa-check-circle';
                });
                
                showNotification('Voice registration completed! Please verify your details.', 'success');
            }
        }
        
        // Override the moveToNextField function temporarily
        const originalMoveToNextField = window.moveToNextField;
        window.moveToNextField = function(fieldId) {
            // Mark current step as completed
            const step = document.getElementById(`step-${fieldId}`);
            if (step) {
                step.classList.remove('active');
                step.classList.add('completed');
                step.querySelector('i').className = 'fas fa-check-circle';
            }
            
            // Process next field
            setTimeout(() => {
                processNextField();
            }, 1000);
        };
        
        // Start the process
        processNextField();
    }

    // Add keyboard shortcut for voice input (Ctrl+Shift+V)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'V') {
            e.preventDefault();
            startFullVoiceRegistration();
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check for microphone permission
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({name: 'microphone'}).then(function(result) {
                if (result.state === 'denied') {
                    showNotification('Microphone access is blocked. Please enable it for voice features.', 'warning');
                }
            });
        }
        
        // Check browser support
        if (!('webkitSpeechRecognition' in window)) {
            showNotification('Voice recognition is not supported in your browser. Please use Chrome, Edge, or Safari.', 'warning');
            document.querySelectorAll('.voice-input-btn, .voice-assistant-btn, .voice-shortcut-btn').forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.title = 'Voice recognition not supported in this browser';
            });
        }
    });
    </script>

    <script src="voice-command.js"></script>
    <script src="script.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>