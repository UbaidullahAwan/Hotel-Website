<?php
include '../config/email_config.php';

function notifyAdmin($subject, $message, $type = 'system') {
    $admin_email = 'info@findahotell.com';
    
    // Log notification in database
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO email_notifications (recipient_email, subject, message, notification_type) 
              VALUES (:email, :subject, :message, :type)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $admin_email);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':type', $type);
    $stmt->execute();
    
    // Send email
    return EmailConfig::sendEmail($admin_email, $subject, $message);
}

// Example usage in other files:
// notifyAdmin('New Booking', 'A new booking has been made!', 'new_booking');
?>