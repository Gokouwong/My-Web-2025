<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$background = getPageBackground($current_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - MY WEB</title>
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
            <?php if ($background): ?>
                <?php if ($background['background_type'] === 'color'): ?>
                    background-color: <?php echo htmlspecialchars($background['background_value']); ?>;
                <?php elseif ($background['background_type'] === 'image'): ?>
                    background-image: url('<?php echo htmlspecialchars($background['background_value']); ?>');
                    background-size: cover;
                    background-position: center;
                    background-attachment: fixed;
                <?php endif; ?>
            <?php endif; ?>
        }

        main {
            flex: 1;
        }

        .page-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        <?php if ($background): ?>
        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            <?php if ($background['background_type'] === 'color'): ?>
                background-color: <?php echo htmlspecialchars($background['background_value']); ?>;
            <?php elseif ($background['background_type'] === 'image'): ?>
                background-image: url('<?php echo htmlspecialchars($background['background_value']); ?>');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
            <?php endif; ?>
        }
        <?php endif; ?>

        /* Page specific styles */
    </style>
</head>
<body>
    <?php if ($background): ?>
    <div class="page-background"></div>
    <?php endif; ?>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="page-content">
            <!-- Page content here -->
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 