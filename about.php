<?php
// about-us.php
session_start();
require_once 'config/database.php';

// Initialize database if needed for any dynamic content
$database = new Database();
$pdo = $database->getConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - FindAHotel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.7;
            color: #333;
            background: #f8f9fa;
        }
        
        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        
        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            margin-bottom: 60px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.1);
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-section .tagline {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.95;
            font-weight: 300;
        }
        
        /* Content Sections */
        .content-section {
            background: white;
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 15px auto;
            border-radius: 2px;
        }
        
        .intro-text {
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 40px;
            color: #555;
            line-height: 1.8;
        }
        
        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }
        
        .feature-card {
            background: #f8f9fa;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
        }
        
        .feature-card h3 {
            font-size: 1.4rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        /* Mission Section */
        .mission-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 60px;
            border-radius: 20px;
            text-align: center;
            margin: 60px 0;
        }
        
        .mission-section h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .mission-text {
            font-size: 1.3rem;
            line-height: 1.8;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.95;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 80px 40px;
            border-radius: 20px;
            text-align: center;
            margin-top: 60px;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .cta-text {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-button {
            display: inline-block;
            background: white;
            color: #4facfe;
            padding: 18px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 60px 0;
            text-align: center;
        }
        
        .stat-item {
            padding: 30px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .about-container {
                padding: 30px 15px;
            }
            
            .hero-section {
                padding: 60px 20px;
            }
            
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .hero-section .tagline {
                font-size: 1.2rem;
            }
            
            .content-section {
                padding: 40px 25px;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .mission-section, .cta-section {
                padding: 40px 25px;
            }
            
            .mission-section h2, .cta-section h2 {
                font-size: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .content-section {
                padding: 30px 20px;
            }
            
            .section-title {
                font-size: 1.6rem;
            }
            
            .intro-text {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include your header -->
    <?php include 'includes/header.php'; ?>

    <div class="about-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>About Us</h1>
                <p class="tagline">Making Travel Affordable, One Stay at a Time</p>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content-section">
            <h2 class="section-title">Our Story</h2>
            <p class="intro-text">
                At Find A Hotell we believe that everyone deserves a great place to stay—without overpaying for it. 
                That's why we created a more affordable way to book hotels, whether you're planning a luxurious getaway, 
                a last-minute weekend escape, or a quick business trip.
            </p>
            
            <p style="text-align: center; font-size: 1.1rem; color: #555; line-height: 1.8; margin-bottom: 30px;">
                We partner directly with hotels around the world to bring you exclusive discounted rates—the kind you won't find on other travel sites. 
                We don't display hotel rates on our site, that's because each rate is tailored to your individual needs - this is how we are able to 
                negotiate the best rates. We unlock prices usually reserved for industry insiders and loyal members.
            </p>
        </section>

        <!-- Why Choose Us Section -->
        <section class="content-section">
            <h2 class="section-title">💡 Why Choose Us?</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-icon">💰</span>
                    <h3>Exclusive Discounts</h3>
                    <p>Save up to 30% on hotels worldwide with our individually tailored, privately negotiated rates.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🏨</span>
                    <h3>Handpicked Stays</h3>
                    <p>We list quality accommodations that match your style and budget, ensuring perfect stays every time.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">⚡</span>
                    <h3>Simple Booking</h3>
                    <p>Fast, user-friendly booking process that gets you confirmed in minutes, not hours.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-icon">🔍</span>
                    <h3>No Hidden Fees</h3>
                    <p>The price you see is the price you pay. No surprises, no extra charges at checkout.</p>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-item">
                <div class="stat-number">30%</div>
                <div class="stat-label">Average Savings</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">1000+</div>
                <div class="stat-label">Hotel Partners</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50+</div>
                <div class="stat-label">Countries</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Support</div>
            </div>
        </div>

        <!-- Mission Section -->
        <section class="mission-section">
            <h2>Our Mission</h2>
            <p class="mission-text">
                We're not just a booking platform—we're travelers too. We know how frustrating it can be to search endlessly, 
                compare prices, and still wonder if you're overpaying. That's why we've made it our mission to cut through 
                the noise and deliver unbeatable value.
            </p>
        </section>

        <!-- Final CTA Section -->
        <section class="cta-section">
            <h2>Ready to Book?</h2>
            <p class="cta-text">
                Start exploring discounted hotel rates in your dream destinations now, simply fill out and submit our enquiry form and leave the rest to us.<br>
                The world is waiting—and it's more affordable than ever.
            </p>
            <a href="contact.php" class="cta-button">Start Your Journey</a>
        </section>
    </div>

    <!-- Include your footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate feature cards on scroll
            const featureCards = document.querySelectorAll('.feature-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            featureCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>