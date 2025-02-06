<?php
require_once 'config.php';
require_once '../functions.php';

// Debug configuration
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug database structure - log only, don't display
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}
error_log("Tables in database: " . implode(", ", $tables));

$result = $conn->query("DESCRIBE products");
$structure = [];
while ($row = $result->fetch_array()) {
    $structure[] = "{$row['Field']} ({$row['Type']})";
}
error_log("Products table structure: " . implode(", ", $structure));

// Include the unified header
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login');
    exit();
}

$message = '';
$error = '';

// Debug POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data received: " . print_r($_POST, true));
}

// Handle reordering AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    // Clear any previous output
    ob_clean();
    
    // Set proper JSON headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    try {
        $orderData = $_POST['order'];
        error_log("Received order data: " . $orderData);
        
        $orders = json_decode($orderData, true);
        if ($orders === null) {
            throw new Exception("Failed to parse JSON: " . json_last_error_msg());
        }
        
        if (!is_array($orders)) {
            throw new Exception("Invalid data format: expected array");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First ensure the display_order column exists
            $result = $conn->query("SHOW COLUMNS FROM products LIKE 'display_order'");
            if ($result->num_rows === 0) {
                $conn->query("ALTER TABLE products ADD COLUMN display_order INT DEFAULT 0");
            }
            
            $stmt = $conn->prepare("UPDATE products SET display_order = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            foreach ($orders as $order) {
                if (!isset($order['id'], $order['order'])) {
                    throw new Exception("Missing required fields in order data");
                }
                
                if (!$stmt->bind_param("ii", $order['order'], $order['id'])) {
                    throw new Exception("Failed to bind parameters: " . $stmt->error);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update order: " . $stmt->error);
                }
            }
            
            $conn->commit();
            
            // Ensure clean JSON response
            echo json_encode([
                'status' => 'success',
                'message' => 'Order updated successfully'
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Order update failed: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    
    // Ensure we stop here
    exit();
}

// Handle product removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product'])) {
    $product_id = $_POST['product_id'];

    // First, get the image path to delete the file
    $stmt = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product && $product['image_path']) {
        // Delete the physical file
        $file_path = '../' . $product['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $message = "Product removed successfully!";
    } else {
        $error = "Error removing product.";
    }
}

// Fetch products ordered by display_order
$products = $conn->query("SELECT * FROM products ORDER BY display_order, id");

// Handle adding new product
if (isset($_POST['add_product'])) {
    try {
        $name = trim($_POST['name']);
        $intro = trim($_POST['intro']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Generate product number
        $product_number = 'P' . sprintf('%03d', time() % 1000);
        
        // Start transaction
        $conn->begin_transaction();
        
        // Insert product
        $stmt = $conn->prepare("INSERT INTO products (product_number, name, intro, description, price, active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdi", $product_number, $name, $intro, $description, $price, $active);
        
        if (!$stmt->execute()) {
            throw new Exception("Error adding product: " . $conn->error);
        }
        
        $product_id = $conn->insert_id;
        
        // Handle image upload if present
        if (isset($_FILES['product_image']) && $_FILES['product_image']['size'] > 0) {
            $upload_dir = dirname(dirname(__FILE__)) . '/uploads/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file = $_FILES['product_image'];
            $fileName = uniqid() . '_' . basename($file['name']);
            $targetPath = $upload_dir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/products/' . $fileName;
                $stmt = $conn->prepare("UPDATE products SET image_path = ? WHERE id = ?");
                $stmt->bind_param("si", $imagePath, $product_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating product image: " . $conn->error);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        $message = "Product added successfully!";
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?message=Product added successfully!");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
        error_log($error);
    }
}

// Handle product image uploads
if (isset($_FILES['product_image']) && isset($_POST['product_id'])) {
    try {
        $upload_dir = dirname(dirname(__FILE__)) . '/images/products/';
        
        // Debug information
        error_log("Upload directory: " . $upload_dir);
        error_log("Directory exists: " . (file_exists($upload_dir) ? 'Yes' : 'No'));
        error_log("Directory writable: " . (is_writable($upload_dir) ? 'Yes' : 'No'));
        error_log("Directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
        
        // Generate filename
        $extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $filename = uniqid() . date('YmdHis') . '.' . $extension;
        $target_file = $upload_dir . $filename;
        
        // Move the file
        if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            throw new Exception("Failed to move uploaded file. Please check directory permissions.");
        }
        
        // Update database
        $id = $_POST['product_id'];
        $image_path = "images/products/" . $filename;
        
        $stmt = $conn->prepare("UPDATE products SET image_path = ? WHERE id = ?");
        $stmt->bind_param("si", $image_path, $id);
        
        if (!$stmt->execute()) {
            // If database update fails, remove the uploaded file
            @unlink($target_file);
            throw new Exception("Failed to update database: " . $conn->error);
        }
        
        $message = "Product image uploaded successfully!";
        error_log("Image uploaded successfully to: " . $target_file);
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        error_log($error);
    }
}

// Handle product updates
if (isset($_POST['update_product'])) {
    try {
        $id = $_POST['product_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $name, $description, $price, $id);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
        } else {
            throw new Exception("Error updating product: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        error_log($error);
    }
}
?>

<div class="cms-container">
    <h1 class="cms-mb">Manage Products</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Add New Product Form -->
    <div class="cms-content mb-4">
        <h2>Add New Product</h2>
        <form method="POST" enctype="multipart/form-data" class="cms-form">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="intro">Introduction:</label>
                <textarea id="intro" name="intro" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="product_image">Product Image:</label>
                <input type="file" id="product_image" name="product_image" accept="image/*">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="active" checked>
                    Active
                </label>
            </div>

            <button type="submit" name="add_product" class="cms-button">Add Product</button>
        </form>
    </div>

    <!-- Existing Products List -->
    <div class="cms-content">
        <h2>Existing Products</h2>
        <div class="products-list" id="sortable-products">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-item" data-id="<?php echo htmlspecialchars($product['id']); ?>">
                    <div class="drag-handle">☰</div>
                    <div class="product-details">
                        <div class="product-image">
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="max-width: 100px;">
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <?php if (!empty($product['product_number'])): ?>
                                <p class="product-number">Product #: <?php echo htmlspecialchars($product['product_number']); ?></p>
                            <?php endif; ?>
                            <div class="status-badge <?php echo $product['active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $product['active'] ? 'Active' : 'Inactive'; ?>
                            </div>
                            <?php if (!empty($product['intro'])): ?>
                                <p class="product-intro"><?php echo htmlspecialchars($product['intro']); ?></p>
                            <?php elseif (!empty($product['description'])): ?>
                                <p class="product-intro"><?php echo htmlspecialchars(substr($product['description'], 0, 150)) . '...'; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="product-actions">
                            <a href="edit-product?id=<?php echo $product['id']; ?>" class="cms-button">Edit</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="remove_product" class="cms-button delete-button" 
                                        onclick="return confirm('Are you sure you want to remove this product?')">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Add Sortable.js library -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var productsList = document.getElementById('sortable-products');
    
    Sortable.create(productsList, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function() {
            // Get new order
            var items = productsList.querySelectorAll('.product-item');
            var order = [];
            
            items.forEach(function(item, index) {
                var id = item.dataset.id;
                if (!id) {
                    console.error('Missing data-id on item:', item);
                    return;
                }
                order.push({
                    id: parseInt(id),
                    order: index + 1
                });
            });

            if (order.length === 0) {
                console.error('No items to reorder');
                return;
            }

            console.log('Sending order data:', order);

            // Send as x-www-form-urlencoded
            var data = new URLSearchParams();
            data.append('order', JSON.stringify(order));

            // Save new order
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: data
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse response:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                if (data.status === 'success') {
                    console.log('Order saved successfully');
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error saving order:', error);
                alert('Error saving order: ' + error.message);
            });
        }
    });
});
</script>

<style>
/* Update container styles */
.cms-container {
    max-width: calc(100% - 20rem); /* Account for 10rem on each side */
    margin: 0 auto;
    padding: 2rem;
    margin-top: 2rem;
}

/* Rest of your existing styles */
.mb-4 {
    margin-bottom: 2rem;
}

.cms-form {
    max-width: 800px;
    margin: 0 auto;
}

/* Add responsive handling for smaller screens */
@media (max-width: 1200px) {
    .cms-container {
        max-width: calc(100% - 4rem); /* Less padding on smaller screens */
    }
}

@media (max-width: 768px) {
    .cms-container {
        max-width: calc(100% - 2rem); /* Even less padding on mobile */
    }
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group input[type="file"] {
    padding: 0.5rem 0;
}

.form-group input[type="checkbox"] {
    margin-right: 0.5rem;
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

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    margin: 0.5rem 0;
}

.status-badge.active {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-badge.inactive {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.product-number {
    color: #666;
    font-size: 0.9rem;
}

.product-intro {
    color: #666;
    margin-top: 0.5rem;
}

/* Update product-item styles for better layout */
.product-item {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.product-details {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1.5rem;
    align-items: center;
    padding: 1rem;
}

.drag-handle {
    cursor: move;
    color: #999;
    padding: 0 1rem;
    font-size: 1.5rem;
}

.product-image {
    width: 100px;
    height: 100px;
    overflow: hidden;
    border-radius: 4px;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-actions {
    display: flex;
    gap: 0.5rem;
}

.delete-button {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
}

.delete-button:hover {
    background-color: #c82333;
}
</style>

</body>
</html> 