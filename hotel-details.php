<?php
// hotel-details.php - Show complete hotel information
require_once 'config/database.php';

$hotel_id = $_GET['id'] ?? 0;

// Get hotel details with images and amenities
$stmt = $pdo->prepare("
    SELECT h.*, 
           GROUP_CONCAT(DISTINCT hi.image_url) as images,
           GROUP_CONCAT(DISTINCT ha.amenity_name) as amenities
    FROM hotels h 
    LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id 
    LEFT JOIN hotel_amenity_mapping ham ON h.hotel_id = ham.hotel_id 
    LEFT JOIN hotel_amenities ha ON ham.amenity_id = ha.amenity_id 
    WHERE h.hotel_id = ?
    GROUP BY h.hotel_id
");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

// Get available room types
$rooms_stmt = $pdo->prepare("
    SELECT rt.*, COUNT(r.room_id) as available_rooms 
    FROM room_types rt 
    LEFT JOIN rooms r ON rt.room_type_id = r.room_type_id AND r.is_available = 1 
    WHERE rt.hotel_id = ? 
    GROUP BY rt.room_type_id
");
$rooms_stmt->execute([$hotel_id]);
$room_types = $rooms_stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($hotel['hotel_name']) ?> - FindAHotel</title>
    <style>
        .hotel-images { display: flex; gap: 10px; margin: 20px 0; }
        .hotel-images img { width: 200px; height: 150px; object-fit: cover; }
        .room-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <a href="index.php">← Back to Home</a>
    
    <h1><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
    <p>⭐ <?= $hotel['star_rating'] ?> Stars • <?= $hotel['hotel_type'] ?></p>
    <p>📍 <?= htmlspecialchars($hotel['address']) ?>, <?= htmlspecialchars($hotel['city']) ?>, <?= htmlspecialchars($hotel['country']) ?></p>
    
    <!-- Hotel Images -->
    <div class="hotel-images">
        <?php if ($hotel['images']): 
            $images = explode(',', $hotel['images']);
            foreach ($images as $image): ?>
            <img src="<?= htmlspecialchars($image) ?>" alt="Hotel Image">
        <?php endforeach; endif; ?>
    </div>
    
    <!-- Hotel Description -->
    <h3>About This Hotel</h3>
    <p><?= nl2br(htmlspecialchars($hotel['description'])) ?></p>
    
    <!-- Amenities -->
    <?php if ($hotel['amenities']): ?>
    <h3>Amenities</h3>
    <p><?= htmlspecialchars($hotel['amenities']) ?></p>
    <?php endif; ?>
    
    <!-- Available Rooms -->
    <h3>Available Rooms</h3>
    <?php foreach ($room_types as $room): ?>
    <div class="room-card">
        <h4><?= htmlspecialchars($room['room_name']) ?></h4>
        <p>💰 $<?= $room['base_price'] ?> per night</p>
        <p>🛏️ <?= $room['bed_type'] ?> • 👥 Max <?= $room['max_guests'] ?> guests</p>
        <p>📏 Room Size: <?= $room['room_size'] ?></p>
        <p>✅ <?= $room['available_rooms'] ?> rooms available</p>
        <a href="booking.php?room_type_id=<?= $room['room_type_id'] ?>" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            Book Now
        </a>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($room_types)): ?>
        <p>No rooms available at the moment.</p>
    <?php endif; ?>
</body>
</html>