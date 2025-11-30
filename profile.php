<?php
include 'config/database.php';
include 'includes/auth.php';
requireLogin();

$user_id = getCurrentUserId();
$success = '';
$error = '';

// Get user data
$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    
    $update_query = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                    phone_number = :phone, country = :country, date_of_birth = :dob,
                    updated_at = NOW() WHERE user_id = :user_id";
    
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':first_name', $first_name);
    $update_stmt->bindParam(':last_name', $last_name);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':country', $country);
    $update_stmt->bindParam(':dob', $date_of_birth);
    $update_stmt->bindParam(':user_id', $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $success = "Profile updated successfully!";
        // Refresh user data
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update profile";
    }
}

// Get user's bookings and reviews (same as before)
$bookings_query = "SELECT b.*, h.hotel_name, h.city, h.country, h.hotel_id,
                   DATEDIFF(b.check_out_date, b.check_in_date) as nights
                   FROM bookings b 
                   JOIN hotels h ON b.hotel_id = h.hotel_id 
                   WHERE b.user_id = :user_id 
                   ORDER BY b.created_at DESC 
                   LIMIT 10";
$bookings_stmt = $db->prepare($bookings_query);
$bookings_stmt->bindParam(':user_id', $user_id);
$bookings_stmt->execute();
$bookings = $bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

$reviews_query = "SELECT r.*, h.hotel_name 
                  FROM reviews r 
                  JOIN hotels h ON r.hotel_id = h.hotel_id 
                  WHERE r.user_id = :user_id 
                  ORDER BY r.created_at DESC 
                  LIMIT 5";
$reviews_stmt = $db->prepare($reviews_query);
$reviews_stmt->bindParam(':user_id', $user_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

// Country list with phone codes
$countries = [
    'US' => ['name' => 'United States', 'code' => '+1'],
    'CA' => ['name' => 'Canada', 'code' => '+1'],
    'GB' => ['name' => 'United Kingdom', 'code' => '+44'],
    'AU' => ['name' => 'Australia', 'code' => '+61'],
    'IN' => ['name' => 'India', 'code' => '+91'],
    'DE' => ['name' => 'Germany', 'code' => '+49'],
    'FR' => ['name' => 'France', 'code' => '+33'],
    'IT' => ['name' => 'Italy', 'code' => '+39'],
    'ES' => ['name' => 'Spain', 'code' => '+34'],
    'BR' => ['name' => 'Brazil', 'code' => '+55'],
    'CN' => ['name' => 'China', 'code' => '+86'],
    'JP' => ['name' => 'Japan', 'code' => '+81'],
    'KR' => ['name' => 'South Korea', 'code' => '+82'],
    'SG' => ['name' => 'Singapore', 'code' => '+65'],
    'MY' => ['name' => 'Malaysia', 'code' => '+60'],
    'ID' => ['name' => 'Indonesia', 'code' => '+62'],
    'TH' => ['name' => 'Thailand', 'code' => '+66'],
    'VN' => ['name' => 'Vietnam', 'code' => '+84'],
    'PH' => ['name' => 'Philippines', 'code' => '+63'],
    'AE' => ['name' => 'United Arab Emirates', 'code' => '+971'],
    'SA' => ['name' => 'Saudi Arabia', 'code' => '+966'],
    'ZA' => ['name' => 'South Africa', 'code' => '+27'],
    'NG' => ['name' => 'Nigeria', 'code' => '+234'],
    'EG' => ['name' => 'Egypt', 'code' => '+20'],
    'KE' => ['name' => 'Kenya', 'code' => '+254'],
    'MX' => ['name' => 'Mexico', 'code' => '+52'],
    'AR' => ['name' => 'Argentina', 'code' => '+54'],
    'CL' => ['name' => 'Chile', 'code' => '+56'],
    'CO' => ['name' => 'Colombia', 'code' => '+57'],
    'PE' => ['name' => 'Peru', 'code' => '+51'],
    'NL' => ['name' => 'Netherlands', 'code' => '+31'],
    'BE' => ['name' => 'Belgium', 'code' => '+32'],
    'SE' => ['name' => 'Sweden', 'code' => '+46'],
    'NO' => ['name' => 'Norway', 'code' => '+47'],
    'DK' => ['name' => 'Denmark', 'code' => '+45'],
    'FI' => ['name' => 'Finland', 'code' => '+358'],
    'CH' => ['name' => 'Switzerland', 'code' => '+41'],
    'AT' => ['name' => 'Austria', 'code' => '+43'],
    'PT' => ['name' => 'Portugal', 'code' => '+351'],
    'GR' => ['name' => 'Greece', 'code' => '+30'],
    'IE' => ['name' => 'Ireland', 'code' => '+353'],
    'PL' => ['name' => 'Poland', 'code' => '+48'],
    'CZ' => ['name' => 'Czech Republic', 'code' => '+420'],
    'HU' => ['name' => 'Hungary', 'code' => '+36'],
    'RO' => ['name' => 'Romania', 'code' => '+40'],
    'RU' => ['name' => 'Russia', 'code' => '+7'],
    'TR' => ['name' => 'Turkey', 'code' => '+90'],
    'IL' => ['name' => 'Israel', 'code' => '+972'],
    'PK' => ['name' => 'Pakistan', 'code' => '+92'],
    'BD' => ['name' => 'Bangladesh', 'code' => '+880'],
    'LK' => ['name' => 'Sri Lanka', 'code' => '+94'],
    'NP' => ['name' => 'Nepal', 'code' => '+977'],
    'MM' => ['name' => 'Myanmar', 'code' => '+95'],
    'KH' => ['name' => 'Cambodia', 'code' => '+855'],
    'LA' => ['name' => 'Laos', 'code' => '+856'],
];

// Extract current phone number and code
$current_phone = $user['phone_number'] ?? '';
$current_country_code = '+1'; // Default
$current_phone_number = $current_phone;

if (!empty($current_phone)) {
    foreach ($countries as $country_code => $country_data) {
        if (strpos($current_phone, $country_data['code']) === 0) {
            $current_country_code = $country_data['code'];
            $current_phone_number = substr($current_phone, strlen($country_data['code']));
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Findahotell</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6b46c1, #805ad5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1.5rem;
        }

        .user-info h3 {
            text-align: center;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }

        .user-info p {
            text-align: center;
            color: #718096;
            margin-bottom: 2rem;
        }

        .profile-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-item {
            padding: 0.8rem 1rem;
            text-decoration: none;
            color: #4a5568;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav-item:hover, .nav-item.active {
            background: #6b46c1;
            color: white;
        }

        .profile-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f7fafc;
        }

        .section-header h2 {
            color: #2d3748;
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .phone-input-group {
            display: flex;
            gap: 0.5rem;
        }

        .country-code-select {
            flex: 0 0 120px;
        }

        .phone-number-input {
            flex: 1;
        }

        .country-code-select select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .booking-card, .review-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .booking-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .booking-hotel {
            flex: 1;
        }

        .booking-hotel h4 {
            margin: 0 0 0.5rem 0;
            color: #2d3748;
        }

        .booking-location {
            color: #718096;
            margin: 0;
        }

        .booking-status {
            text-align: right;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-confirmed {
            background: #c6f6d5;
            color: #276749;
        }

        .status-pending {
            background: #fefcbf;
            color: #744210;
        }

        .status-cancelled {
            background: #fed7d7;
            color: #c53030;
        }

        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #718096;
            margin-bottom: 0.2rem;
        }

        .detail-value {
            font-weight: 600;
            color: #2d3748;
        }

        .booking-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .review-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .review-hotel {
            flex: 1;
        }

        .review-hotel h4 {
            margin: 0 0 0.5rem 0;
            color: #2d3748;
        }

        .review-rating {
            color: #f6ad55;
        }

        .review-text {
            color: #4a5568;
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e0;
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                position: static;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .phone-input-group {
                flex-direction: column;
            }
            
            .country-code-select {
                flex: 1;
            }
            
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .booking-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .booking-status {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="profile-container">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            
            <nav class="profile-nav">
                <a href="#personal-info" class="nav-item active">Personal Information</a>
                <a href="#bookings" class="nav-item">My Bookings</a>
                <a href="#reviews" class="nav-item">My Reviews</a>
                <a href="logout.php" class="nav-item">Logout</a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="profile-content">
            <!-- Personal Info Tab -->
            <div id="personal-info" class="tab-content active">
                <div class="section-header">
                    <h2>Personal Information</h2>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>First Name *</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name *</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small style="color: #718096; font-size: 0.8rem;">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number</label>
                            <div class="phone-input-group">
                                <div class="country-code-select">
                                    <select name="country_code" id="country_code">
                                        <?php foreach ($countries as $code => $country_data): ?>
                                            <option value="<?php echo $country_data['code']; ?>" 
                                                <?php echo $current_country_code == $country_data['code'] ? 'selected' : ''; ?>>
                                                <?php echo $country_data['code']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="phone-number-input">
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($current_phone_number); ?>" 
                                           placeholder="Phone number">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Country</label>
                            <select name="country">
                                <option value="">Select Country</option>
                                <?php foreach ($countries as $code => $country_data): ?>
                                    <option value="<?php echo $code; ?>" 
                                        <?php echo ($user['country'] ?? '') == $code ? 'selected' : ''; ?>>
                                        <?php echo $country_data['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
            
            <!-- My Bookings Tab -->
            <div id="bookings" class="tab-content">
                <div class="section-header">
                    <h2>My Bookings</h2>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Bookings Yet</h3>
                        <p>You haven't made any bookings yet. Start exploring hotels!</p>
                        <a href="index.php" class="btn btn-primary" style="margin-top: 1rem;">Find Hotels</a>
                    </div>
                <?php else: ?>
                    <div class="bookings-list">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <div class="booking-hotel">
                                        <h4><?php echo htmlspecialchars($booking['hotel_name']); ?></h4>
                                        <p class="booking-location"><?php echo htmlspecialchars($booking['city'] . ', ' . $booking['country']); ?></p>
                                    </div>
                                    <div class="booking-status">
                                        <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                        <p style="margin: 0.5rem 0 0 0; font-weight: 600; color: #2d3748;">
                                            $<?php echo number_format($booking['total_amount'], 2); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="booking-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Check-in</span>
                                        <span class="detail-value"><?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Check-out</span>
                                        <span class="detail-value"><?php echo date('M j, Y', strtotime($booking['check_out_date'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Guests</span>
                                        <span class="detail-value"><?php echo $booking['total_guests']; ?> guest<?php echo $booking['total_guests'] > 1 ? 's' : ''; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Duration</span>
                                        <span class="detail-value"><?php echo $booking['nights']; ?> night<?php echo $booking['nights'] > 1 ? 's' : ''; ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-actions">
                                    <a href="booking-details.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-outline">View Details</a>
                                    <?php if ($booking['booking_status'] === 'confirmed'): ?>
                                        <a href="hotel-details.php?id=<?php echo $booking['hotel_id']; ?>" class="btn btn-primary">Book Again</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- My Reviews Tab -->
            <div id="reviews" class="tab-content">
                <div class="section-header">
                    <h2>My Reviews</h2>
                </div>
                
                <?php if (empty($reviews)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <h3>No Reviews Yet</h3>
                        <p>You haven't written any reviews yet. Share your experience after your stay!</p>
                    </div>
                <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-hotel">
                                        <h4><?php echo htmlspecialchars($review['hotel_name']); ?></h4>
                                        <div class="review-rating">
                                            <?php 
                                            $fullStars = floor($review['overall_rating']);
                                            for ($i = 0; $i < 5; $i++) {
                                                if ($i < $fullStars) {
                                                    echo '<i class="fas fa-star"></i>';
                                                } else {
                                                    echo '<i class="far fa-star"></i>';
                                                }
                                            }
                                            ?>
                                            <span style="margin-left: 0.5rem; color: #718096;"><?php echo number_format($review['overall_rating'], 1); ?>/5</span>
                                        </div>
                                    </div>
                                    <div style="color: #718096; font-size: 0.9rem;">
                                        <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($review['review_title'])): ?>
                                    <h5 style="margin: 0 0 0.5rem 0; color: #2d3748;"><?php echo htmlspecialchars($review['review_title']); ?></h5>
                                <?php endif; ?>
                                
                                <p class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Tab switching functionality
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked
                this.classList.add('active');
                const target = this.getAttribute('href');
                document.querySelector(target).classList.add('active');
            });
        });

        // Sync country selection with phone code
        document.querySelector('select[name="country"]').addEventListener('change', function() {
            const countryCode = this.value;
            const countryCodeSelect = document.getElementById('country_code');
            
            if (countryCode && countryCodeSelect) {
                // Find the option with matching country code
                for (let option of countryCodeSelect.options) {
                    if (option.value === getCountryPhoneCode(countryCode)) {
                        option.selected = true;
                        break;
                    }
                }
            }
        });

        // Helper function to get phone code by country code
        function getCountryPhoneCode(countryCode) {
            const countryPhoneCodes = {
                'US': '+1', 'CA': '+1', 'GB': '+44', 'AU': '+61', 'IN': '+91',
                'DE': '+49', 'FR': '+33', 'IT': '+39', 'ES': '+34', 'BR': '+55',
                'CN': '+86', 'JP': '+81', 'KR': '+82', 'SG': '+65', 'MY': '+60',
                'ID': '+62', 'TH': '+66', 'VN': '+84', 'PH': '+63', 'AE': '+971',
                'SA': '+966', 'ZA': '+27', 'NG': '+234', 'EG': '+20', 'KE': '+254',
                'MX': '+52', 'AR': '+54', 'CL': '+56', 'CO': '+57', 'PE': '+51',
                'NL': '+31', 'BE': '+32', 'SE': '+46', 'NO': '+47', 'DK': '+45',
                'FI': '+358', 'CH': '+41', 'AT': '+43', 'PT': '+351', 'GR': '+30',
                'IE': '+353', 'PL': '+48', 'CZ': '+420', 'HU': '+36', 'RO': '+40',
                'RU': '+7', 'TR': '+90', 'IL': '+972', 'PK': '+92', 'BD': '+880',
                'LK': '+94', 'NP': '+977', 'MM': '+95', 'KH': '+855', 'LA': '+856'
            };
            
            return countryPhoneCodes[countryCode] || '+1';
        }
    </script>
</body>
</html>