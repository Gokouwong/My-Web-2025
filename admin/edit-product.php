<?php
require_once 'config.php';
require_once '../functions.php';

// Only start session if one hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: manage-products.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $product_number = trim($_POST['product_number']);
        $intro = trim($_POST['intro']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $active = isset($_POST['active']) ? 1 : 0;

        // Update product details
        $stmt = $conn->prepare("UPDATE products SET name = ?, product_number = ?, intro = ?, description = ?, price = ?, active = ? WHERE id = ?");
        $stmt->bind_param("ssssdii", $name, $product_number, $intro, $description, $price, $active, $product_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating product: " . $conn->error);
        }

        // Handle image upload if present
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $upload_dir = dirname(dirname(__FILE__)) . '/uploads/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file = $_FILES['image'];
            $fileName = uniqid() . '_' . basename($file['name']);
            $targetPath = $upload_dir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/products/' . $fileName;
                $stmt = $conn->prepare("UPDATE products SET image_path = ? WHERE id = ?");
                $stmt->bind_param("si", $imagePath, $product_id);
                $stmt->execute();
            }
        }

        $message = "Product updated successfully!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        error_log($error);
    }
}

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: manage-products.php');
    exit();
}

require_once 'includes/header.php';
?>

<div class="cms-container">
    <h1 class="cms-mb">Edit Product</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cms-content">
        <form method="POST" enctype="multipart/form-data" class="cms-form">
            <div class="form-group">
                <label for="product_number">Product Number:</label>
                <input type="text" id="product_number" name="product_number" 
                       value="<?php echo htmlspecialchars($product['product_number']); ?>" required>
            </div>

            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" min="0"
                       value="<?php echo number_format($product['price'], 2, '.', ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="intro">Introduction:</label>
                <textarea id="intro" name="intro" rows="2"><?php echo htmlspecialchars($product['intro']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Product Image:</label>
                <?php if ($product['image_path']): ?>
                    <div class="current-image">
                        <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="Current product image" style="max-width: 200px;">
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="active" <?php echo $product['active'] ? 'checked' : ''; ?>>
                    Active
                </label>
            </div>

            <div class="form-buttons">
                <button type="submit" class="cms-button">Update Product</button>
                <a href="manage-products" class="cms-button cms-button-secondary">Back to Products</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.cms-button-secondary {
    background: #6c757d;
}

.current-image {
    margin: 1rem 0;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.cms-container {
    max-width: calc(100% - 20rem); /* 10rem padding on each side */
    margin: 0 auto;
    padding: 2rem 10rem; /* Add padding for better spacing */
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h1 {
    margin-bottom: 1.5rem;
    color: #333;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #555;
    font-weight: bold;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #3498db;
    outline: none;
}

button.cms-button {
    padding: 0.75rem 1.5rem;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button.cms-button:hover {
    background-color: #2980b9;
}
</style>

</body>
</html> 