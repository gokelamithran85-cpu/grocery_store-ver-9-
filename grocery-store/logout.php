<?php
require_once 'includes/db_connection.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Check if it's admin logout
$is_admin = isset($_GET['admin']) ? true : false;

// Redirect to appropriate login page
if ($is_admin) {
    header('Location: admin_login.php?logged_out=1');
} else {
    header('Location: index.php?logged_out=1');
}
exit();
?>