<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Findahotell";

echo "<h2>Database Check</h2>";

// Test connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

echo "Connected to database successfully!<br><br>";

// Check if hotels table exists
$table_check = $conn->query("SHOW TABLES LIKE 'hotels'");

if ($table_check->num_rows > 0) {
    echo "Hotels table exists!<br>";
    
    // Count hotels
    $count_result = $conn->query("SELECT COUNT(*) as total FROM hotels");
    $count_row = $count_result->fetch_assoc();
    $hotel_count = $count_row['total'];
    
    echo "Number of hotels: " . $hotel_count . "<br><br>";
    
    if ($hotel_count > 0) {
        // Show hotel data
        $hotels = $conn->query("SELECT * FROM hotels");
        
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Location</th><th>Stars</th><th>Price</th></tr>";
        
        while($hotel = $hotels->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $hotel['id'] . "</td>";
            echo "<td>" . $hotel['name'] . "</td>";
            echo "<td>" . $hotel['location'] . "</td>";
            echo "<td>" . ($hotel['star_rating'] ?? 'N/A') . "</td>";
            echo "<td>$" . ($hotel['price'] ?? '0.00') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hotels found in the table.<br>";
        echo "To add sample data, run this SQL in phpMyAdmin:<br>";
        echo "<pre>";
        echo "INSERT INTO hotels (name, location, star_rating, price) VALUES \n";
        echo "('Amyaj Rotana', 'Muzaffarabad, Dubai', 5, 299.00)";
        echo "('Dusit Thani', 'Dubai, UAE', 5, 399.00),\n";
        echo "('Sofitel Dubai', 'Dubai, UAE', 5, 349.00);";
        echo "</pre>";
    }
    
} else {
    echo "Hotels table does NOT exist!<br><br>";
    echo "To create the table, run this SQL in phpMyAdmin:<br>";
    echo "<pre>";
    echo "CREATE TABLE hotels (\n";
    echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
    echo "    name VARCHAR(255) NOT NULL,\n";
    echo "    location VARCHAR(255) NOT NULL,\n";
    echo "    star_rating INT DEFAULT 5,\n";
    echo "    price DECIMAL(10,2) DEFAULT 0.00,\n";
    echo "    review_count INT DEFAULT 0\n";
    echo ");";
    echo "</pre>";
}

$conn->close();
?>