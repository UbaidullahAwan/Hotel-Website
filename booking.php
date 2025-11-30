<?php
// booking.php - Handle room bookings
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$room_type_id = $_GET['room_type_id'] ?? 0;

// Get room type details
$stmt = $pdo->prepare("
    SELECT rt.*, h.hotel_name, h.hotel_id 
    FROM room_types rt 
    JOIN hotels h ON rt.hotel_id = h.hotel_id 
    WHERE rt.room_type_id = ?
");
$stmt->execute([$room_type_id]);
$room_type = $stmt->fetch();

if (!$room_type) {
    die("Room type not found");
}

// Process booking form
if ($_POST) {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];
    $special_requests = $_POST['special_requests'];
    
    // Calculate total amount (simple calculation)
    $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
    $total_amount = $nights * $room_type['base_price'];
    
    // Create booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, hotel_id, check_in_date, check_out_date, total_guests, total_amount, booking_status, payment_status, special_requests) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Pending', ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $room_type['hotel_id'], $check_in, $check_out, $guests, $total_amount, $special_requests]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Redirect to payment page
    header("Location: payment.php?booking_id=$booking_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book <?= htmlspecialchars($room_type['room_name']) ?> - FindAHotel</title>
</head>
<body>
    <h1>Book <?= htmlspecialchars($room_type['room_name']) ?></h1>
    <p>at <?= htmlspecialchars($room_type['hotel_name']) ?></p>
    
    <form method="POST">
        <div>
            <label>Check-in Date:</label>
            <input type="date" name="check_in" required min="<?= date('Y-m-d') ?>">
        </div>
        <div>
            <label>Check-out Date:</label>
            <input type="date" name="check_out" required>
        </div>
        <div>
            <label>Number of Guests:</label>
            <select name="guests" required>
                <?php for ($i = 1; $i <= $room_type['max_guests']; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label>Special Requests:</label>
            <textarea name="special_requests" placeholder="Any special requirements..."></textarea>
        </div>
        <div>
            <button type="submit">Continue to Payment</button>
        </div>
    </form>
</body>
</html>