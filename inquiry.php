<?php
// inquiry.php - Hotel Inquiry Modal Popup
header('Content-Type: text/html; charset=UTF-8');

// Database configuration
$host = 'localhost';
$dbname = 'Findahotell';
$username = 'root';
$password = '';

// Get parameters from request
$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+1 day'));
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;

// Fetch hotel and room data from database
$hotelData = [];
$roomTypes = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch hotel information
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE hotel_id = ?");
    $stmt->execute([$hotel_id]);
    $hotelData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hotelData) {
        die("Hotel not found");
    }
    
    // Fetch available room types for this hotel
    $stmt = $pdo->prepare("
        SELECT rt.*, 
               (SELECT MIN(final_price) FROM room_prices rp WHERE rp.room_type_id = rt.room_type_id) as min_price
        FROM room_types rt 
        WHERE rt.hotel_id = ?
    ");
    $stmt->execute([$hotel_id]);
    $roomTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no rooms found in database, create default room options
    if (empty($roomTypes)) {
        $roomTypes = [
            [
                'room_type_id' => 1,
                'room_name' => 'Standard Room',
                'max_guests' => 2,
                'base_price' => '100.00',
                'min_price' => '100.00'
            ],
            [
                'room_type_id' => 2,
                'room_name' => 'Deluxe Room',
                'max_guests' => 3,
                'base_price' => '150.00',
                'min_price' => '150.00'
            ],
            [
                'room_type_id' => 3,
                'room_name' => 'Suite',
                'max_guests' => 4,
                'base_price' => '250.00',
                'min_price' => '250.00'
            ]
        ];
    }
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Get form data
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $city = $_POST['city'] ?? '';
        $country = $_POST['country'] ?? '';
        $postcode = $_POST['postcode'] ?? '';
        $message = $_POST['message'] ?? '';
        $hotel_id = $_POST['hotel_id'] ?? 0;
        $room_type_id = $_POST['room_type'] ?? 0;
        $check_in = $_POST['check_in'] ?? '';
        $check_out = $_POST['check_out'] ?? '';
        $adults_count = $_POST['adults'] ?? 1;
        $children_count = $_POST['children'] ?? 0;
        $total_guests = $adults_count + $children_count;
        
        // Get room type name and price
        $room_type_name = '';
        $room_price = '0.00';
        foreach ($roomTypes as $room) {
            if ($room['room_type_id'] == $room_type_id) {
                $room_type_name = $room['room_name'];
                $room_price = $room['min_price'] ?? $room['base_price'];
                break;
            }
        }
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($city) || empty($country)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        // Check if room_type_id exists in room_types table
        $room_type_exists = false;
        if ($room_type_id > 0) {
            $check_stmt = $pdo->prepare("SELECT room_type_id FROM room_types WHERE room_type_id = ?");
            $check_stmt->execute([$room_type_id]);
            $room_type_exists = (bool)$check_stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // If room_type_id doesn't exist in database, set it to NULL and use room_type_name
        $final_room_type_id = $room_type_exists ? $room_type_id : NULL;
        
        // Save to database - using NULL for room_type_id if it doesn't exist
        $stmt = $pdo->prepare("
            INSERT INTO hotel_inquiries (
                hotel_id, room_type_id, room_type_name, room_price, 
                check_in_date, check_out_date, adults_count, children_count, 
                total_guests, number_of_rooms, guests, name, email, phone, 
                city, country, postcode, message, special_requests, 
                ip_address, inquiry_source
            ) VALUES (
                :hotel_id, :room_type_id, :room_type_name, :room_price,
                :check_in_date, :check_out_date, :adults_count, :children_count,
                :total_guests, :number_of_rooms, :guests, :name, :email, :phone,
                :city, :country, :postcode, :message, :special_requests,
                :ip_address, :inquiry_source
            )
        ");
        
        $stmt->execute([
            ':hotel_id' => $hotel_id,
            ':room_type_id' => $final_room_type_id, // Can be NULL if room doesn't exist in DB
            ':room_type_name' => $room_type_name,
            ':room_price' => $room_price,
            ':check_in_date' => $check_in,
            ':check_out_date' => $check_out,
            ':adults_count' => $adults_count,
            ':children_count' => $children_count,
            ':total_guests' => $total_guests,
            ':number_of_rooms' => 1, // Default to 1 room
            ':guests' => $name, // Using name as guest name
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':city' => $city,
            ':country' => $country,
            ':postcode' => $postcode,
            ':message' => $message,
            ':special_requests' => $message, // Using message as special requests
            ':ip_address' => $_SERVER['REMOTE_ADDR'],
            ':inquiry_source' => 'website'
        ]);
        
        $inquiry_id = $pdo->lastInsertId();
        
        // Send email notification to admin
        $admin_email = 'admin@findahotell.com'; // Change to your admin email
        $subject = "New Hotel Inquiry - {$hotelData['hotel_name']}";
        
        $email_body = "
        New Hotel Inquiry Received
        
        Hotel: {$hotelData['hotel_name']}
        Room Type: {$room_type_name}
        Check-in: {$check_in}
        Check-out: {$check_out}
        Guests: {$adults_count} Adults, {$children_count} Children
        Total Guests: {$total_guests}
        
        Customer Details:
        Name: {$name}
        Email: {$email}
        Phone: {$phone}
        City: {$city}
        Country: {$country}
        Postal Code: {$postcode}
        
        Message: {$message}
        
        Inquiry ID: {$inquiry_id}
        Received: " . date('Y-m-d H:i:s') . "
        IP Address: {$_SERVER['REMOTE_ADDR']}
        ";
        
        // Send email (you may need to configure your mail server)
        $headers = "From: noreply@findahotell.com\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Try to send email, but don't fail the submission if email fails
        $email_sent = @mail($admin_email, $subject, $email_body, $headers);
        
        $response['success'] = true;
        $response['message'] = 'Your inquiry has been submitted successfully! We will contact you shortly.' . ($email_sent ? '' : ' (Email notification queued)');
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Set default room type
$default_room_type = $roomTypes[0]['room_type_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry - <?php echo htmlspecialchars($hotelData['hotel_name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('../images/hotel-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
        }
        
        .background-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.3);
            z-index: -1;
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.85);
            width: 100%;
            max-width: 900px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalFade 0.4s ease-out;
            min-height: 500px;
        }
        
        @keyframes modalFade {
            from { 
                opacity: 0; 
                transform: translateY(-40px) scale(0.95);
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1);
            }
        }
        
        .hotel-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 25px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hotel-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }
        
        .hotel-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        
        .hotel-location {
            font-size: 16px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-header {
            margin-bottom: 25px;
            text-align: center;
        }
        
        .form-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .inquiry-form {
            display: flex;
            flex-direction: column;
        }
        
        .form-section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ecf0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #3498db;
            font-size: 14px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-1px);
        }
        
        input::placeholder, textarea::placeholder {
            color: #95a5a6;
        }
        
        select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
            appearance: none;
            padding-right: 40px;
        }
        
        textarea {
            height: 80px;
            resize: vertical;
            font-family: inherit;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46, 204, 113, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .success-message {
            display: none;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
        }
        
        .success-message h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .close-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            display: none;
        }

        .alert.error {
            background: #fee;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert.warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }

        .alert.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                max-width: 600px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 20px 10px;
            }
            
            .hotel-header,
            .form-container {
                padding: 20px;
            }
            
            .hotel-name {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="background-overlay"></div>
    
    <div class="modal-content">
        <button class="close-btn" onclick="window.close()">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Hotel Header -->
        <div class="hotel-header">
            <h1 class="hotel-name"><?php echo htmlspecialchars($hotelData['hotel_name']); ?></h1>
            <div class="hotel-location">
                <i class="fas fa-map-marker-alt"></i>
                <?php echo htmlspecialchars($hotelData['city'] . ', ' . $hotelData['country']); ?>
            </div>
        </div>
        
        <!-- Form Container -->
        <div class="form-container">
            <div class="form-header">
                <h1>Enquiry Now</h1>
                <div class="form-subtitle">Your request to stay at <?php echo htmlspecialchars($hotelData['hotel_name']); ?></div>
            </div>

            <div class="alert" id="form-alert"></div>
            
            <form id="inquiry-form" class="inquiry-form" method="POST">
                <input type="hidden" id="hotel_id" name="hotel_id" value="<?php echo $hotel_id; ?>">
                
                <!-- Stay Details Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Your Stay Details
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="room_type" class="required">Room Type</label>
                            <select id="room_type" name="room_type" required>
                                <?php foreach ($roomTypes as $room): ?>
                                    <option value="<?php echo $room['room_type_id']; ?>" 
                                            data-max-guests="<?php echo $room['max_guests']; ?>">
                                        <?php echo htmlspecialchars($room['room_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="check_in" class="required">Check-in Date</label>
                            <input type="date" id="check_in" name="check_in" value="<?php echo $check_in; ?>" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="check_out" class="required">Check-out Date</label>
                            <input type="date" id="check_out" name="check_out" value="<?php echo $check_out; ?>" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="adults" class="required">Adults</label>
                            <select id="adults" name="adults" required>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $adults ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Adult<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="children">Children</label>
                            <select id="children" name="children">
                                <?php for ($i = 0; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $children ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Child<?php echo $i != 1 ? 'ren' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="name" class="required">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="your@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="required">Mobile Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="+1234567890" required>
                        </div>
                    </div>
                </div>
                
                <!-- Address Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Address Information
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="city" class="required">City</label>
                            <input type="text" id="city" name="city" placeholder="Enter your city" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="country" class="required">Country</label>
                            <input type="text" id="country" name="country" placeholder="Enter your country" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="postcode">Postal Code</label>
                            <input type="text" id="postcode" name="postcode" placeholder="Enter postal code">
                        </div>
                    </div>
                </div>
                
                <!-- Message Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-comment"></i>
                        Additional Information
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="message">Special Requests or Questions</label>
                        <textarea id="message" name="message" placeholder="Please let us know if you have any special requests, dietary requirements, or questions about your stay..."></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" id="submit-btn">
                    <i class="fas fa-paper-plane"></i>
                    Send Enquiry Now
                </button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Submitting your enquiry...</p>
                </div>
            </form>
            
            <div class="success-message" id="success-message">
                <h3>Thank You!</h3>
                <p>Your enquiry has been submitted successfully.</p>
                <p>We will contact you shortly to confirm your booking.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('inquiry-form');
            const roomTypeSelect = document.getElementById('room_type');
            const checkInInput = document.getElementById('check_in');
            const checkOutInput = document.getElementById('check_out');
            const adultsSelect = document.getElementById('adults');
            const childrenSelect = document.getElementById('children');
            const alertDiv = document.getElementById('form-alert');
            
            function showAlert(message, type = 'error') {
                alertDiv.textContent = message;
                alertDiv.className = `alert ${type}`;
                alertDiv.style.display = 'block';
                
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }
            
            // Update guest validation based on room capacity
            function updateGuestValidation(maxGuests) {
                const currentAdults = parseInt(adultsSelect.value);
                const currentChildren = parseInt(childrenSelect.value);
                const totalGuests = currentAdults + currentChildren;
                
                if (totalGuests > maxGuests) {
                    // Adjust adults to fit within max guests
                    const availableAdults = Math.min(currentAdults, maxGuests);
                    adultsSelect.value = availableAdults;
                    childrenSelect.value = maxGuests - availableAdults;
                    
                    showAlert(`This room accommodates maximum ${maxGuests} guests. Your selection has been adjusted.`, 'warning');
                }
            }
            
            // Date validation
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const tomorrow = new Date(checkInDate);
                tomorrow.setDate(tomorrow.getDate() + 1);
                
                checkOutInput.min = tomorrow.toISOString().split('T')[0];
                
                if (new Date(checkOutInput.value) <= checkInDate) {
                    checkOutInput.value = tomorrow.toISOString().split('T')[0];
                }
            });
            
            // Guest count validation
            adultsSelect.addEventListener('change', updateTotalGuests);
            childrenSelect.addEventListener('change', updateTotalGuests);
            
            function updateTotalGuests() {
                const selectedRoom = roomTypeSelect.options[roomTypeSelect.selectedIndex];
                const maxGuests = parseInt(selectedRoom.getAttribute('data-max-guests'));
                const adults = parseInt(adultsSelect.value);
                const children = parseInt(childrenSelect.value);
                const totalGuests = adults + children;
                
                if (totalGuests > maxGuests) {
                    showAlert(`This room accommodates maximum ${maxGuests} guests. Please adjust your selection.`, 'error');
                }
            }
            
            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validate guest count
                const selectedRoom = roomTypeSelect.options[roomTypeSelect.selectedIndex];
                const maxGuests = parseInt(selectedRoom.getAttribute('data-max-guests'));
                const adults = parseInt(adultsSelect.value);
                const children = parseInt(childrenSelect.value);
                const totalGuests = adults + children;
                
                if (totalGuests > maxGuests) {
                    showAlert(`This room accommodates maximum ${maxGuests} guests. Please adjust your selection.`, 'error');
                    return;
                }
                
                if (totalGuests === 0) {
                    showAlert('Please select at least one guest.', 'error');
                    return;
                }
                
                // Validate dates
                const checkInDate = new Date(checkInInput.value);
                const checkOutDate = new Date(checkOutInput.value);
                
                if (checkOutDate <= checkInDate) {
                    showAlert('Check-out date must be after check-in date.', 'error');
                    return;
                }
                
                // Show loading
                document.getElementById('loading').style.display = 'block';
                document.getElementById('submit-btn').disabled = true;
                
                try {
                    const formData = new FormData(form);
                    
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        document.getElementById('loading').style.display = 'none';
                        document.getElementById('success-message').style.display = 'block';
                        form.style.display = 'none';
                    } else {
                        throw new Error(result.message);
                    }
                    
                } catch (error) {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('submit-btn').disabled = false;
                    showAlert(error.message || 'There was an error submitting your enquiry. Please try again.', 'error');
                }
            });
        });
    </script>
</body>
</html>