<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function adminLogin($user_id, $email, $first_name, $last_name) {
    $_SESSION['admin_id'] = $user_id;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_name'] = $first_name . ' ' . $last_name;
    $_SESSION['admin_logged_in'] = true;
    
    // Debug: Check if session is set
    error_log("Admin login successful: " . $email);
}

function adminLogout() {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Check if user is admin
function isUserAdmin($user_id) {
    include '../../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT user_role FROM users WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['user_role'] === 'admin';
    }
    return false;
}
?>