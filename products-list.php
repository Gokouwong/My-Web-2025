<?php
require_once 'admin/config.php';
require_once 'functions.php';

// Fetch all active products
$sql = "SELECT * FROM products WHERE active = 1 ORDER BY display_order, id";
$products = $conn->query($sql);

// Include header
require_once 'includes/header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Products - MY WEB</title>    
    <!--add icon link-->
    <link rel="icon" href="uploads/user_images/RAKKO.jpg" type="image/x-icon">
</head>
<body>
<main>
    <div class="container">
        <h1>Our Products</h1>
        
        <div class="products-grid">
            <?php while ($product = $products->fetch_assoc()): ?>
                <a href="product-detail?id=<?php echo $product['id']; ?>" class="product-card">
                    <?php if (!empty($product['image_path'])): ?>
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        
                        <?php if (!empty($product['intro'])): ?>
                            <p class="intro"><?php echo htmlspecialchars($product['intro']); ?></p>
                        <?php endif; ?>
                        
                        <p class="price">$<?php echo number_format($product['price'] ?? 0, 2); ?></p>
                        
                        <div class="view-details">View Details →</div>
                    </div>
                </a>
            <?php endwhile; ?>
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
    text-align: center;
}

.header {
    margin-bottom: 40px;
}

h1 {
    color: #333;
    margin: 0;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 2rem 0;
}

.product-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    text-decoration: none;
    color: inherit;
    display: block;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.product-image {
    width: 100%;
    padding-top: 100%; /* This makes it square */
    position: relative;
    overflow: hidden;
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    padding: 1.5rem;
}

.product-info h3 {
    margin: 0 0 1rem 0;
    color: #333;
}

.price {
    font-size: 1.5rem;
    color: #28a745;
    font-weight: bold;
    margin: 1rem 0;
}

.intro {
    color: #666;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.view-details {
    color: #007bff;
    margin-top: 1rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Include footer if you have one
if (file_exists('includes/footer.php')) {
    require_once 'includes/footer.php';
}
?> 