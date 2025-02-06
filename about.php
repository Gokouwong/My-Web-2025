<?php
require_once 'admin/config.php';
require_once 'functions.php';

// Fetch about page content
$stmt = $conn->prepare("SELECT * FROM website_content WHERE page = 'about'");
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

// Include header
require_once 'includes/header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - MY WEB</title>
</head>
<body>
    <style>
        body{
            background-color: #c2c2d6;
        }
    </style>
    <main class="main-content">
        <div class="page-content about-container">
            <h1>About Us</h1>
            
            <?php if($page['image']): ?>
                <img src="<?php echo htmlspecialchars($page['image']); ?>" alt="About Us">
            <?php endif; ?>
            
            <div class="content">
                <?php echo renderContent($page['content']); ?>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
            </body>

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
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.content {
    line-height: 1.6;
}

/* Style the HTML elements */
.content h2 {
    margin: 2rem 0 1rem;
    color: #333;
}

.content p {
    margin-bottom: 1rem;
}

.content strong {
    font-weight: bold;
}

.content em {
    font-style: italic;
}

.content ul {
    margin: 1rem 0;
    padding-left: 2rem;
}

.content li {
    margin-bottom: 0.5rem;
}
</style>
</body>
</html> 