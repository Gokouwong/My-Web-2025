<?php
require_once 'admin/config.php';
require_once 'functions.php';

// Get product ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product details
$product = getProduct($id);

// Redirect if product not found
if (!$product) {
    header('Location: products.php');
    exit();
}

// Include header
require_once 'includes/header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($product) ? htmlspecialchars($product['name']) : 'Event Not Found'; ?> - Test Web 2025</title>
    <!--add icon link-->
    <link rel="icon" href="uploads/user_images/RAKKO.jpg" type="image/x-icon">
</head>
<body>
    <main>
        <div class="container">
            <div class="product-detail">
                <div class="product-images">
                    <?php if($product['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <div class="no-image">No image available</div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                    <div class="description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    <a href="products-list" class="btn back-btn">← Back to Products</a>
                </div>
            </div>
        </div>
    </main>

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
        padding: 20px;
    }
    .product-detail {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-top: 40px;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    @media (max-width: 768px) {
        .product-detail {
            grid-template-columns: 1fr;
        }
    }
    .product-images img {
        width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .no-image {
        width: 100%;
        height: 300px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        border-radius: 8px;
    }
    .product-info h1 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 2em;
    }
    .price {
        color: #e44d26;
        font-size: 1.5em;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .description {
        color: #444;
        line-height: 1.8;
        margin-bottom: 30px;
    }
    .btn {
        display: inline-block;
        padding: 12px 24px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background 0.3s;
    }
    .btn:hover {
        background: #0056b3;
    }
    .back-btn {
        background: #666;
    }
    .back-btn:hover {
        background: #444;
    }
    </style>
</body>
</html> 