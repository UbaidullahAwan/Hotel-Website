<?php
include 'config/database.php';
include 'includes/header.php';

$destination = $_GET['destination'] ?? '';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = $_GET['guests'] ?? 2;
$page = $_GET['page'] ?? 1;
$hotel_types = $_GET['hotel_type'] ?? [];
$ratings = $_GET['rating'] ?? [];
$sort_by = $_GET['sort_by'] ?? 'price_asc';
$layout = $_GET['layout'] ?? 'grid'; // grid or list

$limit = 12;
$offset = ($page - 1) * $limit;

// Search logic
$database = new Database();
$db = $database->getConnection();

// Build query with filters
$where_conditions = [];
$params = [];

// Basic destination filter
if (!empty($destination)) {
    $where_conditions[] = "(h.city LIKE :destination OR h.country LIKE :destination OR h.hotel_name LIKE :destination)";
    $params[':destination'] = "%$destination%";
}

// Hotel type filter
if (!empty($hotel_types)) {
    $placeholders = [];
    foreach ($hotel_types as $index => $type) {
        $param = ":hotel_type_$index";
        $placeholders[] = $param;
        $params[$param] = $type;
    }
    $where_conditions[] = "h.hotel_type IN (" . implode(',', $placeholders) . ")";
}

// Rating filter
if (!empty($ratings)) {
    $rating_conditions = [];
    foreach ($ratings as $index => $rating_value) {
        $rating_conditions[] = "(SELECT COALESCE(AVG(overall_rating), 0) FROM reviews WHERE hotel_id = h.hotel_id) >= $rating_value";
    }
    $where_conditions[] = "(" . implode(" OR ", $rating_conditions) . ")";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total results
$count_query = "SELECT COUNT(*) as total FROM hotels h $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_results = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Build order by clause
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'price_desc':
        $order_by .= "(SELECT MIN(final_price) FROM room_prices rp INNER JOIN room_types rt2 ON rp.room_type_id = rt2.room_type_id WHERE rt2.hotel_id = h.hotel_id) DESC";
        break;
    case 'rating_desc':
        $order_by .= "(SELECT COALESCE(AVG(overall_rating), 0) FROM reviews WHERE hotel_id = h.hotel_id) DESC";
        break;
    case 'name_asc':
        $order_by .= "h.hotel_name ASC";
        break;
    case 'name_desc':
        $order_by .= "h.hotel_name DESC";
        break;
    default: // price_asc
        $order_by .= "(SELECT MIN(final_price) FROM room_prices rp INNER JOIN room_types rt2 ON rp.room_type_id = rt2.room_type_id WHERE rt2.hotel_id = h.hotel_id) ASC";
        break;
}

// Main search query
$query = "SELECT 
            h.*,
            (SELECT MIN(final_price) FROM room_prices rp 
             INNER JOIN room_types rt ON rp.room_type_id = rt.room_type_id 
             WHERE rt.hotel_id = h.hotel_id) as min_price,
            (SELECT COALESCE(AVG(overall_rating), 0) FROM reviews WHERE hotel_id = h.hotel_id) as avg_rating,
            (SELECT COUNT(review_id) FROM reviews WHERE hotel_id = h.hotel_id) as review_count,
            (SELECT image_url FROM hotel_images WHERE hotel_id = h.hotel_id AND is_primary = 1 LIMIT 1) as primary_image
          FROM hotels h
          $where_clause
          $order_by
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_pages = ceil($total_results / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Search Results - Find Your Perfect Stay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --card-bg: #ffffff;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border-color: #e2e8f0;
            --rating-bg: #48bb78;
            --rating-text: #ffffff;
            --type-tag-bg: #667eea;
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f7fafc;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .search-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
        }
        
        /* Layout Controls */
        .layout-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        .layout-buttons {
            display: flex;
            gap: 10px;
        }

        .layout-btn {
            padding: 8px 16px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .layout-btn.active,
        .layout-btn:hover {
            background: #667eea;
            color: white;
        }

        /* Grid Layout */
        .grid-layout {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        /* List Layout - Compact Version */
        .list-layout {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 40px;
        }

        .list-layout .hotel-card {
            display: flex;
            flex-direction: row;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            padding: 0;
            min-height: 140px;
        }

        .list-layout .hotel-image {
            width: 200px;
            height: 205px;
            flex-shrink: 0;
            margin: 0;
        }

        .list-layout .hotel-content {
            flex: 1;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
        }

        .list-layout .hotel-main-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* Rating positioning for list layout */
        .list-layout .rating-box {
            width: 50%;
            position: static;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 8px;
            padding: 4px 10px;
            font-size: 14px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: var(--rating-text);
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(72, 187, 120, 0.3);
        }

        /* Compact hotel info */
        .list-layout .hotel-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .list-layout .hotel-location,
        .list-layout .hotel-reviews {
            font-size: 13px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--text-light);
        }

        .list-layout .location-icon {
            font-size: 12px;
            color: purple;
        }

        /* Actions section for list layout */
        .list-layout .hotel-actions {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
            min-width: 120px;
            align-self: stretch;
            display: flex;
            align-items: center;
        }

        .list-layout .view-details {

            padding: 8px 16px;
            font-size: 13px;
            width: 20%;
            margin-left: 450px;
        }

        /* Hotel Cards Common Styles */
        .hotel-card {
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
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
            background: var(--type-tag-bg);
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
            color: var(--rating-text);
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(72, 187, 120, 0.4);
            z-index: 2;
        }

        .hotel-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            margin-top: 0;
            line-height: 1.3;
        }

        .hotel-location {
            color: var(--text-light);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .location-icon {
            color: #e53e3e;
            font-size: 14px;
        }

        .hotel-reviews {
            color: var(--text-light);
            font-size: 14px;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .hotel-actions {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .view-details {
            color: white;
            text-decoration: none;
            font-weight: 500;
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

        /* Filters Sidebar */
        .filters-sidebar {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .filter-section {
            margin-bottom: 1.5rem;
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
            font-size: 1rem;
        }
        
        .filter-options {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .filter-checkbox {
            display: block;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        
        .filter-checkbox:hover {
            background-color: #e9d8fd;
        }
        
        .filter-checkbox input[type="checkbox"] {
            margin-right: 0.5rem;
            accent-color: #667eea;
        }
        
        .filter-checkbox label {
            cursor: pointer;
            margin-bottom: 0;
            font-size: 0.9rem;
            font-weight: 400;
        }
        
        .results-count {
            color: white;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .sort-dropdown .dropdown-toggle {
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-dark);
            font-size: 0.9rem;
            font-weight: 400;
        }
        
        .pagination .page-link {
            color: #667eea;
            border: 1px solid var(--border-color);
            font-weight: 400;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
            font-weight: 500;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            grid-column: 1 / -1;
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
            font-weight: 600;
        }

        .no-results-content p {
            color: #718096;
            margin-bottom: 10px;
            font-weight: 400;
        }

        .no-results-content a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .no-results-content a:hover {
            text-decoration: underline;
        }
        
        .clear-filters {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 1rem;
            font-weight: 500;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .grid-layout {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .list-layout .hotel-image {
                width: 180px;
            }
        }

        @media (max-width: 992px) {
            .list-layout .hotel-card {
                flex-direction: column;
            }
            
            .list-layout .hotel-image {
                width: 100%;
                height: 160px;
            }
            
            .list-layout .hotel-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .list-layout .hotel-actions {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .filters-sidebar {
                margin-bottom: 1.5rem;
            }
            
            .grid-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .layout-controls {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .layout-buttons {
                width: 100%;
            }
            
            .layout-btn {
                flex: 1;
                text-align: center;
                justify-content: center;
            }
            
            .list-layout .hotel-image {
                width: 100%;
                height: 140px;
            }
        }

        @media (max-width: 480px) {
            .search-header {
                padding: 2rem 0;
            }
            
            .search-header h1 {
                font-size: 1.5rem;
            }
            
            .list-layout .hotel-content {
                padding: 12px 15px;
            }
            
            .list-layout .hotel-name {
                font-size: 15px;
            }
            
            .list-layout .hotel-location,
            .list-layout .hotel-reviews {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Search Header -->
    <div class="search-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h2 mb-2" style="font-weight: 600;">
                        <?php if (!empty($destination)): ?>
                            Hotels in <?php echo htmlspecialchars($destination); ?>
                        <?php else: ?>
                            Find Your Perfect Stay
                        <?php endif; ?>
                    </h1>
                    <p class="mb-0 opacity-75" style="font-weight: 400;">
                        <?php if ($check_in && $check_out): ?>
                            <?php echo date('M j', strtotime($check_in)); ?> - <?php echo date('M j, Y', strtotime($check_out)); ?> • 
                        <?php endif; ?>
                        <?php echo $guests; ?> Guest<?php echo $guests > 1 ? 's' : ''; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="results-count mb-0">
                        <?php echo number_format($total_results); ?> hotels found
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="filters-sidebar">
                    <form method="GET" id="filter-form">
                        <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination); ?>">
                        <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                        <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                        <input type="hidden" name="guests" value="<?php echo htmlspecialchars($guests); ?>">
                        <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars($sort_by); ?>">
                        <input type="hidden" name="layout" value="<?php echo htmlspecialchars($layout); ?>">
                        
                        <!-- Star Rating Filter -->
                        <div class="filter-section">
                            <h6 class="filter-title">Star Rating</h6>
                            <div class="filter-options">
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="rating[]" value="5" 
                                        <?php echo in_array('5', $ratings) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>5 Stars</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="rating[]" value="4" 
                                        <?php echo in_array('4', $ratings) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>4+ Stars</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="rating[]" value="3" 
                                        <?php echo in_array('3', $ratings) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>3+ Stars</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="rating[]" value="2" 
                                        <?php echo in_array('2', $ratings) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>2+ Stars</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="rating[]" value="1" 
                                        <?php echo in_array('1', $ratings) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>1+ Stars</label>
                                </div>
                            </div>
                        </div>

                        <!-- Hotel Types Filter -->
                        <div class="filter-section">
                            <h6 class="filter-title">Hotel Type</h6>
                            <div class="filter-options">
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="5-star" 
                                        <?php echo in_array('5-star', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>5 Star Hotels</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="4-star" 
                                        <?php echo in_array('4-star', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>4 Star Hotels</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="3-star" 
                                        <?php echo in_array('3-star', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>3 Star Hotels</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="2-star" 
                                        <?php echo in_array('2-star', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>2 Star Hotels</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="1-star" 
                                        <?php echo in_array('1-star', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>1 Star Hotels</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="Resort" 
                                        <?php echo in_array('Resort', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>Resorts</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="Villa" 
                                        <?php echo in_array('Villa', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>Villas</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="Apartment" 
                                        <?php echo in_array('Apartment', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>Apartments</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="Boutique" 
                                        <?php echo in_array('Boutique', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>Boutique Hotels</label>
                                </div>
                                <div class="filter-checkbox">
                                    <input type="checkbox" name="hotel_type[]" value="Budget" 
                                        <?php echo in_array('Budget', $hotel_types) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filter-form').submit()">
                                    <label>Budget Hotels</label>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($hotel_types) || !empty($ratings)): ?>
                            <a href="?destination=<?php echo urlencode($destination); ?>&check_in=<?php echo $check_in; ?>&check_out=<?php echo $check_out; ?>&guests=<?php echo $guests; ?>&layout=<?php echo $layout; ?>" 
                               class="clear-filters">
                                <i class="fas fa-times"></i> Clear All Filters
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Hotels Grid -->
            <div class="col-lg-9">
                <!-- Layout and Sort Controls -->
                <div class="layout-controls">
                    <div class="sort-dropdown">
                        <div class="dropdown">
                            <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Sort by: 
                                <?php 
                                $sort_labels = [
                                    'price_asc' => 'Price: Low to High',
                                    'price_desc' => 'Price: High to Low',
                                    'rating_desc' => 'Highest Rated',
                                    'name_asc' => 'Name: A to Z',
                                    'name_desc' => 'Name: Z to A'
                                ];
                                echo $sort_labels[$sort_by];
                                ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item <?php echo $sort_by == 'price_asc' ? 'active' : ''; ?>" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'price_asc'])); ?>">Price: Low to High</a></li>
                                <li><a class="dropdown-item <?php echo $sort_by == 'price_desc' ? 'active' : ''; ?>" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'price_desc'])); ?>">Price: High to Low</a></li>
                                <li><a class="dropdown-item <?php echo $sort_by == 'rating_desc' ? 'active' : ''; ?>" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'rating_desc'])); ?>">Highest Rated</a></li>
                                <li><a class="dropdown-item <?php echo $sort_by == 'name_asc' ? 'active' : ''; ?>" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'name_asc'])); ?>">Name: A to Z</a></li>
                                <li><a class="dropdown-item <?php echo $sort_by == 'name_desc' ? 'active' : ''; ?>" 
                                       href="?<?php echo http_build_query(array_merge($_GET, ['sort_by' => 'name_desc'])); ?>">Name: Z to A</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="layout-buttons">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['layout' => 'grid'])); ?>" 
                           class="layout-btn <?php echo $layout == 'grid' ? 'active' : ''; ?>" data-layout="grid">
                            <i class="fas fa-th"></i> Grid
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['layout' => 'list'])); ?>" 
                           class="layout-btn <?php echo $layout == 'list' ? 'active' : ''; ?>" data-layout="list">
                            <i class="fas fa-list"></i> List
                        </a>
                    </div>
                </div>

                <?php if (count($hotels) > 0): ?>
                    <div class="<?php echo $layout; ?>-layout">
                        <?php foreach ($hotels as $hotel): ?>
                            <?php
                            $hotel_name = $hotel['hotel_name'];
                            $location = $hotel['city'] . ', ' . $hotel['country'];
                            $star_rating = floatval($hotel['star_rating'] ?? $hotel['avg_rating'] ?? 0);
                            $hotel_type = $hotel['hotel_type'];
                            $image_url = $hotel['primary_image'] ?: "https://via.placeholder.com/400x250/667eea/ffffff?text=" . urlencode(substr($hotel_name, 0, 2));
                            
                            // Format hotel type for display
                            $hotel_type_display = str_replace('-star', ' Star', $hotel_type);
                            $hotel_type_display = ucwords($hotel_type_display);
                            
                            $view_details_url = "http://localhost/Findahotell/single_hotel.php?id=" . $hotel['hotel_id'] . "&check_in=" . $check_in . "&check_out=" . $check_out . "&guests=" . $guests;
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
                                    <?php if ($layout == 'grid'): ?>
                                        <!-- Grid Layout Content -->
                                        <div class="rating-box">
                                            <?php echo number_format($star_rating, 1); ?>
                                        </div>
                                        
                                        <h3 class="hotel-name"><?php echo htmlspecialchars($hotel_name); ?></h3>
                                        
                                        <div class="hotel-location">
                                            <i class="fas fa-map-marker-alt location-icon" style= "color: purple;"></i> <?php echo htmlspecialchars($location); ?>
                                        </div>
                                        
                                        <div class="hotel-reviews">
                                            <i class="far fa-comment" style= "color: purple;"></i> 
                                            <?php 
                                            $review_count = $hotel['review_count'] ?? 0;
                                            if ($review_count > 0) {
                                                echo $review_count . ' review' . ($review_count > 1 ? 's' : '');
                                            } else {
                                                echo 'No reviews';
                                            }
                                            ?>
                                        </div>
                                        
                                        <div class="hotel-actions">
                                            <a href="<?php echo $view_details_url; ?>" class="view-details">
                                                View Details
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <!-- List Layout Content - Compact -->
                                        <div class="hotel-main-info">
                                            <div class="rating-box">
                                                <i class="fas fa-star" style="font-size: 10px;"></i>
                                                <?php echo number_format($star_rating, 1); ?>
                                            </div>
                                            
                                            <h3 class="hotel-name"><?php echo htmlspecialchars($hotel_name); ?></h3>
                                            
                                            <div class="hotel-location">
                                                <i class="fas fa-map-marker-alt location-icon"></i> 
                                                <?php echo htmlspecialchars($location); ?>
                                            </div>
                                            
                                            <div class="hotel-reviews">
                                                <i class="far fa-comment"></i> 
                                                <?php 
                                                $review_count = $hotel['review_count'] ?? 0;
                                                if ($review_count > 0) {
                                                    echo $review_count . ' review' . ($review_count > 1 ? 's' : '');
                                                } else {
                                                    echo 'No reviews';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <div class="hotel-actions">
                                            <a href="<?php echo $view_details_url; ?>" class="view-details">
                                                View Details
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- No Results Found -->
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
    </div>

    <!-- Bootstrap & JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include 'includes/footer.php'; ?>