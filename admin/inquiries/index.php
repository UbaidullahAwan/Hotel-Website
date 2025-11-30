<?php
// admin/inquiries/index.php
include '../includes/admin_auth.php';
requireAdminLogin();
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// [Keep your existing PHP code for inquiries functionality]
// Handle message status updates
if (isset($_GET['mark_read'])) {
    try {
        $update_stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE message_id = ?");
        $update_stmt->execute([$_GET['mark_read']]);
        header("Location: index.php?success=Message marked as read");
        exit;
    } catch (Exception $e) {
        header("Location: index.php?error=Error updating message");
        exit;
    }
}

if (isset($_GET['mark_unread'])) {
    try {
        $update_stmt = $db->prepare("UPDATE contact_messages SET is_read = 0 WHERE message_id = ?");
        $update_stmt->execute([$_GET['mark_unread']]);
        header("Location: index.php?success=Message marked as unread");
        exit;
    } catch (Exception $e) {
        header("Location: index.php?error=Error updating message");
        exit;
    }
}

if (isset($_GET['delete_id'])) {
    try {
        $delete_stmt = $db->prepare("DELETE FROM contact_messages WHERE message_id = ?");
        $delete_stmt->execute([$_GET['delete_id']]);
        header("Location: index.php?success=Message deleted successfully");
        exit;
    } catch (Exception $e) {
        header("Location: index.php?error=Error deleting message");
        exit;
    }
}

// Get all inquiries
try {
    $stmt = $db->query("
        SELECT message_id, name, email, subject, message, is_read, created_at 
        FROM contact_messages 
        ORDER BY created_at DESC
    ");
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $inquiries = [];
}

// Get counts
try {
    $total_stmt = $db->query("SELECT COUNT(*) as count FROM contact_messages");
    $total_count = $total_stmt->fetch(PDO::FETCH_ASSOC);
    
    $unread_stmt = $db->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
    $unread_count = $unread_stmt->fetch(PDO::FETCH_ASSOC);
    
    $read_stmt = $db->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 1");
    $read_count = $read_stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $total_count = ['count' => 0];
    $unread_count = ['count' => 0];
    $read_count = ['count' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inquiries - Admin</title>
    <!-- REMOVE external CSS file that might be causing conflicts -->
    <!-- <link rel="stylesheet" href="../../../assets/css/style.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* RESET EVERYTHING FIRST */
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

        /* FORCE SIDEBAR TO APPEAR - ULTRA SPECIFIC */
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
            transition: all 0.3s !important;
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

        /* MAIN CONTENT - FORCE PROPER LAYOUT */
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
        .stat-card.unread { border-top: 4px solid #e53e3e !important; }
        .stat-card.read { border-top: 4px solid #48bb78 !important; }
        
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

        /* Inquiries List */
        .inquiries-list {
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05) !important;
            overflow: hidden !important;
        }
        
        .inquiry-item {
            padding: 1.5rem !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        
        .inquiry-item:last-child {
            border-bottom: none !important;
        }
        
        .inquiry-item.unread {
            background: #fff5f5 !important;
            border-left: 4px solid #e53e3e !important;
        }
        
        .inquiry-item.read {
            background: white !important;
            border-left: 4px solid #e2e8f0 !important;
        }
        
        .inquiry-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            margin-bottom: 1rem !important;
        }
        
        .inquiry-info {
            flex: 1 !important;
        }
        
        .inquiry-subject {
            font-size: 1.1rem !important;
            font-weight: 600 !important;
            color: #2d3748 !important;
            margin: 0 0 0.5rem 0 !important;
        }
        
        .inquiry-meta {
            color: #718096 !important;
            font-size: 0.9rem !important;
        }
        
        .inquiry-actions {
            display: flex !important;
            gap: 0.5rem !important;
        }
        
        .btn {
            padding: 8px 16px !important;
            border: none !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
        }
        
        .btn-sm {
            padding: 6px 12px !important;
            font-size: 0.8rem !important;
        }
        
        .btn-read {
            background: #4299e1 !important;
            color: white !important;
        }
        
        .btn-unread {
            background: #ed8936 !important;
            color: white !important;
        }
        
        .btn-delete {
            background: #fc8181 !important;
            color: white !important;
        }
        
        .btn-reply {
            background: #48bb78 !important;
            color: white !important;
        }
        
        .inquiry-message {
            color: #4a5568 !important;
            line-height: 1.6 !important;
            margin: 1rem 0 !important;
            padding: 1rem !important;
            background: #f7fafc !important;
            border-radius: 8px !important;
            border-left: 3px solid #cbd5e0 !important;
        }
        
        .status-badge {
            padding: 4px 8px !important;
            border-radius: 12px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            display: inline-block !important;
            margin-left: 0.5rem !important;
        }
        
        .status-unread {
            background: #fed7d7 !important;
            color: #c53030 !important;
        }
        
        .status-read {
            background: #c6f6d5 !important;
            color: #276749 !important;
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
        
        .filter-tabs {
            display: flex !important;
            border-bottom: 1px solid #e2e8f0 !important;
            margin-bottom: 1.5rem !important;
            background: white !important;
            border-radius: 8px 8px 0 0 !important;
            padding: 0 1.5rem !important;
        }
        
        .filter-tab {
            padding: 1rem 1.5rem !important;
            background: none !important;
            border: none !important;
            color: #718096 !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            border-bottom: 2px solid transparent !important;
        }
        
        .filter-tab.active {
            color: #6b46c1 !important;
            border-bottom-color: #6b46c1 !important;
        }

        /* Mobile Menu Toggle */
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

        /* Responsive Design */
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
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="page-header">
                <div>
                    <h1>Manage Inquiries</h1>
                    <p class="welcome-message">View and manage customer contact messages</p>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-card total">
                    <p class="stat-number"><?php echo $total_count['count']; ?></p>
                    <p class="stat-label">Total Inquiries</p>
                </div>
                <div class="stat-card unread">
                    <p class="stat-number"><?php echo $unread_count['count']; ?></p>
                    <p class="stat-label">Unread Messages</p>
                </div>
                <div class="stat-card read">
                    <p class="stat-number"><?php echo $read_count['count']; ?></p>
                    <p class="stat-label">Read Messages</p>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All Messages</button>
                <button class="filter-tab" data-filter="unread">Unread</button>
                <button class="filter-tab" data-filter="read">Read</button>
            </div>
            
            <!-- Inquiries List -->
            <div class="inquiries-list">
                <?php if (empty($inquiries)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Inquiries Found</h3>
                        <p>No contact messages have been received yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($inquiries as $inquiry): ?>
                        <div class="inquiry-item <?php echo $inquiry['is_read'] ? 'read' : 'unread'; ?>" data-status="<?php echo $inquiry['is_read'] ? 'read' : 'unread'; ?>">
                            <div class="inquiry-header">
                                <div class="inquiry-info">
                                    <h3 class="inquiry-subject">
                                        <?php echo htmlspecialchars($inquiry['subject']); ?>
                                        <span class="status-badge status-<?php echo $inquiry['is_read'] ? 'read' : 'unread'; ?>">
                                            <?php echo $inquiry['is_read'] ? 'READ' : 'UNREAD'; ?>
                                        </span>
                                    </h3>
                                    <div class="inquiry-meta">
                                        <strong>From:</strong> <?php echo htmlspecialchars($inquiry['name']); ?> 
                                        (<a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>"><?php echo htmlspecialchars($inquiry['email']); ?></a>) 
                                        • <strong>Received:</strong> <?php echo date('M j, Y g:i A', strtotime($inquiry['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="inquiry-actions">
                                    <?php if ($inquiry['is_read']): ?>
                                        <a href="index.php?mark_unread=<?php echo $inquiry['message_id']; ?>" class="btn btn-sm btn-unread">
                                            <i class="fas fa-envelope"></i> Mark Unread
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?mark_read=<?php echo $inquiry['message_id']; ?>" class="btn btn-sm btn-read">
                                            <i class="fas fa-envelope-open"></i> Mark Read
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>?subject=Re: <?php echo urlencode($inquiry['subject']); ?>" class="btn btn-sm btn-reply">
                                        <i class="fas fa-reply"></i> Reply
                                    </a>
                                    
                                    <a href="index.php?delete_id=<?php echo $inquiry['message_id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this message? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                            
                            <div class="inquiry-message">
                                <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('adminSidebar').classList.toggle('active');
        });

        // Filter functionality
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const items = document.querySelectorAll('.inquiry-item');
                
                items.forEach(item => {
                    if (filter === 'all') {
                        item.style.display = 'block';
                    } else {
                        const status = item.getAttribute('data-status');
                        if (status === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>