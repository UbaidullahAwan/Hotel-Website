<?php
class EmailConfig {
    public static $smtp_host = 'smtp.gmail.com';
    public static $smtp_port = 587;
    public static $smtp_username = 'info@findahotell.com';
    public static $smtp_password = 'your-email-password'; // You'll need to set this
    public static $from_email = 'info@findahotell.com';
    public static $from_name = 'Findahotell Admin';
    
    public static function sendEmail($to, $subject, $message) {
        // For now, we'll use basic mail() function
        // In production, you should use PHPMailer or similar
        $headers = "From: " . self::$from_name . " <" . self::$from_email . ">\r\n";
        $headers .= "Reply-To: " . self::$from_email . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
}
?>