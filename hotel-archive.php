<?php
// hotels-archive.php
require_once 'config/Database.php';  // Changed path

$database = new Database();
$db = $database->getConnection();

$country_id = $_GET['country_id'] ?? null;

if (!$country_id) {
    header('Location: index.php');
    exit;
}

// Fetch country details
$query = "SELECT * FROM countries WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$country_id]);
$country = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$country) {
    header('Location: index.php');
    exit;
}

// Fetch hotels for this country
$query = "SELECT * FROM hotels WHERE country_id = ? ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute([$country_id]);
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels in <?php echo htmlspecialchars($country['name']); ?></title>
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .back-button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px; text-decoration: none; display: inline-block; }
        .hotels-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .hotel-card { border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white; }
        .hotel-image { width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #888; }
        .hotel-card-content { padding: 20px; }
        .no-hotels { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">← Back to Countries</a>
        
        <h1>Hotels in <?php echo htmlspecialchars($country['name']); ?></h1>
        
        <?php if (empty($hotels)): ?>
            <div class="no-hotels">
                <h3>No hotels found in <?php echo htmlspecialchars($country['name']); ?></h3>
                <p>Check back later or explore other countries.</p>
            </div>
        <?php else: ?>
            <div class="hotels-grid">
                <?php foreach ($hotels as $hotel): ?>
                    <div class="hotel-card">
                        <div class="hotel-image">
                            <?php if (!empty($hotel['image'])): ?>
                                <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <span>Hotel Image</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="hotel-card-content">
                            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                            <p><?php echo htmlspecialchars($hotel['description'] ?? 'Luxury accommodation'); ?></p>
                            <?php if (!empty($hotel['price'])): ?>
                                <div style="color: #667eea; font-weight: bold; margin: 10px 0;">$<?php echo number_format($hotel['price'], 2); ?> per night</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>