<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Language" content="zh-HK, zh-TW, en">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY WEB 2025 - MY WEB</title>
    <!--add icon link-->
    <link rel="icon" href="uploads/user_images/RAKKO.jpg" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
require_once 'admin/config.php';
require_once 'functions.php';

// Fetch active banner
$sql = "SELECT * FROM banners WHERE active = 1 ORDER BY id DESC LIMIT 1";
$banner = $conn->query($sql)->fetch_assoc();

// Fetch home introduction
$homeIntro = getPageContent('home_intro');

include 'includes/header.php'; ?>

<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        padding-top: 60px; /* Height of fixed header */
        background: #fff;
    }

    main {
        flex: 1;
        width: 100%;
    }

    /* Banner styles */
    .banner-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        margin: 0 auto;
        padding: 0 30px;
        height: min(25rem, 60vh);
    }

    .banner-slide {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .banner-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        border-radius: 8px;
    }

    /* Banner navigation dots */
    .banner-dots {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
    }

    .dot {
        height: 12px;
        width: 12px;
        margin: 0 5px;
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .dot.active {
        background-color: #fff;
    }

    /* Fade animation */
    .fade {
        animation-name: fade;
        animation-duration: 1.5s;
    }

    @keyframes fade {
        from {opacity: .4} 
        to {opacity: 1}
    }

    /* Introduction Section */
    .introduction {
        padding: 4rem 0;
        background: #fff;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .banner-container {
            height: min(25rem, 50vh);
            padding: 0 20px;
        }
    }

    @media (max-width: 480px) {
        .banner-container {
            height: min(25rem, 40vh);
            padding: 0 15px;
        }
    }
</style>
<main>
    <!-- Add this where you want the rotating banner -->
    <div class="banner-container">
        <?php
        // Fetch active banners from database
        $banners_query = "SELECT * FROM banners WHERE active = 1 ORDER BY position ASC, id DESC";
        $banners = $conn->query($banners_query);
        
        if ($banners && $banners->num_rows > 0) {
            while ($banner = $banners->fetch_assoc()) {
                if (!empty($banner['image_path'])) {
                    ?>
                    <div class="banner-slide fade">
                        <?php if (!empty($banner['redirect_url'])): ?>
                            <a href="<?php echo htmlspecialchars($banner['redirect_url']); ?>" target="_blank">
                        <?php endif; ?>
                        
                        <img src="<?php echo htmlspecialchars($banner['image_path']); ?>" alt="Banner Image">
                        
                        <?php if (!empty($banner['redirect_url'])): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            }
        }
        ?>
        
        <!-- Navigation dots -->
        <div class="banner-dots">
            <?php
            $banners->data_seek(0); // Reset result pointer
            $index = 0;
            while ($banner = $banners->fetch_assoc()) {
                if (!empty($banner['image_path'])) {
                    echo "<span class='dot' onclick='currentSlide(" . ($index + 1) . ")'></span>";
                    $index++;
                }
            }
            ?>
        </div>
    </div>

    <!-- Introduction Section -->
    <?php if ($homeIntro): ?>
    <section class="introduction">
        <div class="container">
            <h2><?php echo htmlspecialchars($homeIntro['title']); ?></h2>
            
            <?php if(!empty($homeIntro['image'])): ?>
            <div class="intro-image">
                <img src="<?php echo htmlspecialchars($homeIntro['image']); ?>" alt="Introduction">
            </div>
            <?php endif; ?>
            
            <div class="intro-content">
                <div class="ck-content">
                    <?php echo renderContent($homeIntro['content']); ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>
</body>
<script>
let slideIndex = 1;
let slideTimer;

function showSlides(n) {
    let slides = document.getElementsByClassName("banner-slide");
    let dots = document.getElementsByClassName("dot");
    
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    
    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    
    // Remove active class from all dots
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    
    // Show current slide and activate corresponding dot
    slides[slideIndex-1].style.display = "block";
    dots[slideIndex-1].className += " active";
    
    // Reset timer
    clearTimeout(slideTimer);
    slideTimer = setTimeout(() => plusSlides(1), 5000); // Change slide every 5 seconds
}

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

// Start the slideshow when the page loads
document.addEventListener('DOMContentLoaded', function() {
    showSlides(slideIndex);
});
</script> 