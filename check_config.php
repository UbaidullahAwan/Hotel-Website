<?php
// test-simple.php - Put this in your root directory, not components folder
echo "<h1>Simple Test</h1>";

$conn = new mysqli("localhost", "root", "", "Findahotell");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM hotels");
echo "<p>Hotels found: " . $result->num_rows . "</p>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div style='border:1px solid red; padding:10px; margin:5px;'>";
        echo "ID: " . $row['id'] . " | ";
        echo "Stars: " . ($row['star_rating'] ?? 'N/A');
        echo "</div>";
    }
} else {
    echo "NO HOTELS FOUND";
}

$conn->close();
?>