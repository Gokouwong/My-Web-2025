<?php
require_once 'config.php';
require_once '../functions.php';

// Check if admin is logged in
// session_start();  // <- Remove this line
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle banner updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle new banner creation
    if (isset($_POST['add_banner']) && isset($_FILES['banner_image'])) {
        $uploadDir = '../uploads/banners/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Get the highest position
        $result = $conn->query("SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM banners");
        $next_position = $result->fetch_assoc()['next_position'];
        
        $file = $_FILES['banner_image'];
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/banners/' . $fileName;
            $stmt = $conn->prepare("INSERT INTO banners (image_path, active, position) VALUES (?, 0, ?)");
            $stmt->bind_param("si", $imagePath, $next_position);
            
            if ($stmt->execute()) {
                $message = "New banner created successfully!";
            } else {
                $error = "Error creating new banner.";
            }
        } else {
            $error = "Error uploading banner image.";
        }
    }
    
    // Handle existing banner updates
    if (isset($_POST['update_banner'])) {
        if (isset($_POST['banner_id'])) {
            $id = $_POST['banner_id'];
            $active = isset($_POST['active']) ? 1 : 0;
            $redirect_url = trim($_POST['redirect_url'] ?? '');
            
            // Add URL validation and formatting
            if (!empty($redirect_url)) {
                // Check if URL starts with http:// or https://
                if (!preg_match("~^(?:f|ht)tps?://~i", $redirect_url)) {
                    $redirect_url = "https://" . $redirect_url;
                }
            }
            
            // Update banner status and redirect URL
            $stmt = $conn->prepare("UPDATE banners SET active = ?, redirect_url = ? WHERE id = ?");
            $stmt->bind_param("isi", $active, $redirect_url, $id);
            
            if ($stmt->execute()) {
                $message = "Banner updated successfully!";
            } else {
                $error = "Error updating banner.";
            }
            
            // Handle image update if new image was uploaded
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['size'] > 0) {
                $uploadDir = '../uploads/banners/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $file = $_FILES['banner_image'];
                $fileName = uniqid() . '_' . basename($file['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $imagePath = 'uploads/banners/' . $fileName;
                    $stmt = $conn->prepare("UPDATE banners SET image_path = ? WHERE id = ?");
                    $stmt->bind_param("si", $imagePath, $id);
                    
                    if ($stmt->execute()) {
                        $message = "Banner image updated successfully!";
                    } else {
                        $error = "Error updating banner image in database.";
                    }
                } else {
                    $error = "Error uploading banner image.";
                }
            }
        } else {
            $error = "Banner ID not provided.";
        }
    }

    // Add this to your POST handler section
    if (isset($_POST['remove_banner']) && isset($_POST['banner_id'])) {
        $id = $_POST['banner_id'];
        
        // First get the image path to delete the file
        $stmt = $conn->prepare("SELECT image_path FROM banners WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $banner = $result->fetch_assoc();
        
        if ($banner && $banner['image_path']) {
            // Delete the physical file
            $file_path = '../' . $banner['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Banner removed successfully!";
        } else {
            $error = "Error removing banner.";
        }
    }

    // Handle position updates
    if (isset($_POST['action']) && isset($_POST['banner_id'])) {
        $banner_id = (int)$_POST['banner_id'];
        $action = $_POST['action'];
        
        // Get current position
        $stmt = $conn->prepare("SELECT position FROM banners WHERE id = ?");
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current = $result->fetch_assoc();
        
        if ($current) {
            $new_position = $current['position'];
            
            if ($action === 'move_up') {
                // Find the item above
                $stmt = $conn->prepare("SELECT id, position FROM banners WHERE position < ? ORDER BY position DESC LIMIT 1");
                $stmt->bind_param("i", $current['position']);
            } else {
                // Find the item below
                $stmt = $conn->prepare("SELECT id, position FROM banners WHERE position > ? ORDER BY position ASC LIMIT 1");
                $stmt->bind_param("i", $current['position']);
            }
            
            $stmt->execute();
            $swap_with = $stmt->get_result()->fetch_assoc();
            
            if ($swap_with) {
                // Swap positions
                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("UPDATE banners SET position = ? WHERE id = ?");
                    
                    // Update current item
                    $stmt->bind_param("ii", $swap_with['position'], $banner_id);
                    $stmt->execute();
                    
                    // Update swapped item
                    $stmt->bind_param("ii", $current['position'], $swap_with['id']);
                    $stmt->execute();
                    
                    $conn->commit();
                } catch (Exception $e) {
                    $conn->rollback();
                    error_log("Error updating positions: " . $e->getMessage());
                }
            }
        }
        
        // Redirect to prevent form resubmission
        header("Location: manage-banners");
        exit();
    }
}

// Fetch all banners
$banners = $conn->query("SELECT *, COALESCE(position, id) as sort_position FROM banners ORDER BY sort_position ASC, id DESC");

require_once 'includes/header.php';
?>

<div class="cms-container">
    <h1 class="cms-mb">Manage Banners</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cms-content">
        <h2>Add New Banner</h2>
        <form method="POST" enctype="multipart/form-data" class="cms-form">
            <div class="form-group">
                <label for="banner_image">Banner Image</label>
                <input type="file" id="banner_image" name="banner_image" accept="image/*" required class="form-control">
            </div>
            <button type="submit" name="add_banner" class="cms-button">Add Banner</button>
        </form>

        <h2 class="cms-mb">Existing Banners</h2>
        <div class="banners-grid">
            <?php while ($banner = $banners->fetch_assoc()): ?>
                <div class="banner-item">
                    <div class="position-controls">
                        <form method="POST" action="manage-banners" style="display: inline;">
                            <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                            <input type="hidden" name="action" value="move_up">
                            <button type="submit" class="position-btn">↑</button>
                        </form>
                        <form method="POST" action="manage-banners" style="display: inline;">
                            <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                            <input type="hidden" name="action" value="move_down">
                            <button type="submit" class="position-btn">↓</button>
                        </form>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="cms-form">
                        <input type="hidden" name="banner_id" value="<?php echo $banner['id']; ?>">
                        
                        <?php if ($banner['image_path']): ?>
                            <img src="../<?php echo htmlspecialchars($banner['image_path']); ?>" 
                                 alt="Banner" class="banner-preview">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="banner_image_<?php echo $banner['id']; ?>">Update Image</label>
                            <input type="file" id="banner_image_<?php echo $banner['id']; ?>" 
                                   name="banner_image" accept="image/*" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="redirect_url_<?php echo $banner['id']; ?>">Redirect URL (include https:// for external links)</label>
                            <input type="text" id="redirect_url_<?php echo $banner['id']; ?>" 
                                   name="redirect_url" 
                                   value="<?php echo htmlspecialchars($banner['redirect_url'] ?? ''); ?>" 
                                   placeholder="Full URL path"
                                   class="form-control">
                            <small class="form-text">Example: https://example.com or /about</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="active" <?php echo $banner['active'] ? 'checked' : ''; ?>>
                                Active
                            </label>
                        </div>
                        
                        <div class="button-group">
                            <button type="submit" name="update_banner" class="cms-button">Update Banner</button>
                            <button type="submit" name="remove_banner" class="cms-button delete-button" 
                                    onclick="return confirm('Are you sure you want to remove this banner?')">
                                Remove Banner
                            </button>
                        </div>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style>
.cms-container {
    max-width: calc(100% - 20rem);
    margin: 0 auto;
    padding: 2rem;
}

.banners-grid {
    display: grid;
    gap: 2rem;
    margin-top: 2rem;
}

.banner-item {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.banner-preview {
    max-width: 100%;
    height: auto;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.button-group {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.delete-button {
    background-color: #dc3545;
}

.delete-button:hover {
    background-color: #c82333;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 0.25rem;
}

.form-control[type="url"] {
    font-family: monospace;
}

@media (max-width: 1200px) {
    .cms-container {
        max-width: calc(100% - 4rem);
    }
}

@media (max-width: 768px) {
    .cms-container {
        max-width: calc(100% - 2rem);
    }
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.position-controls {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.position-btn {
    padding: 0.25rem 0.5rem;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
}

.position-btn:hover {
    background: #e9ecef;
}
</style>

</body>
</html> 