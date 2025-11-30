<?php
$page_title = "Cookies Policy - Find a Hotell";
$page_description = "Learn how Find a Hotell uses cookies and similar technologies to enhance your experience.";
require_once 'includes/header.php';
?>

<!-- Main Content -->
<main class="cookies-policy-page">
    <!-- Hero Section -->
    <section class="cookies-hero">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">
                    <i class="fas fa-cookie-bite"></i>
                    Cookies Policy
                </h1>
                <p class="hero-subtitle">Understanding how we use cookies to enhance your booking experience</p>
                <div class="last-updated">
                    <i class="fas fa-clock"></i>
                    Last updated: <?php echo date('F j, Y'); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Cookies Policy Content -->
    <section class="cookies-content">
        <div class="container">
            <div class="policy-wrapper">
                <!-- Introduction -->
                <div class="policy-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h2>Introduction</h2>
                    </div>
                    <div class="section-content">
                        <p>This Cookies Policy explains how Find a Hotell ("we", "us", and "our") uses cookies and similar technologies when you visit or use our hotel booking platform, including our website and mobile applications (collectively, the "Platform").</p>
                    </div>
                </div>

                <!-- What Are Cookies? -->
                <div class="policy-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h2>1. What Are Cookies?</h2>
                    </div>
                    <div class="section-content">
                        <div class="cookies-explanation">
                            <div class="explanation-card">
                                <div class="explanation-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="explanation-text">
                                    <h3>Small Text Files</h3>
                                    <p>Cookies are small text files that are stored on your device (computer, smartphone, or other internet-enabled device) when you visit a website.</p>
                                </div>
                            </div>
                            <div class="explanation-card">
                                <div class="explanation-icon">
                                    <i class="fas fa-magic"></i>
                                </div>
                                <div class="explanation-text">
                                    <h3>Enhanced Experience</h3>
                                    <p>They help websites function effectively and enhance user experiences by remembering preferences and actions over time.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Why We Use Cookies -->
                <div class="policy-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h2>2. Why We Use Cookies</h2>
                    </div>
                    <div class="section-content">
                        <div class="cookies-purposes">
                            <div class="purpose-category">
                                <div class="purpose-header essential">
                                    <i class="fas fa-shield-alt"></i>
                                    <h3>Essential Cookies</h3>
                                </div>
                                <p>To ensure our Platform functions properly, including navigation, secure login, and booking management.</p>
                            </div>

                            <div class="purpose-category">
                                <div class="purpose-header analytics">
                                    <i class="fas fa-chart-line"></i>
                                    <h3>Performance and Analytics Cookies</h3>
                                </div>
                                <p>To understand how users interact with our Platform, improve performance, and optimize user experience (e.g., Google Analytics).</p>
                            </div>

                            <div class="purpose-category">
                                <div class="purpose-header functionality">
                                    <i class="fas fa-cog"></i>
                                    <h3>Functionality Cookies</h3>
                                </div>
                                <p>To remember your preferences (such as language or location) and provide a more personalized experience.</p>
                            </div>

                            <div class="purpose-category">
                                <div class="purpose-header advertising">
                                    <i class="fas fa-ad"></i>
                                    <h3>Advertising and Marketing Cookies</h3>
                                </div>
                                <p>To deliver relevant ads to you on and off our Platform, and to measure the effectiveness of our marketing campaigns.</p>
                            </div>

                            <div class="purpose-category">
                                <div class="purpose-header third-party">
                                    <i class="fas fa-handshake"></i>
                                    <h3>Third-Party Cookies</h3>
                                </div>
                                <p>Some cookies are set by third-party services we use, such as payment processors, social media platforms, or travel partners.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Types of Cookies We Use -->
                <div class="policy-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-list-alt"></i>
                        </div>
                        <h2>3. Types of Cookies We Use</h2>
                    </div>
                    <div class="section-content">
                        <div class="cookies-table-container">
                            <table class="cookies-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-tag"></i> Type</th>
                                        <th><i class="fas fa-bullseye"></i> Purpose</th>
                                        <th><i class="fas fa-clock"></i> Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="cookie-type-essential">
                                        <td>
                                            <i class="fas fa-shield-alt"></i>
                                            <strong>Strictly Necessary</strong>
                                        </td>
                                        <td>Booking processing, authentication, security</td>
                                        <td><span class="duration-badge">Session-only</span></td>
                                    </tr>
                                    <tr class="cookie-type-preferences">
                                        <td>
                                            <i class="fas fa-user-cog"></i>
                                            <strong>Preferences</strong>
                                        </td>
                                        <td>Language settings, saved hotels</td>
                                        <td><span class="duration-badge">Up to 1 year</span></td>
                                    </tr>
                                    <tr class="cookie-type-analytics">
                                        <td>
                                            <i class="fas fa-chart-bar"></i>
                                            <strong>Analytics</strong>
                                        </td>
                                        <td>Track site usage and performance (e.g., Google Analytics)</td>
                                        <td><span class="duration-badge">Up to 2 years</span></td>
                                    </tr>
                                    <tr class="cookie-type-advertising">
                                        <td>
                                            <i class="fas fa-ad"></i>
                                            <strong>Advertising</strong>
                                        </td>
                                        <td>Display personalized ads (e.g., via Facebook Pixel)</td>
                                        <td><span class="duration-badge">Up to 2 years</span></td>
                                    </tr>
                                    <tr class="cookie-type-social">
                                        <td>
                                            <i class="fas fa-share-alt"></i>
                                            <strong>Social Media</strong>
                                        </td>
                                        <td>Share content via social networks (e.g., LinkedIn, Twitter)</td>
                                        <td><span class="duration-badge">Varies</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Managing Your Cookies -->
                <div class="policy-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <h2>4. Managing Your Cookies</h2>
                    </div>
                    <div class="section-content">
                        <div class="management-info">
                            <p>You can control or disable cookies through your browser settings. Most browsers allow you to:</p>
                            
                            <div class="management-features">
                                <div class="feature-item">
                                    <i class="fas fa-eye"></i>
                                    <span>View which cookies are stored</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Delete cookies</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-ban"></i>
                                    <span>Block third-party cookies or all cookies</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Set preferences for certain websites</span>
                                </div>
                            </div>

                            <div class="important-note">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p><strong>Please note:</strong> Disabling cookies may affect the functionality and features of our Platform.</p>
                            </div>

                            <div class="browser-guides">
                                <h3><i class="fas fa-window-restore"></i> Manage cookies from popular browsers:</h3>
                                <div class="browser-buttons">
                                    <a href="https://support.google.com/chrome/answer/95647" target="_blank" class="browser-btn chrome">
                                        <i class="fab fa-chrome"></i>
                                        Chrome
                                    </a>
                                    <a href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences" target="_blank" class="browser-btn firefox">
                                        <i class="fab fa-firefox-browser"></i>
                                        Firefox
                                    </a>
                                    <a href="https://support.apple.com/guide/safari/manage-cookies-and-website-data-sfri11471/mac" target="_blank" class="browser-btn safari">
                                        <i class="fab fa-safari"></i>
                                        Safari
                                    </a>
                                    <a href="https://support.microsoft.com/en-us/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" class="browser-btn edge">
                                        <i class="fab fa-edge"></i>
                                        Edge
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cookie Consent Banner -->
                <div class="policy-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-cookie"></i>
                        </div>
                        <h2>Cookie Consent</h2>
                    </div>
                    <div class="section-content">
                        <div class="consent-info">
                            <div class="consent-visual">
                                <div class="cookie-illustration">
                                    <i class="fas fa-cookie-bite"></i>
                                </div>
                                <div class="consent-text">
                                    <h3>Your Control Matters</h3>
                                    <p>We respect your privacy and provide you with control over your cookie preferences. You can adjust your settings at any time through our cookie consent manager or your browser settings.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Updates to This Policy -->
                <div class="policy-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h2>5. Updates to This Policy</h2>
                    </div>
                    <div class="section-content">
                        <p>We may update this Cookies Policy from time to time to reflect changes in our technology, legal obligations, or business practices. When we update this Policy, we will revise the "Last Updated" date above and, where required, notify you.</p>
                    </div>
                </div>

                <!-- Contact Section -->
                <div class="policy-section contact-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h2>6. Contact Us</h2>
                    </div>
                    <div class="section-content text-center">
                        <p>If you have any questions about our use of cookies, please contact us at:</p>
                        <div class="contact-info">
                            <a href="mailto:info@findahotell.com" class="contact-email">
                                <i class="fas fa-envelope"></i>
                                Info@findahotell.com
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    /* Cookies Policy Specific Styles - Purple Theme */
    .cookies-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 0 60px;
        text-align: center;
    }

    .hero-title {
        font-family: 'Playfair Display', serif;
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .hero-title i {
        margin-right: 15px;
        color: #ffd700;
    }

    .hero-subtitle {
        font-size: 1.3rem;
        opacity: 0.9;
        margin-bottom: 1.5rem;
    }

    .last-updated {
        background: rgba(255, 255, 255, 0.2);
        padding: 10px 20px;
        border-radius: 25px;
        display: inline-block;
        font-size: 0.9rem;
    }

    .last-updated i {
        margin-right: 8px;
    }

    .cookies-content {
        padding: 60px 0;
        background: #f8f9fa;
    }

    .policy-wrapper {
        max-width: 1000px;
        margin: 0 auto;
    }

    .policy-section {
        background: white;
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        border-left: 5px solid #667eea;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .policy-section:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f4;
    }

    .section-icon {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 1.5rem;
    }

    .section-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: #2d3748;
        margin: 0;
    }

    .section-content {
        color: #4a5568;
        line-height: 1.8;
    }

    /* Cookies Explanation */
    .cookies-explanation {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .explanation-card {
        display: flex;
        align-items: flex-start;
        background: #f8f9fa;
        padding: 25px;
        border-radius: 12px;
        border-left: 4px solid #667eea;
    }

    .explanation-icon {
        background: #667eea;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .explanation-text h3 {
        color: #2d3748;
        margin-bottom: 8px;
        font-size: 1.2rem;
    }

    /* Cookies Purposes */
    .cookies-purposes {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-top: 20px;
    }

    .purpose-category {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }

    .purpose-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .purpose-header i {
        font-size: 1.5rem;
        margin-right: 12px;
        width: 30px;
        color: #667eea;
    }

    .purpose-header h3 {
        margin: 0;
        color: #2d3748;
        font-size: 1.2rem;
    }

    /* Cookies Table */
    .cookies-table-container {
        overflow-x: auto;
        margin-top: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .cookies-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .cookies-table th {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
    }

    .cookies-table th i {
        margin-right: 8px;
    }

    .cookies-table td {
        padding: 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .cookies-table tr:hover {
        background: #f7fafc;
    }

    .cookie-type-essential td:first-child,
    .cookie-type-preferences td:first-child,
    .cookie-type-analytics td:first-child,
    .cookie-type-advertising td:first-child,
    .cookie-type-social td:first-child {
        border-left: 4px solid #667eea;
    }

    .cookies-table td i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
        color: #667eea;
    }

    .duration-badge {
        background: #edf2f7;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #4a5568;
    }

    /* Cookie Management */
    .management-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }

    .feature-item {
        display: flex;
        align-items: center;
        background: #f0f4ff;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .feature-item i {
        color: #667eea;
        margin-right: 12px;
        font-size: 1.2rem;
    }

    .important-note {
        background: #fffaf0;
        border: 1px solid #fed7d7;
        border-left: 4px solid #e53e3e;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        display: flex;
        align-items: flex-start;
    }

    .important-note i {
        color: #e53e3e;
        margin-right: 12px;
        font-size: 1.2rem;
        margin-top: 2px;
    }

    .browser-guides {
        margin-top: 30px;
    }

    .browser-guides h3 {
        margin-bottom: 15px;
        color: #2d3748;
    }

    .browser-guides h3 i {
        margin-right: 10px;
        color: #667eea;
    }

    .browser-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }

    .browser-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        text-decoration: none;
        color: #4a5568;
        transition: all 0.3s ease;
        text-align: center;
    }

    .browser-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
        color: #667eea;
    }

    .browser-btn i {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    /* Cookie Consent Section */
    .consent-info {
        text-align: center;
    }

    .consent-visual {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 30px;
        margin: 20px 0;
    }

    .cookie-illustration {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
    }

    .consent-text {
        text-align: left;
        max-width: 400px;
    }

    .consent-text h3 {
        color: #2d3748;
        margin-bottom: 10px;
        font-size: 1.5rem;
    }

    /* Contact Section */
    .contact-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
    }

    .contact-section .section-header {
        border-bottom-color: rgba(255, 255, 255, 0.3);
    }

    .contact-section .section-icon {
        background: rgba(255, 255, 255, 0.2);
    }

    .contact-email {
        display: inline-flex;
        align-items: center;
        background: white;
        color: #667eea;
        padding: 15px 30px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.2rem;
        margin-top: 20px;
        transition: all 0.3s ease;
    }

    .contact-email:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .contact-email i {
        margin-right: 10px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .policy-section {
            padding: 25px;
        }

        .section-header {
            flex-direction: column;
            text-align: center;
        }

        .section-icon {
            margin-right: 0;
            margin-bottom: 15px;
        }

        .cookies-explanation {
            grid-template-columns: 1fr;
        }

        .explanation-card {
            flex-direction: column;
            text-align: center;
        }

        .explanation-icon {
            margin-right: 0;
            margin-bottom: 15px;
        }

        .consent-visual {
            flex-direction: column;
            text-align: center;
        }

        .consent-text {
            text-align: center;
        }

        .browser-buttons {
            grid-template-columns: repeat(2, 1fr);
        }

        .cookies-table {
            font-size: 0.9rem;
        }

        .cookies-table th,
        .cookies-table td {
            padding: 10px;
        }
    }

    @media (max-width: 480px) {
        .browser-buttons {
            grid-template-columns: 1fr;
        }
        
        .management-features {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>