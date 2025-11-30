<?php
// create_database.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Setting up your database...</h2>";

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS findahotell");
    echo "✅ Database 'findahotell' created<br>";
    
    // Use the database
    $pdo->exec("USE findahotell");
    
    // Create countries table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS countries (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(3),
            flag_image VARCHAR(255),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Countries table created<br>";
    
    // Create hotels table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS hotels (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            country_id INT,
            description TEXT,
            price DECIMAL(10,2),
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Hotels table created<br>";
    
    // Add sample countries
    $pdo->exec("
        INSERT IGNORE INTO countries (name, code, description) VALUES 
        ('United States', 'US', 'Discover amazing hotels across the USA'),
        ('United Kingdom', 'UK', 'Explore beautiful hotels in the UK'),
        ('France', 'FR', 'Experience luxury hotels in France'),
        ('Italy', 'IT', 'Find romantic hotels in Italy'),
        ('Japan', 'JP', 'Discover traditional and modern hotels in Japan')
    ");
    echo "✅ Sample countries added<br>";
    
    echo "<div style='color: green; font-weight: bold; margin: 20px 0;'>🎉 Setup completed successfully!</div>";
    echo "<a href='index.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Website</a>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
?>