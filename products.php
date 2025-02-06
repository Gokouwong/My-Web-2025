<?php
require_once 'admin/config.php';
require_once 'functions.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no ID is provided, redirect to products list
if (!$product_id) {
    header('Location: products-list.php');
    exit();
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Redirect if product not found
if (!$product) {
    header('Location: products-list.php');
    exit();
}

require_once 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="product-detail">
            <div class="product-image">
                <?php if (!empty($product['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <p class="price">$<?php echo number_format($product['price'] ?? 0, 2); ?></p>
                
                <?php if (!empty($product['intro'])): ?>
                    <p class="intro"><?php echo htmlspecialchars($product['intro']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($product['description'])): ?>
                    <div class="description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                <?php endif; ?>
                
                <a href="products-list.php" class="back-button">← Back to Products</a>
            </div>
        </div>
    </div>
</main>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.product-image img {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.product-info h1 {
    margin: 0 0 1rem 0;
    color: #333;
}

.price {
    font-size: 2rem;
    color: #28a745;
    font-weight: bold;
    margin: 1rem 0;
}

.intro {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 1.5rem;
}

.description {
    color: #444;
    line-height: 1.6;
    margin: 1.5rem 0;
}

.back-button {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 2rem;
    transition: background-color 0.3s;
}

.back-button:hover {
    background: #0056b3;
}

@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>

<?php
// Include footer if you have one
if (file_exists('includes/footer.php')) {
    require_once 'includes/footer.php';
}
?>