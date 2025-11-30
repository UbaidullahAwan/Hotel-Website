<?php
// index.php
session_start();
include 'includes/header.php';

// Database connection for dropdown data
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Findahotell";

$conn = new mysqli($servername, $username, $password, $dbname);

// Fetch countries from database
$countries_sql = "SELECT DISTINCT country FROM hotels WHERE country != '' ORDER BY country";
$countries_result = $conn->query($countries_sql);

// Fetch cities from database
$cities_sql = "SELECT DISTINCT city FROM hotels WHERE city != '' ORDER BY city";
$cities_result = $conn->query($cities_sql);

// Fetch hotel types from database
$types_sql = "SELECT DISTINCT hotel_type FROM hotels WHERE hotel_type != '' ORDER BY hotel_type";
$types_result = $conn->query($types_sql);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="search-container">
        <div class="search-header">
            <h1>Find Your Perfect Stay</h1>
            <p>Discover amazing hotels and resorts tailored to your preferences. Book with confidence and create unforgettable memories.</p>
        </div>
        
        <form action="search.php" method="GET" id="searchForm" class="search-form">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-globe"></i> Country</label>
                    <select name="country" id="country">
                        <option value="">Any Country</option>
                        <?php if ($countries_result && $countries_result->num_rows > 0): ?>
                            <?php while($country = $countries_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($country['country']); ?>">
                                    <?php echo htmlspecialchars($country['country']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> City</label>
                    <select name="destination" id="destination">
                        <option value="">Any City</option>
                        <?php if ($cities_result && $cities_result->num_rows > 0): ?>
                            <?php while($city = $cities_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($city['city']); ?>">
                                    <?php echo htmlspecialchars($city['city']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-hotel"></i> Hotel Type</label>
                    <select name="hotel_type" id="hotel_type">
                        <option value="">Any Type</option>
                        <?php if ($types_result && $types_result->num_rows > 0): ?>
                            <?php while($type = $types_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($type['hotel_type']); ?>">
                                    <?php 
                                    $type_display = str_replace('-star', ' Star', $type['hotel_type']);
                                    echo htmlspecialchars(ucwords($type_display)); 
                                    ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search Hotels
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php $conn->close(); ?>

<style>
    /* Hero Section */
    .hero {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%), 
                    url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        padding: 100px 20px;
    }

    .search-container {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }

    .search-header h1 {
        font-family: 'Poppins', sans-serif;
        font-size: 3.5rem;
        margin-bottom: 20px;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        line-height: 1.2;
    }

    .search-header p {
        font-size: 1.3rem;
        margin-bottom: 50px;
        opacity: 0.9;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
        font-weight: 400;
    }

    .search-form {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        width: 100%;
    }

    .form-row {
        display: flex;
        gap: 20px;
        align-items: end;
        justify-content: center;
        flex-wrap: wrap;
    }

    .form-group {
        text-align: left;
        flex: 1;
        min-width: 200px;
    }

    .form-group:last-child {
        flex: 0 0 auto;
        min-width: auto;
    }

    .form-group label {
        display: block;
        color: white;
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Poppins', sans-serif;
    }

    .form-group select {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        background: white;
        color: #333;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        font-family: 'Poppins', sans-serif;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'><path fill='%23333' d='M2 0L0 2h4zm0 5L0 3h4z'/></svg>");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 12px;
    }

    .form-group select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
    }

    .form-group select:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .search-btn {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        height: 51px;
        font-family: 'Poppins', sans-serif;
        white-space: nowrap;
        min-width: 180px;
    }

    .search-btn:hover {
        background: #ff5252;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(255, 107, 107, 0.5);
    }

    /* Success Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin: 20px auto;
        max-width: 1200px;
        text-align: center;
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .form-row {
            gap: 15px;
        }
        
        .form-group {
            min-width: 180px;
        }
    }

    @media (max-width: 1024px) {
        .form-row {
            grid-template-columns: repeat(2, 1fr);
            display: grid;
        }
        
        .form-group:last-child {
            grid-column: 1 / -1;
            justify-self: center;
            margin-top: 10px;
        }
        
        .search-btn {
            width: 100%;
            max-width: 300px;
        }
        
        .search-header h1 {
            font-size: 3rem;
        }
    }

    @media (max-width: 768px) {
        .search-header h1 {
            font-size: 2.5rem;
        }
        
        .search-header p {
            font-size: 1.1rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .form-group:last-child {
            grid-column: 1;
            margin-top: 10px;
        }
        
        .search-form {
            padding: 30px 20px;
        }

        .hero {
            min-height: 80vh;
            padding: 80px 20px;
        }
    }

    @media (max-width: 480px) {
        .search-header h1 {
            font-size: 2rem;
        }
        
        .hero {
            padding: 60px 15px;
        }
        
        .search-form {
            padding: 25px 15px;
        }
        
        .form-group select,
        .search-btn {
            padding: 12px 15px;
            font-size: 0.95rem;
        }
        
        .search-btn {
            min-width: 160px;
        }
    }
</style>

<script>
// Search form functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    
    searchForm.addEventListener('submit', function(e) {
        const country = document.getElementById('country').value;
        const destination = document.getElementById('destination').value;
        const hotelType = document.getElementById('hotel_type').value;
        
        console.log('Search submitted:', { country, destination, hotelType });
        return true;
    });
});

// Auto-hide success messages after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

<?php include 'includes/footer.php'; ?>