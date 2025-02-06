<?php
require_once 'admin/config.php';
require_once 'functions.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

try {
    if (!isset($_GET['id'])) {
        throw new Exception("Event ID not provided");
    }

    $id = (int)$_GET['id'];
    
    // Get event details
    $stmt = $conn->prepare("SELECT * FROM past_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        throw new Exception("Event not found");
    }

    // Get event gallery images
    $stmt = $conn->prepare("SELECT * FROM event_gallery WHERE event_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $gallery = $stmt->get_result();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Language" content="zh-HK, zh-TW, en">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($event) ? htmlspecialchars($event['title']) : 'Event Not Found'; ?> - Test Web 2025</title>
    <!--add icon link-->
    <link rel="icon" href="uploads/user_images/RAKKO.jpg" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
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
            padding-top: 70px; /* Height of fixed header */
        }

        main {
            flex: 1;
        }

        .container {
            max-width: calc(100% - 20rem);
            margin: 0 auto;
            padding: 2rem;
            color: #333;
        }

        .event-header {
            margin-bottom: 2rem;
            color:#ffffff
        }

        .event-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .video-section {
            position: relative;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            min-height: 400px;
            height: calc(100vh - 400px);
            max-height: 800px;
            margin: 0 auto;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
            max-width: 100vw;
        }

        /* Aspect ratio containers */
        .video-container.ratio-16-9 {
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
        }

        .video-container.ratio-4-3 {
            padding-bottom: 75%; /* 4:3 */
            height: 0;
        }

        .video-container.ratio-8-10 {
            padding-bottom: 125%; /* 8:10 */
            height: 0;
            max-width: 600px;
            margin: 0 auto;
        }

        .video-container.ratio-9-16 {
            padding-bottom: 177.78%; /* 9:16 */
            height: 0;
            max-width: 450px;
            margin: 0 auto;
        }

        .content-section {
            line-height: 1.6;
        }

        .gallery-section {
            margin-top: 3rem;
            margin-bottom: 3rem;
            color:#ffffff
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .gallery-item {
            position: relative;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-link {
            display: block;
            width: 100%;
            height: 100%;
        }

        .more-images {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }

        .back-link {
            display: block;
            width: fit-content;
            margin: 2rem auto;
            padding: 1rem 2rem;
            color: #333;
            text-decoration: none;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .event-content {
                grid-template-columns: 1fr;
            }

            .video-container {
                width: 100%;
                min-height: 300px;
                height: calc(100vh - 300px);
            }

            .video-container.ratio-9-16 {
                max-width: 100%;
            }
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            max-width: 90%;
            max-height: 90vh;
            margin: auto;
            display: block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .modal-nav button {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-size: 24px;
            padding: 20px;
            cursor: pointer;
            transition: background 0.3s;
            z-index: 1010;
        }

        .modal-nav button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-nav .prev {
            left: 20px;
        }

        .modal-nav .next {
            right: 20px;
        }

        .close {
            position: absolute;
            right: 25px;
            top: 10px;
            color: #f1f1f1;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }

        .image-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0, 0, 0, 0.5);
            padding: 5px 10px;
            border-radius: 4px;
            margin: 0 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
<style>
    body{
        background-image: url('images/BKG.jpg');
    }
</style>
    <main class="container">
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (isset($event)): ?>
            <div class="event-header">
                <h1><?php echo htmlspecialchars($event['title']); ?></h1>
            </div>

            <div class="event-content">
                <div class="video-section">
                    <?php if (!empty($event['video_url'])): ?>
                        <?php echo embedVideo($event['video_url']); ?>
                    <?php elseif (!empty($event['main_image'])): ?>
                        <img src="<?php echo htmlspecialchars($event['main_image']); ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php endif; ?>
                </div>
                
                <div class="content-section">
                    <?php echo $event['content']; ?>
                </div>
            </div>

            <?php if ($gallery && $gallery->num_rows > 0): ?>
                <div class="gallery-section">
                    <h2>Photo Gallery</h2>
                    <div class="gallery-grid">
                        <?php 
                        $count = 0;
                        $total = $gallery->num_rows;
                        while ($image = $gallery->fetch_assoc()): 
                            $count++;
                            if ($count > 5) break;
                        ?>
                            <div class="gallery-item">
                                <a href="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                   class="gallery-link" 
                                   data-total="<?php echo $total; ?>"
                                   data-current="<?php echo $count; ?>">
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         alt="Event gallery image">
                                    <?php if ($count === 5 && $total > 5): ?>
                                        <div class="more-images">
                                            +<?php echo $total - 5; ?> more
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
               
                <!-- Image Modal -->
                <div id="imageModal" class="modal">
                    <span class="close">&times;</span>
                    <img class="modal-content" id="modalImage">
                    <div class="modal-nav">
                        <button class="prev">&#10094;</button>
                        <span class="image-counter"></span>
                        <button class="next">&#10095;</button>
                    </div>
                </div>
            <?php endif; ?>

            <a href="past-events" class="back-link">← Back to Events</a>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        const closeBtn = document.querySelector('.close');
        const prevBtn = document.querySelector('.prev');
        const nextBtn = document.querySelector('.next');
        const counter = document.querySelector('.image-counter');
        let currentImageIndex = 0;
        const galleryImages = document.querySelectorAll('.gallery-link');

        galleryImages.forEach((link, index) => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                currentImageIndex = index;
                showImage(this.href);
                modal.style.display = 'block';
                updateCounter();
            });
        });

        function showImage(src) {
            modalImg.src = src;
        }

        function updateCounter() {
            counter.textContent = `${currentImageIndex + 1} / ${galleryImages.length}`;
        }

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        prevBtn.onclick = function() {
            currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            showImage(galleryImages[currentImageIndex].href);
            updateCounter();
        }

        nextBtn.onclick = function() {
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            showImage(galleryImages[currentImageIndex].href);
            updateCounter();
        }

        document.addEventListener('keydown', function(e) {
            if (modal.style.display === 'block') {
                if (e.key === 'ArrowLeft') prevBtn.click();
                if (e.key === 'ArrowRight') nextBtn.click();
                if (e.key === 'Escape') closeBtn.click();
            }
        });
    });

    function checkVideoAspectRatio(iframe) {
        setTimeout(function() {
            const width = iframe.clientWidth;
            const height = iframe.clientHeight;
            const ratio = width / height;
            const container = iframe.parentElement;

            if (ratio < 1) {
                // Portrait video (e.g., 9:16)
                container.classList.add('portrait');
            } else if (ratio === 1) {
                // Square video
                container.classList.add('square');
            } else if (ratio < 1.77) { // Less than 16:9
                // Likely 4:3
                container.classList.add('traditional');
            }
            // Default is 16:9, no class needed
        }, 1000); // Wait for video to load
    }
    </script>
</body>
</html> 