<?php
// search.php
session_start();
include 'includes/header.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Findahotell";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search parameters from URL
$country = isset($_GET['country']) ? $_GET['country'] : '';
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$hotel_type = isset($_GET['hotel_type']) ? $_GET['hotel_type'] : '';

// Build the SQL query based on search parameters
$sql = "SELECT h.hotel_id, h.hotel_name, h.city, h.country, h.star_rating, h.hotel_type, hi.image_url 
        FROM hotels h 
        LEFT JOIN hotel_images hi ON h.hotel_id = hi.hotel_id AND hi.is_primary = 1
        WHERE 1=1";

$params = [];
$types = '';

// Add filters based on search parameters
if (!empty($country)) {
    $sql .= " AND h.country = ?";
    $params[] = $country;
    $types .= 's';
}

if (!empty($destination)) {
    $sql .= " AND h.city = ?";
    $params[] = $destination;
    $types .= 's';
}

if (!empty($hotel_type)) {
    $sql .= " AND h.hotel_type = ?";
    $params[] = $hotel_type;
    $types .= 's';
}

$sql .= " ORDER BY h.star_rating DESC, h.hotel_name ASC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count 5-star hotels in the search city
$five_star_count = 0;
if (!empty($destination)) {
    $count_sql = "SELECT COUNT(*) as count FROM hotels WHERE city = ? AND star_rating = 5";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param('s', $destination);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $five_star_count = $count_result->fetch_assoc()['count'];
    $count_stmt->close();
}
?>

<!-- Search Results Section -->
<section class="search-results">
    <div class="container">
        <div class="results-header">
            <h1>Available Hotels</h1>
            
            <!-- Search Summary -->
            <div class="search-summary">
                <?php if (!empty($country) || !empty($destination) || !empty($hotel_type)): ?>
                    <div class="filters-applied">
                        <p>
                            <?php
                            $filters = [];
                            if (!empty($country)) $filters[] = "<strong>" . htmlspecialchars($country) . "</strong>";
                            if (!empty($destination)) $filters[] = "<strong>" . htmlspecialchars($destination) . "</strong>";
                            if (!empty($hotel_type)) {
                                $type_display = str_replace('-star', ' Star', $hotel_type);
                                $filters[] = "<strong>" . htmlspecialchars(ucwords($type_display)) . "</strong>";
                            }
                            echo 'Search: ' . implode(' • ', $filters);
                            ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($destination) && $five_star_count > 0): ?>
                    <div class="five-star-info">
                        <p><i class="fas fa-star"></i> <strong><?php echo $five_star_count; ?> five-star hotels</strong> available in <?php echo htmlspecialchars($destination); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="results-count">
                    <p><?php echo $result->num_rows; ?> hotel(s) found matching your criteria</p>
                </div>
            </div>
        </div>

        <!-- Hotels Grid -->
        <div class="hotels-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php
                    $hotel_name = $row['hotel_name'];
                    $location = $row['city'] . ', ' . $row['country'];
                    $star_rating = floatval($row['star_rating']);
                    $hotel_type_display = str_replace('-star', ' Star', $row['hotel_type']);
                    $hotel_type_display = ucwords($hotel_type_display);
                    $image_url = $row['image_url'] ?: "https://via.placeholder.com/400x250/667eea/ffffff?text=" . urlencode(substr($hotel_name, 0, 2));
                    
                    $view_details_url = "http://localhost/Findahotell/single_hotel.php?id=" . $row['hotel_id'] . "&check_in=&check_out=&guests=2";
                    ?>
                    
                    <div class="hotel-card">
                        <!-- Hotel Image with Hotel Type Tag -->
                        <div class="hotel-image">
                            <img src="<?php echo $image_url; ?>" 
                                 alt="<?php echo htmlspecialchars($hotel_name); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x250/667eea/ffffff?text=<?php echo urlencode(substr($hotel_name, 0, 2)); ?>'">
                            <div class="hotel-type-tag"><?php echo $hotel_type_display; ?></div>
                        </div>
                        
                        <div class="hotel-content">
                            <div class="hotel-main-info">
                                <!-- Rating Box -->
                                <div class="rating-box">
                                    <?php echo number_format($star_rating, 1); ?>
                                </div>
                                
                                <h3 class="hotel-name"><?php echo htmlspecialchars($hotel_name); ?></h3>
                                
                                <div class="hotel-location">
                                    <i class="fas fa-map-marker-alt location-icon"></i> <?php echo htmlspecialchars($location); ?>
                                </div>
                                
                                <div class="hotel-reviews">
                                    <i class="far fa-comment"></i> No reviews
                                </div>
                            </div>
                            
                            <div class="hotel-actions">
                                <a href="<?php echo $view_details_url; ?>" class="view-details">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-content">
                        <i class="fas fa-search fa-3x"></i>
                        <h3>No Hotels Found</h3>
                        <p>We couldn't find any hotels matching your search criteria.</p>
                        <p>Try adjusting your filters or <a href="index.php">browse all hotels</a>.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    /* Search Results Section */
    .search-results {
        padding: 60px 20px;
        background: #f7fafc;
        min-height: 80vh;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .results-header {
        margin-bottom: 40px;
    }

    .results-header h1 {
        font-size: 2.5rem;
        color: #2d3748;
        margin-bottom: 20px;
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        text-align: center;
    }

    .search-summary {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .filters-applied {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .filters-applied p {
        margin: 0;
        color: #4a5568;
        font-size: 1.1rem;
        font-weight: 500;
    }

    .filters-applied strong {
        color: #667eea;
        background: #e9d8fd;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
    }

    .five-star-info {
        margin-bottom: 15px;
        padding: 12px 16px;
        background: linear-gradient(135deg, #fed7d7 0%, #feebc8 100%);
        border-radius: 8px;
        border-left: 4px solid #e53e3e;
    }

    .five-star-info p {
        margin: 0;
        color: #744210;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .five-star-info i {
        color: #e53e3e;
    }

    .five-star-info strong {
        color: #744210;
    }

    .results-count {
        text-align: center;
        padding: 10px;
        background: #667eea;
        color: white;
        border-radius: 8px;
        font-weight: 600;
    }

    .results-count p {
        margin: 0;
        font-size: 1rem;
    }

    /* Hotels Grid - Same as hotel-card.php */
    .hotels-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
    }

    .hotel-card {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        position: relative;
    }

    .hotel-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .hotel-image {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
    }

    .hotel-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .hotel-card:hover .hotel-image img {
        transform: scale(1.05);
    }

    .hotel-type-tag {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #667eea;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .hotel-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
        position: relative;
    }

    .rating-box {
        position: absolute;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(72, 187, 120, 0.4);
        z-index: 2;
    }

    .hotel-name {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
        margin-top: 0;
        line-height: 1.3;
    }

    .hotel-location {
        color: #718096;
        font-size: 14px;
        margin-bottom: 8px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .location-icon {
        color: #e53e3e;
        font-size: 14px;
    }

    .hotel-reviews {
        color: #718096;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hotel-actions {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }

    .view-details {
        color: white;
        text-decoration: none;
        font-weight: 700;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        white-space: nowrap;
        text-align: center;
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        font-size: 14px;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        width: 100%;
    }

    .view-details:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    /* No Results */
    .no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
    }

    .no-results-content {
        background: white;
        padding: 60px 40px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 2px dashed #e2e8f0;
    }

    .no-results-content i {
        color: #a0aec0;
        margin-bottom: 20px;
    }

    .no-results-content h3 {
        color: #2d3748;
        margin-bottom: 15px;
        font-size: 1.5rem;
    }

    .no-results-content p {
        color: #718096;
        margin-bottom: 10px;
    }

    .no-results-content a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }

    .no-results-content a:hover {
        text-decoration: underline;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .hotels-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .hotels-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .search-results {
            padding: 40px 15px;
        }
        
        .results-header h1 {
            font-size: 2rem;
        }
        
        .hotels-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .search-summary {
            padding: 20px;
        }
        
        .filters-applied p {
            font-size: 1rem;
        }
    }
</style>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>