<?php
// simple_test.php - Basic PHP test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";

// Test basic PHP functionality
$test_var = "Hello World";
echo "Test variable: " . $test_var . "<br>";

// Test if we can connect to database (remove this if you don't have DB credentials yet)
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "Database connection successful!<br>";
} catch(Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}

echo "Script completed successfully!";
?>