<?php
// submit_inquiry.php
header('Content-Type: application/json');

// Database configuration - UPDATE THESE CREDENTIALS
$host = 'localhost';
$dbname = 'Findahotell';
$username = 'root'; // Default XAMPP username
$password = '';     // Default XAMPP password (empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from the request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate required fields
    $required = ['name', 'email', 'phone', 'city', 'country', 'postcode', 'hotel_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }
    
    try {
        // Insert into hotel_inquiries table
        $stmt = $pdo->prepare("
            INSERT INTO hotel_inquiries 
            (hotel_id, room_type_name, check_in_date, check_out_date, 
             adults_count, children_count, total_guests, name, email, phone, 
             city, country, postcode, message, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['hotel_id'],
            $data['room_type'] ?? 'Standard Room',
            $data['check_in'] ?? date('Y-m-d'),
            $data['check_out'] ?? date('Y-m-d', strtotime('+1 day')),
            $data['adults_count'] ?? 1,
            $data['children_count'] ?? 0,
            $data['total_guests'] ?? 1,
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['city'],
            $data['country'],
            $data['postcode'],
            $data['message'] ?? '',
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $inquiryId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Inquiry submitted successfully',
            'inquiry_id' => $inquiryId
        ]);
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
        
        $inquiryId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Inquiry submitted successfully',
            'inquiry_id' => $inquiryId
        ]);
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>