<?php
// Common helper functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input));
}

function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

function getCartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}

function getCategoryIcon($category) {
    $icons = [
        'vegetables' => '🥦',
        'fruits' => '🍎',
        'snacks' => '🍿',
        'dairy' => '🥛',
        'beverages' => '🥤',
        'household' => '🏠',
        'personal-care' => '🧴',
        'frozen-foods' => '🧊'
    ];
    
    return $icons[$category] ?? '📦';
}

function sendEmail($to, $subject, $message) {
    // In production, use PHPMailer or similar
    // For demo, save to file
    $filename = "emails/" . time() . "_" . uniqid() . ".html";
    @file_put_contents($filename, $message);
    return true;
}
?>