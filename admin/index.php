<?php
include 'includes/admin_auth.php';
requireAdminLogin();
include '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];
$queries = [
    'total_users' => "SELECT COUNT(*) as count FROM users",
    'total_hotels' => "SELECT COUNT(*) as count FROM hotels", 
    'total_bookings' => "SELECT COUNT(*) as count FROM bookings",
    'pending_bookings' => "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'pending'",
    'total_reviews' => "SELECT COUNT(*) as count FROM reviews",
    'today_bookings' => "SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()",
    'revenue_today' => "SELECT COALESCE(SUM(total_amount), 0) as amount FROM bookings WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'",
    'admin_users' => "SELECT COUNT(*) as count FROM users WHERE user_role = 'admin'",
    'unread_inquiries' => "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0",
    'active_users' => "SELECT COUNT(*) as count FROM users WHERE last_active >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
];

foreach ($queries as $key => $query) {
    try {
        $stmt = $db->query($query);
        $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $stats[$key] = ['count' => 0, 'amount' => 0];
    }
}

// Get recent activities
try {
    $activities_query = "
        (SELECT 'booking' as type, booking_id as id, CONCAT('New booking #', booking_id) as description, created_at 
         FROM bookings ORDER BY created_at DESC LIMIT 3)
        UNION
        (SELECT 'user' as type, user_id as id, CONCAT('New user: ', email) as description, created_at 
         FROM users ORDER BY created_at DESC LIMIT 3)
        UNION
        (SELECT 'review' as type, review_id as id, CONCAT('New review for booking #', booking_id) as description, created_at 
         FROM reviews ORDER BY created_at DESC LIMIT 2)
        UNION
        (SELECT 'admin' as type, user_id as id, CONCAT('Admin login: ', email) as description, created_at 
         FROM users WHERE user_role = 'admin' ORDER BY created_at DESC LIMIT 2)
        ORDER BY created_at DESC LIMIT 8
    ";
    $activities_stmt = $db->query($activities_query);
    $activities = $activities_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $activities = [];
}

// Get active users
try {
    $active_users_stmt = $db->query("
        SELECT user_id, first_name, last_name, email, profile_picture, last_active, user_role 
        FROM users 
        WHERE last_active >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) 
        ORDER BY last_active DESC 
        LIMIT 10
    ");
    $active_users = $active_users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $active_users = [];
}

// Get recent inquiries
try {
    $inquiries_stmt = $db->query("
        SELECT message_id, name, email, subject, message, is_read, created_at 
        FROM contact_messages 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_inquiries = $inquiries_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent_inquiries = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Findahotell</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f7fafc;
            overflow-x: hidden;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Fixed Sidebar */
        .admin-sidebar {
            background: #2d3748;
            color: white;
            width: 260px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }

        .admin-brand {
            padding: 1.5rem;
            border-bottom: 1px solid #4a5568;
            background: #1a202c;
        }

        .admin-brand h2 {
            color: white;
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .admin-brand p {
            color: #ffffff;
            font-size: 0.85rem;
            margin: 0.3rem 0 0 0;
        }

        .admin-nav {
            padding: 1rem 0;
        }

        .admin-nav a {
            color: #cbd5e0;
            text-decoration: none;
            padding: 0.9rem 1.5rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.9rem;
            border-left: 3px solid transparent;
            position: relative;
        }

        .admin-nav a:hover, .admin-nav a.active {
            background: #4a5568;
            color: white;
            border-left-color: #6b46c1;
        }

        .admin-nav a i {
            width: 20px;
            text-align: center;
        }

        /* Notification Badges */
        .nav-badge {
            background: #e53e3e;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            margin-left: auto;
            min-width: 18px;
            text-align: center;
        }

        .online-indicator {
            width: 8px;
            height: 8px;
            background: #48bb78;
            border-radius: 50%;
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .admin-header h1 {
            color: #2d3748;
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .welcome-message {
            color: #718096;
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        /* Statistics - Flex Layout */
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-left: 4px solid #6b46c1;
            flex: 1;
            min-width: 250px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .stat-card.alert {
            border-left-color: #e53e3e;
            animation: alertPulse 2s infinite;
        }

        @keyframes alertPulse {
            0% { box-shadow: 0 4px 6px rgba(229, 62, 62, 0.1); }
            50% { box-shadow: 0 4px 15px rgba(229, 62, 62, 0.3); }
            100% { box-shadow: 0 4px 6px rgba(229, 62, 62, 0.1); }
        }

        .stat-card h3 {
            margin: 0 0 0.8rem 0;
            color: #718096;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .stat-card .number {
            font-size: 2.4rem;
            font-weight: bold;
            color: #2d3748;
            margin: 0;
            line-height: 1;
        }

        .stat-card .description {
            font-size: 0.8rem;
            color: #a0aec0;
            margin-top: 0.8rem;
        }

        /* Dashboard Sections */
        .dashboard-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .activities-section, .quick-actions, .active-users-section, .inquiries-section {
            background: white;
            border-radius: 12px;
            padding: 1.8rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .section-title {
            color: #2d3748;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header .section-title {
            margin-bottom: 0;
        }

        .view-all-link {
            color: #6b46c1;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .activity-item, .user-item, .inquiry-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .activity-item:last-child, .user-item:last-child, .inquiry-item:last-child {
            border-bottom: none;
        }

        .activity-icon, .user-avatar, .inquiry-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .activity-booking { background: #4299e1; }
        .activity-user { background: #48bb78; }
        .activity-review { background: #ed8936; }
        .activity-admin { background: #9f7aea; }
        .inquiry-unread { background: #e53e3e; }
        .inquiry-read { background: #a0aec0; }

        .user-avatar {
            background: #6b46c1;
            position: relative;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-status {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #48bb78;
            border: 2px solid white;
            border-radius: 50%;
        }

        .activity-content, .user-content, .inquiry-content {
            flex: 1;
        }

        .activity-content p, .user-content p, .inquiry-content p {
            margin: 0 0 0.3rem 0;
            font-weight: 500;
            color: #2d3748;
            font-size: 0.95rem;
        }

        .activity-time, .user-role, .inquiry-time {
            color: #718096;
            font-size: 0.8rem;
        }

        .user-role {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            display: inline-block;
        }

        .user-role.admin {
            background: #fed7d7;
            color: #c53030;
        }

        .inquiry-item.unread {
            background: #fff5f5;
            margin: 0 -1.8rem;
            padding: 1.2rem 1.8rem;
            border-left: 3px solid #e53e3e;
        }

        .inquiry-subject {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .inquiry-message {
            color: #718096;
            font-size: 0.85rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .quick-actions-grid {
            display: grid;
            gap: 1rem;
        }

        .action-card {
            background: #f8fafc;
            padding: 1.4rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .action-card:hover {
            background: #6b46c1;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(107, 70, 193, 0.3);
            border-color: #6b46c1;
        }

        .action-card h4 {
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1rem;
        }

        .action-card p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e0;
        }

        /* Tabs for Active Users and Inquiries */
        .section-tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .tab-button {
            padding: 0.8rem 1.5rem;
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }

        .tab-button.active {
            color: #6b46c1;
            border-bottom-color: #6b46c1;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                min-width: 200px;
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .admin-main {
                margin-left: 0;
                padding: 1rem;
            }
            
            .stats-container {
                flex-direction: column;
            }
            
            .stat-card {
                min-width: 100%;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #2d3748;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-sidebar.active {
                transform: translateX(0);
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
        <!-- Fixed Sidebar -->
        <div class="admin-sidebar" id="adminSidebar">
            <div class="admin-brand">
                <h2>Findahotell</h2>
                <p>Admin Panel</p>
                <p style="font-size: 0.8rem; margin-top: 0.5rem; color: #6b46c1;">
                    <i class="fas fa-user-shield"></i> <?php echo $_SESSION['admin_name']; ?>
                </p>
            </div>
            
            <nav class="admin-nav">
                <a href="index.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="hotels/index.php">
                    <i class="fas fa-hotel"></i> Hotels
                </a>
                <a href="countries/edit.php">
                    <i class="fas fa-globe"></i> Countries
                </a>
                <a href="rooms/rooms.php">
                    <i class="fas fa-bed"></i> Rooms
                </a>
                <a href="bookings/index.php">
                    <i class="fas fa-calendar-check"></i> Bookings
                </a>
                <a href="users/index.php">
                    <i class="fas fa-users"></i> Users
                    <?php if ($stats['active_users']['count'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['active_users']['count']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="reviews/index.php">
                    <i class="fas fa-star"></i> Reviews
                </a>
                <a href="inquiries/index.php">
                    <i class="fas fa-envelope"></i> Inquiries
                    <?php if ($stats['unread_inquiries']['count'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['unread_inquiries']['count']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" style="margin-top: 1rem; color: #fc8181; border-left-color: #fc8181;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <div>
                    <h1>Dashboard Overview</h1>
                    <p class="welcome-message">Welcome back! Here's what's happening today.</p>
                </div>
                <div style="color: #718096; font-size: 0.9rem; font-weight: 500;">
                    <i class="fas fa-calendar"></i> <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
            
            <!-- Statistics - Flex Layout -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="number"><?php echo $stats['total_users']['count']; ?></p>
                    <p class="description">Registered users</p>
                </div>
                <div class="stat-card">
                    <h3>Hotels</h3>
                    <p class="number"><?php echo $stats['total_hotels']['count']; ?></p>
                    <p class="description">Properties listed</p>
                </div>
                <div class="stat-card">
                    <h3>Active Users</h3>
                    <p class="number"><?php echo $stats['active_users']['count']; ?></p>
                    <p class="description">Online now</p>
                </div>
                <div class="stat-card <?php echo $stats['unread_inquiries']['count'] > 0 ? 'alert' : ''; ?>">
                    <h3>Unread Inquiries</h3>
                    <p class="number"><?php echo $stats['unread_inquiries']['count']; ?></p>
                    <p class="description">Need attention</p>
                </div>
                <div class="stat-card">
                    <h3>Today's Bookings</h3>
                    <p class="number"><?php echo $stats['today_bookings']['count']; ?></p>
                    <p class="description">New today</p>
                </div>
                <div class="stat-card">
                    <h3>Revenue Today</h3>
                    <p class="number">£ <?php echo number_format($stats[' revenue_today']['amount'], 2); ?></p>
                    <p class="description">Today's income</p>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <!-- Recent Activities -->
                <div class="activities-section">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i> Recent Activities
                    </h3>
                    
                    <?php if (empty($activities)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <h3>No Recent Activities</h3>
                            <p>Activities will appear here as they happen</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon activity-<?php echo $activity['type']; ?>">
                                    <i class="fas fa-<?php echo getActivityIcon($activity['type']); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p><?php echo $activity['description']; ?></p>
                                    <p class="activity-time"><?php echo time_ago($activity['created_at']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Right Sidebar Sections -->
                <div class="quick-actions-section">
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h3 class="section-title">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h3>
                        
                        <div class="quick-actions-grid">
                            <a href="hotels/add.php" class="action-card">
                                <h4><i class="fas fa-plus-circle"></i> Add Hotel</h4>
                                <p>Create new hotel property</p>
                            </a>
                            
                            <a href="rooms/rooms.php" class="action-card">
                                <h4><i class="fas fa-bed"></i> Manage Rooms</h4>
                                <p>Add or edit rooms</p>
                            </a>
                            
                            <a href="bookings/index.php" class="action-card">
                                <h4><i class="fas fa-calendar-alt"></i> View Bookings</h4>
                                <p>Check all reservations</p>
                            </a>
                            
                            <a href="users/index.php" class="action-card">
                                <h4><i class="fas fa-user-cog"></i> User Management</h4>
                                <p>Manage user accounts</p>
                            </a>
                        </div>
                    </div>

                    <!-- Active Users & Inquiries Tabs -->
                    <div class="active-users-section" style="margin-top: 2rem;">
                        <div class="section-tabs">
                            <button class="tab-button active" data-tab="active-users">Active Users</button>
                            <button class="tab-button" data-tab="inquiries">Inquiries</button>
                        </div>

                        <!-- Active Users Tab -->
                        <div class="tab-content active" id="active-users-tab">
                            <div class="section-header">
                                <h3 class="section-title">
                                    <i class="fas fa-users"></i> Active Users
                                </h3>
                                <a href="users/index.php" class="view-all-link">View All</a>
                            </div>
                            
                            <?php if (empty($active_users)): ?>
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <i class="fas fa-user-slash"></i>
                                    <h3>No Active Users</h3>
                                    <p>No users are currently online</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($active_users as $user): ?>
                                    <div class="user-item">
                                        <div class="user-avatar">
                                            <?php if (!empty($user['profile_picture'])): ?>
                                                <img src="../../<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>">
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                            <div class="user-status"></div>
                                        </div>
                                        <div class="user-content">
                                            <p><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                                            <p class="user-role <?php echo $user['user_role']; ?>">
                                                <?php echo ucfirst($user['user_role']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Inquiries Tab -->
                        <div class="tab-content" id="inquiries-tab">
                            <div class="section-header">
                                <h3 class="section-title">
                                    <i class="fas fa-envelope"></i> Recent Inquiries
                                </h3>
                                <a href="inquiries/index.php" class="view-all-link">View All</a>
                            </div>
                            
                            <?php if (empty($recent_inquiries)): ?>
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <i class="fas fa-inbox"></i>
                                    <h3>No Inquiries</h3>
                                    <p>No contact messages yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_inquiries as $inquiry): ?>
                                    <div class="inquiry-item <?php echo !$inquiry['is_read'] ? 'unread' : ''; ?>">
                                        <div class="inquiry-icon <?php echo !$inquiry['is_read'] ? 'inquiry-unread' : 'inquiry-read'; ?>">
                                            <i class="fas fa-envelope<?php echo !$inquiry['is_read'] ? '-open' : ''; ?>"></i>
                                        </div>
                                        <div class="inquiry-content">
                                            <p class="inquiry-subject"><?php echo htmlspecialchars($inquiry['subject']); ?></p>
                                            <p class="inquiry-message"><?php echo htmlspecialchars(substr($inquiry['message'], 0, 100)); ?>...</p>
                                            <p class="inquiry-time">From: <?php echo htmlspecialchars($inquiry['name']); ?> • <?php echo time_ago($inquiry['created_at']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.getElementById('mobileMenuToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and contents
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Show corresponding content
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Add animation to stat cards
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>
</body>
</html>

<?php
function getActivityIcon($type) {
    switch ($type) {
        case 'booking': return 'calendar-check';
        case 'user': return 'user-plus';
        case 'review': return 'star';
        case 'admin': return 'user-shield';
        default: return 'bell';
    }
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}
?>