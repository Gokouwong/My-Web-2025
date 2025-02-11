<?php
require_once 'admin/config.php';
require_once 'functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/user_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $userName = trim($_POST['user_name'] ?? '');
    $userEmail = trim($_POST['user_email'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate inputs
    if (empty($userName) || empty($userEmail) || empty($title)) {
        $error = "Please fill in all required fields.";
    } else if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select an image to upload.";
    } else {
        $file = $_FILES['image'];
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;

        // Verify file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = "Only JPG, PNG and GIF files are allowed.";
        } else if ($file['size'] > 5000000) { // 5MB limit
            $error = "File is too large. Maximum size is 5MB.";
        } else if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Save to database
            $stmt = $conn->prepare("INSERT INTO user_uploads (image_path, title, description, user_name, user_email) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $targetPath, $title, $description, $userName, $userEmail);
            
            if ($stmt->execute()) {
                $message = "Image uploaded successfully! It will be reviewed by our team.";
            } else {
                $error = "Error saving to database.";
            }
            $stmt->close();
        } else {
            $error = "Error uploading file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images - MY WEB</title>
    <!--add icon link-->
    <link rel="icon" href="uploads/user_images/RAKKO.jpg" type="image/x-icon">
   
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
            padding-top: 60px;
            background_color: #333
        }

        .upload-container {
            background: #99ebff;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <style>
            body{
                background-color: #c2c2d6;
            }
        </style>
        <div class="page-content upload-container">
            <h1>Share Your Image</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
                <div>
                    <label for="user_name">Your Name *</label>
                    <input type="text" id="user_name" name="user_name" required 
                           style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div>
                    <label for="user_email">Your Email *</label>
                    <input type="email" id="user_email" name="user_email" required
                           style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div>
                    <label for="title">Image Title *</label>
                    <input type="text" id="title" name="title" required
                           style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div>
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                              style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>

                <div>
                    <label for="image">Select Image *</label>
                    <input type="file" id="image" name="image" required accept="image/*"
                           style="width: 100%; padding: 0.5rem; margin-top: 0.25rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <button type="submit" 
                        style="background: #007bff; color: white; padding: 0.75rem; border: none; border-radius: 4px; cursor: pointer;">
                    Upload Image
                </button>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>