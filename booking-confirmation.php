<?php
include 'config/database.php';
include 'includes/auth.php';
requireLogin();

$booking_id = $_GET['booking_id'] ?? 0;

$database = new Database();
$db = $database->getConnection();

$query = "SELECT b.*, h.hotel_name, h.address, h.city, h.country, 
          u.first_name, u.last_name, u.email, u.phone_number,
          p.transaction_id, p.payment_status
          FROM bookings b
          JOIN hotels h ON b.hotel_id = h.hotel_id
          JOIN users u ON b.user_id = u.user_id
          LEFT JOIN payments p ON b.booking_id = p.booking_id
          WHERE b.booking_id = :booking_id AND b.user_id = :user_id";

$stmt = $db->prepare($query);
$stmt->bindParam(':booking_id', $booking_id);
$stmt->bindParam(':user_id', getCurrentUserId());
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header("Location: index.php");
    exit;
}

$booking = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Findahotell</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="confirmation-container">
        <div class="confirmation-header success">
            <h1>Booking Confirmed!</h1>
            <p>Your booking has been successfully completed</p>
        </div>
        
        <div class="confirmation-details">
            <div class="booking-summary">
                <h2>Booking Summary</h2>
                <div class="summary-item">
                    <strong>Booking ID:</strong> #<?php echo $booking['booking_id']; ?>
                </div>
                <div class="summary-item">
                    <strong>Hotel:</strong> <?php echo htmlspecialchars($booking['hotel_name']); ?>
                </div>
                <div class="summary-item">
                    <strong>Address:</strong> <?php echo htmlspecialchars($booking['address'] . ', ' . $booking['city'] . ', ' . $booking['country']); ?>
                </div>
                <div class="summary-item">
                    <strong>Check-in:</strong> <?php echo date('F j, Y', strtotime($booking['check_in_date'])); ?>
                </div>
                <div class="summary-item">
                    <strong>Check-out:</strong> <?php echo date('F j, Y', strtotime($booking['check_out_date'])); ?>
                </div>
                <div class="summary-item">
                    <strong>Guests:</strong> <?php echo $booking['total_guests']; ?>
                </div>
                <div class="summary-item">
                    <strong>Total Amount:</strong> $<?php echo number_format($booking['total_amount'], 2); ?>
                </div>
                <div class="summary-item">
                    <strong>Status:</strong> 
                    <span class="status-badge confirmed"><?php echo ucfirst($booking['booking_status']); ?></span>
                </div>
            </div>
            
            <div class="guest-info">
                <h2>Guest Information</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone_number'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <div class="confirmation-actions">
            <a href="profile.php" class="btn btn-primary">View My Bookings</a>
            <a href="index.php" class="btn btn-outline">Back to Home</a>
            <button onclick="window.print()" class="btn btn-secondary">Print Confirmation</button>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>