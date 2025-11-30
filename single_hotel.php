<?php
include 'config/database.php';
include 'includes/header.php';

$hotel_id = $_GET['id'] ?? 0;
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 2;

// Fetch hotel data
$database = new Database();
$db = $database->getConnection();

// Get hotel main data
$hotel_query = "SELECT h.*, 
                (SELECT image_url FROM hotel_images WHERE hotel_id = h.hotel_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM hotels h WHERE h.hotel_id = ?";
$hotel_stmt = $db->prepare($hotel_query);
$hotel_stmt->execute([$hotel_id]);
$hotel = $hotel_stmt->fetch(PDO::FETCH_ASSOC);

if (!$hotel) {
    echo "Hotel not found";
    include 'includes/footer.php';
    exit;
}

// Get hotel images for gallery
$images_query = "SELECT image_url FROM hotel_images WHERE hotel_id = ?";
$images_stmt = $db->prepare($images_query);
$images_stmt->execute([$hotel_id]);
$hotel_images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get room types
$rooms_query = "SELECT rt.* FROM room_types rt WHERE rt.hotel_id = ?";
$rooms_stmt = $db->prepare($rooms_query);
$rooms_stmt->execute([$hotel_id]);
$room_types = $rooms_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get room amenities for each room type and assign images
foreach ($room_types as &$room) {
    // Get amenities
    $amenities_query = "SELECT ra.amenity_name 
                       FROM room_amenity_mapping ram 
                       JOIN room_amenities ra ON ram.room_amenity_id = ra.room_amenity_id 
                       WHERE ram.room_type_id = ?";
    $amenities_stmt = $db->prepare($amenities_query);
    $amenities_stmt->execute([$room['room_type_id']]);
    $room['amenities'] = $amenities_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get room image from hotel_images table (use first available image as fallback)
    $room_image_query = "SELECT image_url FROM hotel_images WHERE hotel_id = ? AND image_type = 'room' LIMIT 1";
    $room_image_stmt = $db->prepare($room_image_query);
    $room_image_stmt->execute([$hotel_id]);
    $room_image = $room_image_stmt->fetch(PDO::FETCH_ASSOC);
    
    $room['room_image'] = $room_image ? $room_image['image_url'] : $hotel['primary_image'];
}

// Function to format text with headings and lists
function formatHotelText($text) {
    if (empty($text)) return 'No information available.';
    
    // Convert line breaks
    $text = nl2br($text);
    
    // Format numbered lists
    $text = preg_replace('/(\d+\.)\s*(.+)/', '<li>$2</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ol>$1</ol>', $text);
    
    // Format bullet points
    $text = preg_replace('/\*\s*(.+)/', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    
    // Format headings
    $text = preg_replace('/(\d+\.\s*[A-Z][^\.]+\.)/', '<strong>$1</strong><br>', $text);
    $text = preg_replace('/([A-Z][A-Z\s]+\:)/', '<strong>$1</strong><br>', $text);
    
    return $text;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['hotel_name']); ?> - Hotel Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
            font-size: 12px;
        }
        
        .hotel-header {
            background: #fff;
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .hotel-title-section {
            flex: 1;
        }
        
        .breadcrumb-section {
            flex: 1;
            text-align: right;
        }
        
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 11px;
        }
        
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        
        .hotel-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .hotel-location {
            color: #666;
            font-size: 11px;
            margin-bottom: 0;
        }
        
        .hotel-type-badge {
            background: linear-gradient(135deg, #8B5FBF, #6B46C1);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
            font-size: 11px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 11px;
        }
        
        .sidebar-box {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .sidebar-title {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #2c3e50;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
        }
        
        .date-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 8px 0;
            font-size: 11px;
        }
        
        .date-label {
            font-weight: 600;
            color: #555;
        }
        
        .date-value {
            color: #333;
        }
        
        .btn-enquire {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
            margin: 12px 0;
            font-size: 11px;
        }
        
        .btn-enquire:hover {
            background: #c82333;
            color: white;
        }
        
        .btn-view-images {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            margin-top: 12px;
            font-size: 11px;
        }
        
        .btn-view-images:hover {
            background: #218838;
            color: white;
        }
        
        .address-section {
            margin: 12px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            font-size: 11px;
        }
        
        .address-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin: 20px 0 15px 0;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #666;
            font-weight: 500;
            padding: 10px 15px;
            font-size: 11px;
        }
        
        .nav-tabs .nav-link.active {
            border: none;
            border-bottom: 3px solid #007bff;
            color: #007bff;
            background: none;
        }
        
        .tab-content {
            padding: 15px 0;
            font-size: 11px;
        }
        
        .tab-content strong {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .tab-content ol, .tab-content ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .tab-content li {
            margin-bottom: 5px;
        }
        
        .room-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            font-size: 11px;
        }
        
        .room-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 12px;
        }
        
        .room-title {
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .room-meta {
            color: #666;
            margin-bottom: 12px;
            font-size: 11px;
        }
        
        .room-amenities {
            margin: 12px 0;
        }
        
        .amenity-badge {
            background: #e9ecef;
            color: #495057;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin: 0 3px 3px 0;
            display: inline-block;
        }
        
        .btn-enquire-room {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 8px;
            font-size: 11px;
        }
        
        .btn-enquire-room:hover {
            background: #c82333;
            color: white;
        }
        
        .room-unavailable {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            margin-top: 12px;
            font-size: 11px;
        }
        
        .hotel-featured-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .main-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-top: 15px;
            font-size: 11px;
        }
        
        .rooms-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin: 20px 0;
            font-size: 11px;
        }
        
        .rooms-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
        }
        
        /* Image Gallery Modal */
        .gallery-modal .modal-dialog {
            max-width: 90%;
            max-height: 90vh;
        }
        
        .gallery-modal .modal-content {
            background: #000;
        }
        
        .gallery-modal .modal-body {
            padding: 0;
            text-align: center;
        }
        
        .gallery-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 16px;
            z-index: 1000;
        }
        
        .gallery-nav.prev {
            left: 15px;
        }
        
        .gallery-nav.next {
            right: 15px;
        }
        
        .gallery-nav:hover {
            background: rgba(0,0,0,0.9);
        }
        
        .gallery-counter {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0,0,0,0.7);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
        }
        
        .image-container {
            margin-top: 15px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .breadcrumb-section {
                text-align: center;
                margin-top: 10px;
            }
            
            .hotel-featured-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Hotel Header -->
    <div class="hotel-header">
        <div class="container">
            <div class="header-content">
                <div class="hotel-title-section">
                    <div class="hotel-type-badge">
                        <?php echo htmlspecialchars($hotel['hotel_type']); ?>
                    </div>
                    <h1 class="hotel-title"><?php echo htmlspecialchars($hotel['hotel_name']); ?></h1>
                    <p class="hotel-location">
                        <i class="fas fa-map-marker-alt"></i> 
                        <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?>
                    </p>
                </div>
                <div class="breadcrumb-section">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="search.php">Hotels</a></li>
                            <li class="breadcrumb-item"><a href="search.php?destination=<?php echo urlencode($hotel['country']); ?>"><?php echo htmlspecialchars($hotel['country']); ?></a></li>
                            <li class="breadcrumb-item"><a href="search.php?destination=<?php echo urlencode($hotel['city']); ?>"><?php echo htmlspecialchars($hotel['city']); ?></a></li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($hotel['hotel_name']); ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Main Content - Extended to col-lg-9 -->
            <div class="col-lg-9">
                <div class="main-content">
                    <!-- Featured Image with View Images Button -->
                    <div class="image-container">
                        <?php if (!empty($hotel['primary_image'])): ?>
                            <img src="<?php echo htmlspecialchars($hotel['primary_image']); ?>" 
                                 class="hotel-featured-image" 
                                 alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                        <?php else: ?>
                            <img src="images/default-hotel.jpg" 
                                 class="hotel-featured-image" 
                                 alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                        <?php endif; ?>
                        
                        <?php if (count($hotel_images) > 1): ?>
                            <button class="btn btn-view-images" data-bs-toggle="modal" data-bs-target="#imageGallery">
                                <i class="fas fa-images me-2"></i>View Photos (<?php echo count($hotel_images); ?>)
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="alert alert-warning">
                        <strong>Rates & Availability not available at the moment. BUT Don't miss out !</strong>
                    </div>
                    
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs" id="hotelTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">About</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="policies-tab" data-bs-toggle="tab" data-bs-target="#policies" type="button" role="tab">Policies</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms" type="button" role="tab">Terms & Conditions</button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="hotelTabsContent">
                        <!-- About Tab -->
                        <div class="tab-pane fade show active" id="about" role="tabpanel">
                            <?php echo formatHotelText($hotel['about_info'] ?? 'No description available.'); ?>
                        </div>

                        <!-- Policies Tab -->
                        <div class="tab-pane fade" id="policies" role="tabpanel">
                            <?php echo formatHotelText($hotel['policies'] ?? 'No policies available.'); ?>
                        </div>

                        <!-- Terms & Conditions Tab -->
                        <div class="tab-pane fade" id="terms" role="tabpanel">
                            <?php echo formatHotelText($hotel['terms_conditions'] ?? 'No terms and conditions available.'); ?>
                        </div>
                    </div>
                </div>

                <!-- Separate Rooms Container -->
                <div class="rooms-container">
                    <h2 class="rooms-title">Available Rooms</h2>
                    
                    <?php foreach ($room_types as $room): ?>
                        <div class="room-card">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php if (!empty($room['room_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($room['room_image']); ?>" 
                                             class="room-image" 
                                             alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                                    <?php else: ?>
                                        <img src="images/default-room.jpg" 
                                             class="room-image" 
                                             alt="<?php echo htmlspecialchars($room['room_name']); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="room-title"><?php echo htmlspecialchars($room['room_name']); ?></div>
                                    <div class="room-meta">
                                        <i class="fas fa-users me-1"></i> Max <?php echo $room['max_guests']; ?> guests · 
                                        <i class="fas fa-expand-arrows-alt me-1"></i> <?php echo $room['room_size']; ?> · 
                                        <i class="fas fa-bed me-1"></i> <?php echo $room['bed_type']; ?>
                                    </div>
                                    
                                    <?php if (!empty($room['amenities'])): ?>
                                        <div class="room-amenities">
                                            <?php foreach (array_slice($room['amenities'], 0, 6) as $amenity): ?>
                                                <span class="amenity-badge"><?php echo htmlspecialchars($amenity); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($room['amenities']) > 6): ?>
                                                <span class="amenity-badge">+<?php echo (count($room['amenities']) - 6); ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="text-muted"><?php echo htmlspecialchars($room['description']); ?></p>
                                    
                                    <a href="inquiry.php?hotel_id=<?php echo $hotel_id; ?>&room_type_id=<?php echo $room['room_type_id']; ?>" 
                                       class="btn btn-enquire-room">
                                        <i class="fas fa-envelope me-2"></i>Enquire Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($room_types)): ?>
                        <div class="room-card">
                            <div class="room-unavailable">
                                No room types available for this hotel.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Sidebar - Reduced to col-lg-3 -->
            <div class="col-lg-3">
                <!-- Enquire Now Button -->
                <div class="sidebar-box text-center">
                    <a href="inquiry.php?hotel_id=<?php echo $hotel_id; ?>" 
                       target="_blank" 
                       class="btn btn-enquire">
                        <i class="fas fa-envelope me-2"></i>Enquire Now
                    </a>
                </div>

                <!-- Hotel Address -->
                <div class="sidebar-box">
                    <div class="sidebar-title">Hotel Address</div>
                    <div class="address-section">
                        <div class="address-label">Address:</div>
                        <?php echo htmlspecialchars($hotel['address']); ?><br>
                        <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['country']); ?><br>
                        Postal Code: <?php echo htmlspecialchars($hotel['postal_code']); ?>
                    </div>
                </div>

                <!-- Check-in/Check-out Times -->
                <div class="sidebar-box">
                    <div class="sidebar-title">Check-in & Check-out</div>
                    <div class="date-info">
                        <div class="date-label">Check-in Time</div>
                        <div class="date-value">14:00 (2:00 PM)</div>
                    </div>
                    <div class="date-info">
                        <div class="date-label">Check-out Time</div>
                        <div class="date-value">12:00 (12:00 PM)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Gallery Modal -->
    <div class="modal fade gallery-modal" id="imageGallery" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative">
                    <img id="galleryCurrentImage" class="gallery-image" src="" alt="">
                    <div class="gallery-counter" id="galleryCounter">1 / <?php echo count($hotel_images); ?></div>
                    
                    <?php if (count($hotel_images) > 1): ?>
                        <button class="gallery-nav prev" onclick="changeImage(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="gallery-nav next" onclick="changeImage(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentImageIndex = 0;
        const hotelImages = <?php echo json_encode($hotel_images); ?>;
        
        function openGallery(initialIndex = 0) {
            currentImageIndex = initialIndex;
            updateGalleryImage();
        }
        
        function changeImage(direction) {
            currentImageIndex += direction;
            if (currentImageIndex < 0) {
                currentImageIndex = hotelImages.length - 1;
            } else if (currentImageIndex >= hotelImages.length) {
                currentImageIndex = 0;
            }
            updateGalleryImage();
        }
        
        function updateGalleryImage() {
            const imageElement = document.getElementById('galleryCurrentImage');
            const counterElement = document.getElementById('galleryCounter');
            
            if (hotelImages[currentImageIndex]) {
                imageElement.src = hotelImages[currentImageIndex].image_url;
                counterElement.textContent = (currentImageIndex + 1) + ' / ' + hotelImages.length;
            }
        }
        
        // Initialize gallery when modal opens
        document.getElementById('imageGallery').addEventListener('show.bs.modal', function () {
            openGallery(0);
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', function(event) {
            const galleryModal = document.getElementById('imageGallery');
            if (galleryModal.classList.contains('show')) {
                if (event.key === 'ArrowLeft') {
                    changeImage(-1);
                } else if (event.key === 'ArrowRight') {
                    changeImage(1);
                } else if (event.key === 'Escape') {
                    bootstrap.Modal.getInstance(galleryModal).hide();
                }
            }
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>