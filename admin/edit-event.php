<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once '../functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';
$event = null;

// Fetch event data if editing
if (isset($_GET['id'])) {
    // First, ensure the table supports UTF-8
    $conn->query("ALTER TABLE past_events CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->query("ALTER TABLE event_gallery CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $stmt = $conn->prepare("SELECT * FROM past_events WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    // Fetch gallery images
    $gallery_stmt = $conn->prepare("SELECT * FROM event_gallery WHERE event_id = ?");
    $gallery_stmt->bind_param("i", $_GET['id']);
    $gallery_stmt->execute();
    $gallery_images = $gallery_stmt->get_result();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle gallery image removal
    if (isset($_POST['remove_images'])) {
        $image_ids = $_POST['selected_images'] ?? [];
        if (!empty($image_ids)) {
            foreach ($image_ids as $image_id) {
                $image_id = (int)$image_id;
                
                // Get image path before deleting
                $stmt = $conn->prepare("SELECT image_path FROM event_gallery WHERE id = ? AND event_id = ?");
                $stmt->bind_param("ii", $image_id, $_GET['id']);
                $stmt->execute();
                $image = $stmt->get_result()->fetch_assoc();
                
                if ($image) {
                    // Delete physical file
                    $filepath = dirname(dirname(__FILE__)) . '/' . $image['image_path'];
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                    
                    // Delete from database
                    $stmt = $conn->prepare("DELETE FROM event_gallery WHERE id = ? AND event_id = ?");
                    $stmt->bind_param("ii", $image_id, $_GET['id']);
                    $stmt->execute();
                }
            }
            header("Location: edit-event?id=" . $_GET['id'] . "&message=Images removed");
            exit();
        }
    }

    try {
        // Get form data and properly handle UTF-8
        $title = $_POST['title'] ?? '';
        $intro = $_POST['intro'] ?? '';
        $content = $_POST['content'] ?? '';
        $video_url = $_POST['video_url'] ?? '';
        
        error_log("Processing video URL in edit-event: " . $video_url);
        
        if (empty($title)) {
            throw new Exception("Title is required");
        }

        $conn->begin_transaction();

        if (isset($_GET['id'])) {
            // Update existing event
            $event_id = (int)$_GET['id'];
            $query = "UPDATE past_events SET 
                     title = '" . $conn->real_escape_string($title) . "',
                     intro = '" . $conn->real_escape_string($intro) . "',
                     content = '" . $conn->real_escape_string($content) . "',
                     video_url = '" . $conn->real_escape_string($video_url) . "',
                     updated_at = NOW() 
                     WHERE id = " . $event_id;
            if (!$conn->query($query)) {
                throw new Exception("Failed to update event: " . $conn->error);
            }
        } else {
            // Create new event
            $query = "INSERT INTO past_events (title, intro, content, video_url) VALUES (
                     '" . $conn->real_escape_string($title) . "',
                     '" . $conn->real_escape_string($intro) . "',
                     '" . $conn->real_escape_string($content) . "',
                     '" . $conn->real_escape_string($video_url) . "'
                     )";
            if (!$conn->query($query)) {
                throw new Exception("Failed to create event: " . $conn->error);
            }
            $event_id = $conn->insert_id;
        }

        // Handle main image upload
        if (isset($_FILES['main_image']) && $_FILES['main_image']['size'] > 0) {
            $upload_dir = dirname(dirname(__FILE__)) . '/uploads/events/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = uniqid() . '_' . basename($_FILES['main_image']['name']);
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $filepath)) {
                $image_path = 'uploads/events/' . $filename;
                $stmt = $conn->prepare("UPDATE past_events SET main_image = ? WHERE id = ?");
                $stmt->bind_param("si", $image_path, $event_id);
                $stmt->execute();
            }
        }

        // Handle gallery images
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $upload_dir = dirname(dirname(__FILE__)) . '/uploads/events/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . '_' . basename($_FILES['gallery_images']['name'][$key]);
                    $filepath = $upload_dir . $filename;

                    if (move_uploaded_file($tmp_name, $filepath)) {
                        $image_path = 'uploads/events/' . $filename;
                        $stmt = $conn->prepare("INSERT INTO event_gallery (event_id, image_path) VALUES (?, ?)");
                        $stmt->bind_param("is", $event_id, $image_path);
                        $stmt->execute();
                    }
                }
            }
        }

        $conn->commit();
        header("Location: manage-events");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo isset($_GET['id']) ? 'Edit Event' : 'Add New Event'; ?></title>
    <script src="https://cdn.ckeditor.com/4.20.1/full/ckeditor.js"></script>
    <style>
        .cms-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .current-image {
            margin: 1rem 0;
        }
        .current-image img {
            max-width: 200px;
            border-radius: 4px;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .cms-button {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cms-button:hover {
            background-color: #0056b3;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .gallery-item {
            position: relative;
            border-radius: 4px;
            overflow: hidden;
        }
        .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .gallery-item-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            transform: scale(1.5);
        }
        .gallery-actions {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .remove-selected {
            background-color: #dc3545;
        }
        .remove-selected:hover {
            background-color: #c82333;
        }
        .select-all-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .form-text {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="cms-container">
    <h1><?php echo isset($_GET['id']) ? 'Edit Event' : 'Add New Event'; ?></h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="main-form">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="intro">Introduction:</label>
            <textarea id="intro" name="intro" rows="3"><?php echo htmlspecialchars($event['intro'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="content">Content:</label>
            <textarea id="content" name="content"><?php echo htmlspecialchars($event['content'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="video_url">Video URL:</label>
            <input type="url" id="video_url" name="video_url" value="<?php echo htmlspecialchars($event['video_url'] ?? ''); ?>">
            <small class="form-text">Supports YouTube and Vimeo URLs (e.g., https://www.youtube.com/watch?v=xxxxx or https://vimeo.com/xxxxx)</small>
        </div>

        <div class="form-group">
            <label for="main_image">Main Image:</label>
            <input type="file" id="main_image" name="main_image" accept="image/*">
            <?php if (!empty($event['main_image'])): ?>
                <div class="current-image">
                    <img src="../<?php echo htmlspecialchars($event['main_image']); ?>" alt="Current main image">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="gallery_images">Gallery Images:</label>
            <input type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple>
            <small>Hold Ctrl (Windows) or Command (Mac) to select multiple images</small>
            
            <?php if (isset($_GET['id'])): ?>
                <div class="gallery-grid">
                    <?php if (isset($gallery_images) && $gallery_images->num_rows > 0): ?>
                        <?php while ($image = $gallery_images->fetch_assoc()): ?>
                            <div class="gallery-item">
                                <input type="checkbox" name="selected_images[]" 
                                       value="<?php echo $image['id']; ?>" 
                                       class="gallery-item-checkbox">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     alt="Gallery image">
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($gallery_images) && $gallery_images->num_rows > 0): ?>
                    <div class="gallery-actions">
                        <label class="select-all-label">
                            <input type="checkbox" id="select-all">
                            Select All
                        </label>
                        <button type="button" id="remove-selected" 
                                class="cms-button remove-selected" 
                                onclick="removeSelectedImages()">
                            Remove Selected
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <button type="submit" class="cms-button">Save Event</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CKEditor initialization
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('content', {
            height: '400px',
            fullPage: false,
            allowedContent: true,
            entities: false,
            toolbar: [
                { name: 'document', items: [ 'Source' ] },
                { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', '-', 'Undo', 'Redo' ] },
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'insert', items: [ 'Table', 'HorizontalRule', 'SpecialChar' ] },
                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] }
            ],
            font_names: 'Arial/Arial, Helvetica, sans-serif;' +
                       'Times New Roman/Times New Roman, Times, serif;' +
                       'Microsoft YaHei/Microsoft YaHei, 微软雅黑;' +
                       'SimSun/SimSun, 宋体;' +
                       'SimHei/SimHei, 黑体;' +
                       'MingLiU/MingLiU, 細明體;' +
                       'PMingLiU/PMingLiU, 新細明體;' +
                       'DFKai-SB/DFKai-SB, 標楷體;',
        });
    } else {
        console.error('CKEditor not loaded');
    }

    // Handle select all functionality
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.gallery-item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Handle main form submission
    document.getElementById('main-form').addEventListener('submit', function(e) {
        if (CKEDITOR.instances.content) {
            document.getElementById('content').value = CKEDITOR.instances.content.getData();
        }
    });
});

// Function to handle image removal
function removeSelectedImages() {
    if (confirm('Are you sure you want to remove selected images?')) {
        const form = document.getElementById('main-form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_images';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    }
}
</script>

</body>
</html> 