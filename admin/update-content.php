<?php
require_once 'config.php';
require_once '../functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = '';
$error = '';

// Define which page you want to update. Change this if you plan to update multiple pages.
$page = 'home_intro';

// Process form submission if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // If "remove image" is checked, remove the current image
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === 'on') {
        $stmtImg = $conn->prepare("SELECT image FROM website_content WHERE page = ?");
        $stmtImg->bind_param("s", $page);
        $stmtImg->execute();
        $resImg = $stmtImg->get_result()->fetch_assoc();
        if ($resImg && !empty($resImg['image'])) {
            $currentImagePath = dirname(dirname(__FILE__)) . '/' . $resImg['image'];
            if (file_exists($currentImagePath)) {
                unlink($currentImagePath);
            }
            // Clear image field in the database
            $stmtUpdate = $conn->prepare("UPDATE website_content SET image = '' WHERE page = ?");
            $stmtUpdate->bind_param("s", $page);
            $stmtUpdate->execute();
        }
    } else {
        // Handle image upload if present
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $upload_dir = dirname(dirname(__FILE__)) . '/uploads/images/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file = $_FILES['image'];
            $fileName = uniqid() . '_' . basename($file['name']);
            $targetPath = $upload_dir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/images/' . $fileName;
                // Update image path in the database
                $stmt = $conn->prepare("UPDATE website_content SET image = ? WHERE page = ?");
                $stmt->bind_param("ss", $imagePath, $page);
                $stmt->execute();
            }
        }
    }

    // Update title and content in the database
    $stmt = $conn->prepare("UPDATE website_content SET title = ?, content = ? WHERE page = ?");
    $stmt->bind_param("sss", $title, $content, $page);
    
    
    if ($stmt->execute()) {
        $message = ucfirst($page) . " page updated successfully!";
    } else {
        $error = "Error updating content: " . $conn->error;
    }
}

// Fetch current content from the database
$stmt = $conn->prepare("SELECT * FROM website_content WHERE page = ?");
$stmt->bind_param("s", $page);
$stmt->execute();
$contentData = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Content</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Container for the content editing page */
        .cms-container {
            max-width: calc(100% - 20rem); /* 10rem padding on each side */
            margin: 0 auto;
            padding: 2rem 10rem;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }
        
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .cms-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            font-weight: bold;
            margin-bottom: 0.5rem;
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
        
        .current-image {
            margin: 1rem 0;
        }
        
        .current-image img {
            max-width: 200px;
            display: block;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<!-- Include CKEditor -->
<script src="https://cdn.ckeditor.com/4.20.1/full/ckeditor.js"></script>

<div class="cms-container">
    <h1>Update Content</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="cms-form" enctype="multipart/form-data">
        <!-- Hidden field with page key -->
        <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">
        
        <div class="form-group">
        <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($contentData['title'] ?? ''); ?>" required>
         </div>
        
        <div class="form-group">
        <label for="content">Content:</label>
            <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($contentData['content'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="image">Image (optional):</label>
            <input type="file" id="image" name="image" accept="image/*">
            <?php if (!empty($contentData['image'])): ?>
                <div class="current-image">
                    <img src="../<?php echo htmlspecialchars($contentData['image']); ?>" alt="Current Image">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="remove_image"> Remove current image</label>
                </div>
            <?php endif; ?>
        </div>
        
        <button type="submit" name="update_content" class="cms-button">Update Content</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (CKEDITOR.instances['content']) {
        CKEDITOR.instances['content'].destroy();
    }
    
    CKEDITOR.replace('content', {
        height: '400px',
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
        allowedContent: true,
    });
});
</script>

</body>
</html> 