<?php
require_once 'admin/config.php';
require_once 'functions.php';

$pageContent = getPageContent('contact');

// Include header
require_once 'includes/header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contect Me - MY WEB</title>
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
    </style>
</head>

    <main>
        <div class="container">
            <h1><?php echo htmlspecialchars($pageContent['title']); ?></h1>
            
            <?php if($pageContent['image']): ?>
                <img src="<?php echo htmlspecialchars($pageContent['image']); ?>" alt="Contact Us">
            <?php endif; ?>
            
            <div class="content">
                <?php echo renderContent($pageContent['content']); ?>
            </div>
        </div>
    </main>
</body>
</html> 
<?php include 'includes/footer.php'; ?>
