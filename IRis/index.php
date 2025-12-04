<?php
// index.php
// Homepage for Rocelyn RJ Building Trades Inc

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rocelyn RJ Building Trades Inc</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <script src="script.js" defer></script>
</head>
<body>

<!-- ================= HEADER / NAVBAR ================= -->
<header>
    <div class="navbar">
        <div class="LOGO">
            <img src="../Capstone pics/LOGO.jpg" alt="Company Logo">
            <div>
                <h2>Rocelyn RJ Building Trades Inc</h2>
                <p>Professional Construction Services</p>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#portfolio">Portfolio</a></li>
                <li><a href="#about">About/Contacts</a></li>
            </ul>
        </nav>
       <a href="login.php" class="login-btn">Login</a>
    </div>
</header>

<!-- ================= HERO SECTION ================= -->
<section class="hero">
    <div class="hero-text">
        <h1>Building Excellence, <br><span>One Project at a Time</span></h1>
        <p>Rocelyn RJ Building Trades Inc provides professional construction services with quality craftsmanship, reliable timelines, and competitive pricing.</p>
    </div>
    <div class="HOMEPIC">
        <img src="../Capstone pics/HOMEPIC.jpg" alt="Company Info">
    </div>
</section>

<!-- ================= SERVICES SECTION ================= -->
<section id="services" class="services">
    <h2>Our Services</h2>
    <p>We offer comprehensive building trades services for residential and commercial projects</p>
    <div class="service-boxes">
        <div class="service-card">
            <h3>Residential Construction</h3>
            <p>Custom homes, additions, and renovations built to your specifications</p>
        </div>
        <div class="service-card">
            <h3>Commercial Projects</h3>
            <p>Office buildings, retail spaces, and industrial facilities</p>
        </div>
        <div class="service-card">
            <h3>Repairs & Maintenance</h3>
            <p>Professional repair services and ongoing maintenance programs</p>
        </div>
    </div>
</section>


<!-- ================= PORTFOLIO SECTION ================= -->
<section id="portfolio" class="portfolio">
    <h2>Our Portfolio</h2>
    <p>Take a look at some of our completed projects showcasing quality craftsmanship and reliable construction services.</p>
    
    <div class="portfolio-gallery">
        <div class="portfolio-item">
            <img src="project1.jpg" alt="Project 1">
            <p>Residential Home Construction</p>
        </div>
        <div class="portfolio-item">
            <img src="project2.jpg" alt="Project 2">
            <p>Church Renovation</p>
        </div>
        <div class="portfolio-item">
            <img src="project3.jpg" alt="Project 3">
            <p>Office Interior Fit-Out</p>
        </div>
        <div class="portfolio-item">
            <img src="project4.jpg" alt="Project 4">
            <p>Commercial Retail Space</p>
        </div>
        <div class="portfolio-item">
            <img src="project5.jpg" alt="Project 5">
            <p>Restaurant Construction</p>
        </div>
        <div class="portfolio-item">
            <img src="project6.jpg" alt="Project 6">
            <p>Event Venue Renovation</p>
        </div>
    </div>
</section>

<!-- ================= WHY CHOOSE US SECTION ================= -->
<section class="why-choose">
    <h2>Why Choose Rocelyn RJ?</h2>
    <div class="choose-list">
        <div class="choose-item">
            
            <h3>✔ Licensed & Insured</h3>
            <p>Fully licensed contractors with comprehensive insurance coverage for your peace of mind.</p>
        </div>
        <div class="choose-item">
            <h3>✔ Quality Craftsmanship</h3>
            <p>Attention to detail and commitment to excellence in every project we undertake.</p>
        </div>
        <div class="choose-item">
            <h3>✔ On-Time Delivery</h3>
            <p>We respect your timeline and deliver projects on schedule and within budget.</p>
        </div>
    </div>
    <div class="HOMEPIC">
        <img src="../Capstone pics/HOMEPIC.jpg" alt="Company Projects">
    </div>
</section>

<!-- ================= ABOUT SECTION ================= -->
<section id="about" class="about">
    <h2>About Rocelyn RJ Building Trades Inc</h2>
    <p>
        With years of experience in the construction industry, Rocelyn RJ Building Trades Inc has established itself 
        as a trusted name in professional building services. We pride ourselves on delivering exceptional quality work 
        while maintaining the highest standards of safety and professionalism.
    </p>
    <p>
        Our team of skilled professionals is dedicated to bringing your vision to life, whether it's a residential renovation, 
        commercial construction project, or ongoing maintenance services. We believe in building lasting relationships 
        with our clients through reliable service and superior craftsmanship.
    </p>
</section>

<!-- ================= FOOTER ================= -->
<footer>
    <div class="footer-container">
        <div class="footer-box">
            <h3>Rocelyn RJ Building Trades Inc</h3>
            <p>Professional building trades services with quality craftsmanship and reliable results.</p>
        </div>
        <div class="footer-box">
            <h3>Services</h3>
            <ul>
                <li>General Construction</li>
                <li>Residential Building</li>
                <li>Commercial Projects</li>
                <li>Renovations & Repairs</li>
                <li>Project Management</li>
            </ul>
        </div>
        <div class="footer-box">
            <h3>Contact Info</h3>
            <p>📞 (02) 564-6348 / (02) 561-1568</p>
            <p>📧 rocelenrjbldg.trades@yahoo.com</p>
            <p>📍 1565 Aviadores, San Andres Bukid, Manila, 1009 Metro Manila</p>
            <p>✔ Licensed & Insured</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2025 Rocelyn RJ Building Trades Inc. All rights reserved.</p>
    </div>

    
</footer>

</body>
</html>
