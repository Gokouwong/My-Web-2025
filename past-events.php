<?php
require_once 'admin/config.php';
require_once 'functions.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

try {
    // Create table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS past_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        intro TEXT,
        content TEXT,
        main_image VARCHAR(255),
        video_url VARCHAR(255),
        position INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci DEFAULT COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating table: " . $conn->error);
    }

    // Get all events
    $events = $conn->query("SELECT * FROM past_events ORDER BY position ASC, id DESC");
    if (!$events) {
        error_log("Events query error: " . $conn->error);
        throw new Exception("Error fetching events: " . $conn->error);
    }
    error_log("Number of events found: " . $events->num_rows);
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
    <title>Past Events - Test Web 2025</title>
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
        padding-top: 60px; /* Height of fixed header */
    }

        main {
            flex: 1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin: 2rem 0;
        }

        .event-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-image {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            overflow: hidden;
            max-height: 25rem;
        }

        .event-image img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .event-card a {
            text-decoration: none;
            color: inherit;
        }

        .event-info {
            padding: 1.5rem;
        }

        .event-info h2 {
            margin: 0 0 1rem;
            color: #333;
        }

        .event-info p {
            margin: 0;
            line-height: 1.5;
            color: #666;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .read-more {
            display: inline-block;
            margin-top: 1rem;
            color: #007bff;
        }

        .no-events {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <h1><?php echo t('past_events'); ?></h1>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="events-grid">
            <?php if (isset($events) && $events->num_rows > 0): ?>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <div class="event-card">
                        <a href="event-detail?id=<?php echo $event['id']; ?>">
                            <div class="event-image">
                                <?php if (!empty($event['main_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>">
                                <?php else: ?>
                                    <img src="images/placeholder.jpg" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="event-info">
                                <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                                <?php if (!empty($event['intro'])): ?>
                                    <p><?php echo htmlspecialchars(substr($event['intro'], 0, 300)) . '...'; ?></p>
                                <?php endif; ?>
                                <span class="read-more"><?php echo t('read_more'); ?> →</span>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-events">
                    <p><?php echo t('no_events'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 