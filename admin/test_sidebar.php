<?php
// admin/test_sidebar.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f7fafc;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* FORCE SIDEBAR TO BE VISIBLE */
        .admin-sidebar {
            background: #2d3748 !important;
            color: white !important;
            width: 260px !important;
            position: fixed !important;
            height: 100vh !important;
            left: 0 !important;
            top: 0 !important;
            z-index: 9999 !important;
            border: 5px solid red !important;
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
        }

        .admin-nav {
            padding: 1rem 0;
        }

        .admin-nav a {
            color: #cbd5e0;
            text-decoration: none;
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            border-left: 3px solid transparent;
        }

        .admin-nav a:hover {
            background: #4a5568;
            color: white;
            border-left-color: #6b46c1;
        }

        .admin-main {
            margin-left: 260px;
            padding: 2rem;
            background: white;
            min-height: 100vh;
        }

        .debug-box {
            background: yellow;
            padding: 2rem;
            margin: 2rem 0;
            border: 3px solid red;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Simple hardcoded sidebar -->
        <div class="admin-sidebar">
            <div class="admin-brand">
                <h2>Findahotell</h2>
                <p>Admin Panel</p>
            </div>
            <nav class="admin-nav">
                <a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="#"><i class="fas fa-hotel"></i> Hotels</a>
                <a href="#"><i class="fas fa-envelope"></i> Inquiries</a>
                <a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <div class="admin-main">
            <h1>Sidebar Test Page</h1>
            
            <div class="debug-box">
                <h3>DEBUG INFORMATION</h3>
                <p>If you can see a red-bordered sidebar on the left, then the sidebar CSS is working.</p>
                <p>If you DON'T see a red sidebar, there's a fundamental CSS issue.</p>
            </div>

            <p>This page has a hardcoded sidebar with forced CSS styles.</p>
        </div>
    </div>
</body>
</html>