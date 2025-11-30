<?php
// admin/users/index.php
include '../includes/admin_auth.php';
requireAdminLogin();
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle user actions
if ($_POST) {
    try {
        // Update user role
        if (isset($_POST['update_role'])) {
            $stmt = $db->prepare("UPDATE users SET user_role = ? WHERE user_id = ?");
            $stmt->execute([$_POST['user_role'], $_POST['user_id']]);
            $success = "User role updated successfully";
        }
        
        // Send email to user
        if (isset($_POST['send_email'])) {
            $to = $_POST['user_email'];
            $subject = $_POST['email_subject'];
            $message = $_POST['email_message'];
            $headers = "From: admin@findahotell.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            if (mail($to, $subject, $message, $headers)) {
                $success = "Email sent successfully to " . htmlspecialchars($to);
            } else {
                $error = "Failed to send email";
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    try {
        $delete_stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $delete_stmt->execute([$_GET['delete_id']]);
        $success = "User deleted successfully";
    } catch (Exception $e) {
        $error = "Error deleting user: " . $e->getMessage();
    }
}

// Handle user status toggle
if (isset($_GET['toggle_status'])) {
    try {
        // First get current status
        $stmt = $db->prepare("SELECT email_verified FROM users WHERE user_id = ?");
        $stmt->execute([$_GET['toggle_status']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $new_status = $user['email_verified'] ? 0 : 1;
        $update_stmt = $db->prepare("UPDATE users SET email_verified = ? WHERE user_id = ?");
        $update_stmt->execute([$new_status, $_GET['toggle_status']]);
        
        $status_text = $new_status ? 'activated' : 'deactivated';
        $success = "User {$status_text} successfully";
    } catch (Exception $e) {
        $error = "Error updating user status: " . $e->getMessage();
    }
}

// Get all users with additional info
try {
    $stmt = $db->query("
        SELECT 
            u.*,
            (SELECT COUNT(*) FROM bookings WHERE user_id = u.user_id) as total_bookings,
            (SELECT COUNT(*) FROM reviews WHERE user_id = u.user_id) as total_reviews
        FROM users u 
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
    $error = "Error loading users: " . $e->getMessage();
}

// Get user statistics
try {
    $total_users = $db->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
    $active_users = $db->query("SELECT COUNT(*) as count FROM users WHERE email_verified = 1")->fetch(PDO::FETCH_ASSOC);
    $admin_users = $db->query("SELECT COUNT(*) as count FROM users WHERE user_role = 'admin'")->fetch(PDO::FETCH_ASSOC);
    $online_users = $db->query("SELECT COUNT(*) as count FROM users WHERE last_active >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $total_users = $active_users = $admin_users = $online_users = ['count' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <!-- REMOVE external CSS that might be causing conflicts -->
    <!-- <link rel="stylesheet" href="../../../assets/css/style.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* COMPLETE RESET */
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }

        body {
            font-family: 'Arial', sans-serif !important;
            background: #f7fafc !important;
            overflow-x: hidden !important;
        }

        .admin-container {
            display: flex !important;
            min-height: 100vh !important;
        }

        /* ========== SIDEBAR STYLES - FORCED TO APPEAR ========== */
        .admin-sidebar {
            background: #2d3748 !important;
            color: white !important;
            width: 260px !important;
            position: fixed !important;
            height: 100vh !important;
            left: 0 !important;
            top: 0 !important;
            z-index: 1000 !important;
            overflow-y: auto !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .admin-brand {
            padding: 1.5rem !important;
            border-bottom: 1px solid #4a5568 !important;
            background: #1a202c !important;
        }

        .admin-brand h2 {
            color: white !important;
            margin: 0 !important;
            font-size: 1.4rem !important;
            font-weight: 700 !important;
        }

        .admin-brand p {
            color: #ffffff !important;
            font-size: 0.85rem !important;
            margin: 0.3rem 0 0 0 !important;
        }

        .admin-nav {
            padding: 1rem 0 !important;
        }

        .admin-nav a {
            color: #cbd5e0 !important;
            text-decoration: none !important;
            padding: 0.9rem 1.5rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.8rem !important;
            font-size: 0.9rem !important;
            border-left: 3px solid transparent !important;
            position: relative !important;
        }

        .admin-nav a:hover, .admin-nav a.active {
            background: #4a5568 !important;
            color: white !important;
            border-left-color: #6b46c1 !important;
        }

        .admin-nav a i {
            width: 20px !important;
            text-align: center !important;
        }

        .nav-badge {
            background: #e53e3e !important;
            color: white !important;
            border-radius: 10px !important;
            padding: 2px 6px !important;
            font-size: 0.7rem !important;
            font-weight: bold !important;
            margin-left: auto !important;
            min-width: 18px !important;
            text-align: center !important;
        }
        /* ========== END SIDEBAR STYLES ========== */

        /* Main Content */
        .admin-main {
            flex: 1 !important;
            margin-left: 260px !important;
            padding: 2rem !important;
            min-height: 100vh !important;
            width: calc(100% - 260px) !important;
            display: block !important;
            position: relative !important;
            z-index: 1 !important;
        }

        .page-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 2rem !important;
            padding-bottom: 1.5rem !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }

        .page-header h1 {
            color: #2d3748 !important;
            margin: 0 !important;
            font-size: 1.8rem !important;
            font-weight: 700 !important;
        }

        .welcome-message {
            color: #718096 !important;
            font-size: 1rem !important;
            margin-top: 0.5rem !important;
        }

        /* Statistics */
        .stats-container {
            display: flex !important;
            gap: 1.5rem !important;
            margin-bottom: 2rem !important;
            flex-wrap: wrap !important;
        }
        
        .stat-card {
            background: white !important;
            padding: 1.5rem !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
            flex: 1 !important;
            min-width: 200px !important;
            text-align: center !important;
        }
        
        .stat-card.total { border-top: 4px solid #6b46c1 !important; }
        .stat-card.active { border-top: 4px solid #48bb78 !important; }
        .stat-card.admin { border-top: 4px solid #ed8936 !important; }
        .stat-card.online { border-top: 4px solid #4299e1 !important; }
        
        .stat-number {
            font-size: 2rem !important;
            font-weight: bold !important;
            color: #2d3748 !important;
            margin: 0 !important;
        }
        
        .stat-label {
            color: #718096 !important;
            font-size: 0.9rem !important;
            margin: 0.5rem 0 0 0 !important;
        }

        /* Users Table */
        .users-table-container {
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
            overflow: hidden !important;
        }

        .table-header {
            padding: 1.5rem !important;
            border-bottom: 1px solid #e2e8f0 !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }

        .table-header h3 {
            margin: 0 !important;
            color: #2d3748 !important;
        }

        .search-box {
            padding: 0.5rem 1rem !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 6px !important;
            width: 300px !important;
        }

        .users-table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .users-table th {
            background: #f8fafc !important;
            padding: 1rem !important;
            text-align: left !important;
            font-weight: 600 !important;
            color: #4a5568 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .users-table td {
            padding: 1rem !important;
            border-bottom: 1px solid #e2e8f0 !important;
            vertical-align: middle !important;
        }

        .users-table tr:hover {
            background: #f7fafc !important;
        }

        .user-avatar {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            background: #6b46c1 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            font-weight: bold !important;
        }

        .user-avatar img {
            width: 100% !important;
            height: 100% !important;
            border-radius: 50% !important;
            object-fit: cover !important;
        }

        .user-info {
            display: flex !important;
            align-items: center !important;
            gap: 1rem !important;
        }

        .user-details h4 {
            margin: 0 !important;
            color: #2d3748 !important;
            font-weight: 600 !important;
        }

        .user-details p {
            margin: 0.2rem 0 0 0 !important;
            color: #718096 !important;
            font-size: 0.85rem !important;
        }

        .status-badge {
            padding: 4px 8px !important;
            border-radius: 12px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            display: inline-block !important;
        }

        .status-active {
            background: #c6f6d5 !important;
            color: #276749 !important;
        }

        .status-inactive {
            background: #fed7d7 !important;
            color: #c53030 !important;
        }

        .role-badge {
            padding: 4px 8px !important;
            border-radius: 12px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
        }

        .role-admin {
            background: #fed7d7 !important;
            color: #c53030 !important;
        }

        .role-user {
            background: #bee3f8 !important;
            color: #2c5282 !important;
        }

        .online-indicator {
            width: 8px !important;
            height: 8px !important;
            background: #48bb78 !important;
            border-radius: 50% !important;
            display: inline-block !important;
            margin-right: 5px !important;
        }

        .user-actions {
            display: flex !important;
            gap: 0.5rem !important;
        }

        .btn {
            padding: 6px 12px !important;
            border: none !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.3rem !important;
        }

        .btn-sm {
            padding: 4px 8px !important;
            font-size: 0.75rem !important;
        }

        .btn-edit {
            background: #4299e1 !important;
            color: white !important;
        }

        .btn-edit:hover {
            background: #3182ce !important;
        }

        .btn-delete {
            background: #fc8181 !important;
            color: white !important;
        }

        .btn-delete:hover {
            background: #f56565 !important;
        }

        .btn-email {
            background: #48bb78 !important;
            color: white !important;
        }

        .btn-email:hover {
            background: #38a169 !important;
        }

        .btn-toggle {
            background: #ed8936 !important;
            color: white !important;
        }

        .btn-toggle:hover {
            background: #dd771c !important;
        }

        /* Modals */
        .modal {
            display: none !important;
            position: fixed !important;
            z-index: 1000 !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: rgba(0,0,0,0.5) !important;
        }

        .modal-content {
            background-color: white !important;
            margin: 5% auto !important;
            padding: 2rem !important;
            border-radius: 12px !important;
            width: 90% !important;
            max-width: 500px !important;
        }

        .modal-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 1.5rem !important;
        }

        .modal-header h3 {
            margin: 0 !important;
            color: #2d3748 !important;
        }

        .close {
            color: #718096 !important;
            font-size: 1.5rem !important;
            font-weight: bold !important;
            cursor: pointer !important;
        }

        .close:hover {
            color: #2d3748 !important;
        }

        .form-group {
            margin-bottom: 1rem !important;
        }

        .form-label {
            display: block !important;
            margin-bottom: 0.5rem !important;
            font-weight: 600 !important;
            color: #4a5568 !important;
        }

        .form-control {
            width: 100% !important;
            padding: 0.75rem !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 6px !important;
            font-size: 1rem !important;
        }

        textarea.form-control {
            min-height: 120px !important;
            resize: vertical !important;
        }

        .btn-primary {
            background: #6b46c1 !important;
            color: white !important;
            padding: 0.75rem 1.5rem !important;
            border: none !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
        }

        .alert {
            padding: 1rem !important;
            border-radius: 8px !important;
            margin-bottom: 1.5rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            border: 1px solid transparent !important;
        }

        .alert-success {
            background: #f0fff4 !important;
            color: #22543d !important;
            border-color: #9ae6b4 !important;
        }

        .alert-error {
            background: #fed7d7 !important;
            color: #742a2a !important;
            border-color: #feb2b2 !important;
        }

        .empty-state {
            text-align: center !important;
            padding: 4rem 2rem !important;
            color: #718096 !important;
        }

        .empty-state i {
            font-size: 4rem !important;
            margin-bottom: 1rem !important;
            color: #cbd5e0 !important;
        }

        /* Mobile Toggle */
        .mobile-menu-toggle {
            display: none !important;
            background: none !important;
            border: none !important;
            color: #2d3748 !important;
            font-size: 1.5rem !important;
            cursor: pointer !important;
            padding: 0.5rem !important;
            position: fixed !important;
            top: 1rem !important;
            left: 1rem !important;
            z-index: 1001 !important;
            background: white !important;
            border-radius: 6px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block !important;
            }
            
            .admin-main {
                margin-left: 0 !important;
                padding: 1rem !important;
                padding-top: 4rem !important;
                width: 100% !important;
            }
            
            .admin-sidebar {
                transform: translateX(-100%) !important;
            }
            
            .admin-sidebar.active {
                transform: translateX(0) !important;
            }
            
            .stats-container {
                flex-direction: column !important;
            }
            
            .stat-card {
                min-width: 100% !important;
            }
            
            .search-box {
                width: 100% !important;
            }
            
            .table-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 1rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-container">
        <!-- Include Sidebar -->
        <?php 
        // Simple sidebar include with fallback
        $sidebar_file = '../includes/admin_sidebar.php';
        if (file_exists($sidebar_file)) {
            include $sidebar_file;
        } else {
            // Fallback sidebar
            echo '
            <div class="admin-sidebar">
                <div class="admin-brand">
                    <h2>Findahotell</h2>
                    <p>Admin Panel</p>
                </div>
                <nav class="admin-nav">
                    <a href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="index.php" class="active"><i class="fas fa-users"></i> Users</a>
                    <a href="../hotels/index.php"><i class="fas fa-hotel"></i> Hotels</a>
                    <a href="#"><i class="fas fa-bed"></i> Rooms</a>
                    <a href="#"><i class="fas fa-pen"></i> Bookings</a>
                    <a href="../inquiries/index.php"><i class="fas fa-envelope"></i> Inquiries</a>
                    <a href="../logout.php" style="margin-top: 1rem; color: #fc8181;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>';
        }
        ?>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="page-header">
                <div>
                    <h1>Manage Users</h1>
                    <p class="welcome-message">View and manage all registered users</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-card total">
                    <p class="stat-number"><?php echo $total_users['count']; ?></p>
                    <p class="stat-label">Total Users</p>
                </div>
                <div class="stat-card active">
                    <p class="stat-number"><?php echo $active_users['count']; ?></p>
                    <p class="stat-label">Active Users</p>
                </div>
                <div class="stat-card admin">
                    <p class="stat-number"><?php echo $admin_users['count']; ?></p>
                    <p class="stat-label">Admin Users</p>
                </div>
                <div class="stat-card online">
                    <p class="stat-number"><?php echo $online_users['count']; ?></p>
                    <p class="stat-label">Online Now</p>
                </div>
            </div>

            <!-- Users Table -->
            <div class="users-table-container">
                <div class="table-header">
                    <h3>All Users</h3>
                    <input type="text" class="search-box" placeholder="Search users..." id="searchUsers">
                </div>

                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>No users have registered yet.</p>
                    </div>
                <?php else: ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Bookings</th>
                                <th>Reviews</th>
                                <th>Last Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php if (!empty($user['profile_picture'])): ?>
                                                    <img src="../../../<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>">
                                                <?php else: ?>
                                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['user_role']; ?>">
                                            <?php echo ucfirst($user['user_role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['email_verified'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $user['email_verified'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['total_bookings']; ?></td>
                                    <td><?php echo $user['total_reviews']; ?></td>
                                    <td>
                                        <?php if ($user['last_active'] && strtotime($user['last_active']) > time() - 900): ?>
                                            <span class="online-indicator"></span> Online
                                        <?php else: ?>
                                            <?php echo $user['last_active'] ? date('M j, g:i A', strtotime($user['last_active'])) : 'Never'; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="user-actions">
                                            <!-- Edit Role Button -->
                                            <button class="btn btn-sm btn-edit" onclick="openEditModal(<?php echo $user['user_id']; ?>, '<?php echo $user['user_role']; ?>')">
                                                <i class="fas fa-edit"></i> Role
                                            </button>

                                            <!-- Send Email Button -->
                                            <button class="btn btn-sm btn-email" onclick="openEmailModal('<?php echo $user['email']; ?>', '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                                <i class="fas fa-envelope"></i> Email
                                            </button>

                                            <!-- Toggle Status Button -->
                                            <a href="index.php?toggle_status=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-toggle">
                                                <i class="fas fa-power-off"></i> 
                                                <?php echo $user['email_verified'] ? 'Deactivate' : 'Activate'; ?>
                                            </a>

                                            <!-- Delete Button -->
                                            <a href="index.php?delete_id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User Role</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" id="editUserId">
                <input type="hidden" name="update_role" value="1">
                
                <div class="form-group">
                    <label class="form-label">User Role</label>
                    <select name="user_role" class="form-control" id="editUserRole" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">Update Role</button>
            </form>
        </div>
    </div>

    <!-- Send Email Modal -->
    <div id="emailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Send Email to User</h3>
                <span class="close" onclick="closeEmailModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="user_email" id="emailUserEmail">
                <input type="hidden" name="send_email" value="1">
                
                <div class="form-group">
                    <label class="form-label">Recipient</label>
                    <input type="text" class="form-control" id="emailUserName" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="email_subject" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="email_message" class="form-control" required></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Send Email</button>
            </form>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('active');
        });

        // Modal functions
        function openEditModal(userId, currentRole) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserRole').value = currentRole;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openEmailModal(userEmail, userName) {
            document.getElementById('emailUserEmail').value = userEmail;
            document.getElementById('emailUserName').value = userName + ' (' + userEmail + ')';
            document.getElementById('emailModal').style.display = 'block';
        }

        function closeEmailModal() {
            document.getElementById('emailModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const emailModal = document.getElementById('emailModal');
            
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == emailModal) {
                closeEmailModal();
            }
        }

        // Search functionality
        document.getElementById('searchUsers').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Show mobile toggle on small screens
        window.addEventListener('resize', function() {
            const toggle = document.getElementById('mobileMenuToggle');
            if (window.innerWidth <= 768) {
                toggle.style.display = 'block';
            } else {
                toggle.style.display = 'none';
                document.getElementById('adminSidebar').classList.remove('active');
            }
        });

        // Initialize mobile toggle visibility
        if (window.innerWidth <= 768) {
            document.getElementById('mobileMenuToggle').style.display = 'block';
        }
    </script>
</body>
</html>