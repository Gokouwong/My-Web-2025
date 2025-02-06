<?php
// At the very top of the file
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once '../functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login');
    exit();
}

// Get server information without using posix functions
$server_info = array(
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'PHP Version' => PHP_VERSION,
    'Server Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'Server Name' => $_SERVER['SERVER_NAME'] ?? 'Unknown'
);

// Add debug information
$upload_path = dirname(dirname(__FILE__)) . '/images';
echo "<!-- Debug: Upload path = $upload_path -->";
if (!file_exists($upload_path)) {
    echo "<!-- Debug: Images directory does not exist -->";
}

// Handle content updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_page'])) {
        $page = $_POST['page'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        
        $stmt = $conn->prepare("UPDATE website_content SET title = ?, content = ? WHERE page = ?");
        $stmt->bind_param("sss", $title, $content, $page);
        
        if ($stmt->execute()) {
            $message = ucfirst($page) . " page updated successfully!";
        } else {
            $error = "Error updating content: " . $conn->error;
        }
    }
    
    // Handle page image uploads
    if (isset($_FILES['page_image'])) {
        try {
            $upload_dir = dirname(dirname(__FILE__)) . '/images/pages/';
            error_log("Upload directory: " . $upload_dir);
            
            // Create directories with proper permissions
            if (!file_exists(dirname($upload_dir))) {
                error_log("Creating main images directory");
                if (!@mkdir(dirname($upload_dir), 0777, true)) {
                    throw new Exception("Failed to create images directory. Error: " . error_get_last()['message']);
                }
                @chmod(dirname($upload_dir), 0777);
                // Also set ownership if possible
                @chown(dirname($upload_dir), '_www');
                @chgrp(dirname($upload_dir), '_www');
            }
            
            if (!file_exists($upload_dir)) {
                error_log("Creating pages directory");
                if (!@mkdir($upload_dir, 0777, true)) {
                    throw new Exception("Failed to create pages directory. Error: " . error_get_last()['message']);
                }
                @chmod($upload_dir, 0777);
                // Also set ownership if possible
                @chown($upload_dir, '_www');
                @chgrp($upload_dir, '_www');
            }
            
            // Clean the filename and ensure it's unique
            $filename = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["page_image"]["name"]));
            $filename = time() . '_' . $filename; // Add timestamp to make filename unique
            $target_file = $upload_dir . $filename;
            error_log("Target file: " . $target_file);
            
            // Check if file was uploaded successfully
            if (!is_uploaded_file($_FILES["page_image"]["tmp_name"])) {
                throw new Exception("File upload failed - not an uploaded file");
            }
            
            // Ensure target directory is writable
            if (!is_writable(dirname($target_file))) {
                throw new Exception("Target directory is not writable");
            }
            
            // Move the file
            if (!@move_uploaded_file($_FILES["page_image"]["tmp_name"], $target_file)) {
                $error = error_get_last();
                throw new Exception("Failed to move uploaded file. Error: " . ($error ? $error['message'] : 'Unknown error'));
            }
            
            // Set proper permissions for uploaded file
            @chmod($target_file, 0644);
            
            // Update database
            $page = $_POST['page'];
            $image_path = "images/pages/" . $filename;
            
            $stmt = $conn->prepare("UPDATE website_content SET image = ? WHERE page = ?");
            $stmt->bind_param("ss", $image_path, $page);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update database. Error: " . $conn->error);
            }
            
            $message = "Page image uploaded successfully!";
            
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
            error_log($error);
        }
    }

    // Handle product updates
    if (isset($_POST['update_product'])) {
        $id = $_POST['product_id'];
        $number = $_POST['product_number'];
        $name = $_POST['name'];
        $intro = $_POST['intro'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("UPDATE products SET product_number = ?, name = ?, intro = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $number, $name, $intro, $description, $id);
        $stmt->execute();
        
        $message = "Product updated successfully!";
    }
    
    // Handle product image uploads
    if (isset($_FILES['product_image'])) {
        try {
            $upload_dir = dirname(dirname(__FILE__)) . '/images/products/';
            
            // Create directories if they don't exist
            if (!file_exists(dirname($upload_dir))) {
                if (!@mkdir(dirname($upload_dir), 0777, true)) {
                    throw new Exception("Failed to create images directory");
                }
                @chmod(dirname($upload_dir), 0777);
            }
            
            if (!file_exists($upload_dir)) {
                if (!@mkdir($upload_dir, 0777, true)) {
                    throw new Exception("Failed to create products directory");
                }
                @chmod($upload_dir, 0777);
            }
            
            // Clean the filename
            $filename = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["product_image"]["name"]));
            $target_file = $upload_dir . $filename;
            
            // Check if file was uploaded successfully
            if (!is_uploaded_file($_FILES["product_image"]["tmp_name"])) {
                throw new Exception("File upload failed");
            }
            
            // Move the file
            if (!@move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                throw new Exception("Failed to move uploaded file");
            }
            
            // Update database
            $id = $_POST['product_id'];
            $image_path = "images/products/" . $filename;
            
            $stmt = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt->bind_param("si", $image_path, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update database");
            }
            
            $message = "Product image uploaded successfully!";
            
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
            error_log($error);
        }
    }
}

// Get pages content
$pages_result = $conn->query("SELECT * FROM website_content WHERE page IN ('about', 'contact', 'home_intro')");
$pages = [];
while($row = $pages_result->fetch_assoc()) {
    $pages[$row['page']] = $row;
}

// Get products
$products_result = $conn->query("SELECT * FROM products ORDER BY id");
$products = [];
while($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

require_once 'includes/header.php';
?>

<div class="cms-container">
    <h1 class="cms-mb">Admin Dashboard</h1>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin: 0 10rem; padding: 1rem;">
        <!-- Banners Management -->
        <div class="dashboard-card">
            <h3>Banners</h3>
            <p>Manage website banners and hero sections</p>
            <a href="manage-banners" class="cms-button">Manage Banners</a>
        </div>

         <!-- Homepage Content Management -->
         <div class="dashboard-card">
            <h3>Homepage Content</h3>
            <p>Manage homepage content</p>
            <a href="update-content" class="cms-button">Manage Homepage Content</a>
        </div>

        <!-- Products Management -->
        <div class="dashboard-card">
            <h3>Products</h3>
            <p>Manage product listings and details</p>
            <a href="manage-products" class="cms-button">Manage Products</a>
        </div>

        <!-- Content Management -->
        <div class="dashboard-card">
            <h3>Content</h3>
            <p>Edit website content and pages</p>
            <a href="manage-content" class="cms-button">Manage Content</a>
        </div>

        <!-- User Uploads Management -->
        <div class="dashboard-card">
            <h3>User Uploads</h3>
            <p>Review and manage user submitted images</p>
            <a href="manage-uploads" class="cms-button">Manage Uploads</a>
        </div>
    </div>
</div>

<style>
    .dashboard-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .dashboard-card h3 {
        margin: 0 0 0.5rem 0;
        color: var(--primary-color);
    }

    .dashboard-card p {
        margin: 0 0 1rem 0;
        color: #666;
    }

    .dashboard-card .cms-button {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: var(--secondary-color);
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: opacity 0.3s;
    }

    .dashboard-card .cms-button:hover {
        opacity: 0.9;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            margin: 0 2rem;
        }
    }
</style>

</body>
</html> 