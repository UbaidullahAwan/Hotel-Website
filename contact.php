<?php
// contact.php - UPDATED FOR YOUR DATABASE CLASS
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$success = '';
$error = '';

// Load database configuration using your class
try {
    require_once 'config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Test connection
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    
} catch (Exception $e) {
    $error = "Database configuration error: " . $e->getMessage();
}

// Process contact form ONLY if database is connected
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $country = $_POST['country'] ?? '';
    $subject = $_POST['subject'] ?? 'General Inquiry';
    $message_content = $_POST['message'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message_content)) {
        $error = "Please fill in all required fields (Name, Email, Message).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Check if table exists, create if not
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'contact_messages'")->fetch();
            if (!$tableCheck) {
                $pdo->exec("
                    CREATE TABLE contact_messages (
                        message_id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        email VARCHAR(255) NOT NULL,
                        phone VARCHAR(20),
                        country VARCHAR(100),
                        subject VARCHAR(255),
                        message TEXT NOT NULL,
                        ip_address VARCHAR(45),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        is_read TINYINT DEFAULT 0
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            }
            
            // Prepare data
            $name = htmlspecialchars(trim($name));
            $email = htmlspecialchars(trim($email));
            $phone = htmlspecialchars(trim($phone));
            $country = htmlspecialchars(trim($country));
            $subject = htmlspecialchars(trim($subject));
            $message_content = htmlspecialchars(trim($message_content));
            $ip_address = $_SERVER['REMOTE_ADDR'];
            
            // Save to database
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, phone, country, subject, message, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([$name, $email, $phone, $country, $subject, $message_content, $ip_address]);
            
            if ($result) {
                $success = "We have got your message! We will get back to you shortly.";
                
                // Send email notification to admin
                try {
                    $admin_stmt = $pdo->query("SELECT email FROM users WHERE user_role = 'admin'");
                    $admin_emails = $admin_stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (!empty($admin_emails)) {
                        $to = implode(',', $admin_emails);
                        $email_subject = "New Contact Form Message: " . $subject;
                        $email_body = "
                            New contact form submission received:\n\n
                            Name: $name\n
                            Email: $email\n
                            Phone: $phone\n
                            Country: $country\n
                            Subject: $subject\n\n
                            Message:\n$message_content\n\n
                            IP Address: $ip_address\n
                            Received: " . date('Y-m-d H:i:s') . "
                        ";
                        
                        $headers = "From: noreply@findahotell.com\r\n";
                        $headers .= "Reply-To: $email\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        
                        // Send email (uncomment when ready)
                        // mail($to, $email_subject, $email_body, $headers);
                    }
                } catch (Exception $e) {
                    // Email failure shouldn't prevent success message
                    error_log("Email error: " . $e->getMessage());
                }
                
            } else {
                $error = "Failed to save your message. Please try again.";
            }
            
        } catch (Exception $e) {
            $error = "Sorry, there was an error sending your message. Please try again.";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = "Database connection failed. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - FindAHotel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .contact-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .contact-header h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .contact-header p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }
        
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .contact-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px;
        }
        
        .contact-info h2 {
            font-size: 2rem;
            margin-bottom: 30px;
            color: white;
        }
        
        .contact-method {
            margin-bottom: 30px;
        }
        
        .contact-method h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: white;
        }
        
        .contact-method p {
            margin-bottom: 5px;
            opacity: 0.9;
        }
        
        .contact-method a {
            color: white;
            text-decoration: none;
        }
        
        .contact-method a:hover {
            text-decoration: underline;
        }
        
        .phone-numbers {
            margin-top: 10px;
        }
        
        .phone-numbers p {
            margin-bottom: 3px;
            font-size: 0.9rem;
        }
        
        .office-address {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .office-address h4 {
            margin-bottom: 10px;
            color: white;
        }
        
        .contact-form {
            padding: 50px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #c3e6cb;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            animation: fadeIn 0.5s ease-in;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #f5c6cb;
            text-align: center;
            font-weight: bold;
            animation: fadeIn 0.5s ease-in;
        }
        
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease-in;
        }
        
        .popup {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease-out;
        }
        
        .popup h3 {
            color: #155724;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .popup p {
            margin-bottom: 20px;
            color: #333;
        }
        
        .popup-btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .popup-btn:hover {
            background: #5a6fd8;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .popup {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Include your header -->
    <?php include 'includes/header.php'; ?>

    <div class="contact-container">
        <div class="contact-header">
            <h1>Get in Touch With Us</h1>
            <p>We're here to help you find the perfect stay</p>
        </div>
        
        <div class="contact-content">
            <!-- Contact Information Section -->
            <div class="contact-info">
                <h2>Contact Information</h2>
                
                <div class="contact-method">
                    <h3> Mobile & WhatsApp</h3>
                    <p>We are available 24/7</p>
                    <div class="phone-numbers">
                        <p><a href="tel:+447837263635">UK: +44 1233 456560</a></p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <h3>Send us an Email</h3>
                    <p>Please write to us on</p>
                    <p><a href="mailto:info@findahotell.com">info@findahotell.com</a></p>
                </div>
                
                <div class="contact-method">
                    <h3>Company Registration</h3>
                    <p>Registered in England & Wales</p>
                    <p>Reg No: 16497358</p>
                </div>
                
                <div class="office-address">
                    <h4>Head Office:</h4>
                    <p>Flat 30<br>
                    MackWorth House<br>
                    Augustus Street, London,<br>
                    England, NW1 3RE</p>
                </div>
                
               
            </div>
            
            <!-- Contact Form Section -->
            <div class="contact-form">
                <h2>Online Contact Form</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Your Email *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Mobile Number</label>
                            <input type="tel" id="phone" name="phone"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select id="country" name="country">
                                <option value="">Select Country</option>
                                <option value="United Kingdom" <?= ($_POST['country'] ?? '') == 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                                <option value="United States" <?= ($_POST['country'] ?? '') == 'United States' ? 'selected' : '' ?>>United States</option>
                                <option value="Canada" <?= ($_POST['country'] ?? '') == 'Canada' ? 'selected' : '' ?>>Canada</option>
                                <option value="Australia" <?= ($_POST['country'] ?? '') == 'Australia' ? 'selected' : '' ?>>Australia</option>
                                <option value="Germany" <?= ($_POST['country'] ?? '') == 'Germany' ? 'selected' : '' ?>>Germany</option>
                                <option value="France" <?= ($_POST['country'] ?? '') == 'France' ? 'selected' : '' ?>>France</option>
                                <option value="Other" <?= ($_POST['country'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" placeholder="What is this regarding?"
                               value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message *</label>
                        <textarea id="message" name="message" rows="6" placeholder="Please describe your inquiry in detail..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <?php if (!empty($success)): ?>
    <div class="popup-overlay" id="successPopup">
        <div class="popup">
            <h3>Message Sent Successfully!</h3>
            <p><?= $success ?></p>
            <button class="popup-btn" onclick="closePopup()">OK</button>
        </div>
    </div>
    <?php endif; ?>
    <?php include 'includes/footer.php'; ?>
    <script>
        // Form submission handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.textContent = 'Sending...';
        });

        // Close popup function
        function closePopup() {
            const popup = document.getElementById('successPopup');
            if (popup) {
                popup.style.display = 'none';
                document.getElementById('contactForm').reset();
            }
        }

        // Auto-close popup after 5 seconds
        <?php if (!empty($success)): ?>
        setTimeout(() => {
            closePopup();
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>