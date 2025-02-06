<?php
require_once 'config.php';
require_once '../functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_name = $_POST['page_name'];
    $background_type = $_POST['background_type'];
    $background_value = '';

    error_log("Saving background - Page: " . $page_name);
    error_log("Background type: " . $background_type);

    if ($background_type === 'color') {
        $background_value = $_POST['background_color'];
        error_log("Color value: " . $background_value);
    } else if ($background_type === 'image' && isset($_FILES['background_image'])) {
        $uploadDir = '../uploads/backgrounds/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['background_image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['background_image']['tmp_name'], $targetPath)) {
            $background_value = 'uploads/backgrounds/' . $fileName;
            error_log("Image path: " . $background_value);
        }
    }

    if ($background_value) {
        $stmt = $conn->prepare("REPLACE INTO page_backgrounds (page_name, background_type, background_value) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $page_name, $background_type, $background_value);
        $stmt->execute();
        
        header("Location: background-settings?page=" . urlencode($page_name));
        exit;
    }
}

// Get current settings
$backgrounds = [];
$result = $conn->query("SELECT * FROM page_backgrounds");
while ($row = $result->fetch_assoc()) {
    $backgrounds[$row['page_name']] = $row;
}

$selected_page = $_GET['page'] ?? 'index';
$current_background = $backgrounds[$selected_page] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Background Settings - CMS</title>
    <style>
        .background-form {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #ddd;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .preview {
            width: 200px;
            height: 100px;
            border: 1px solid #ddd;
            margin-top: 1rem;
        }
        .current-background {
            margin-top: 1rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .current-image {
            max-width: 200px;
            max-height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <h1>Background Settings</h1>

    <form method="POST" enctype="multipart/form-data" class="background-form">
        <div class="form-group">
            <label for="page_name">Select Page:</label>
            <select name="page_name" id="page_name" required>
                <option value="index" <?php echo isset($_GET['page']) && $_GET['page'] == 'index' ? 'selected' : ''; ?>>Home</option>
                <option value="about" <?php echo isset($_GET['page']) && $_GET['page'] == 'about' ? 'selected' : ''; ?>>About</option>
                <option value="past-events" <?php echo isset($_GET['page']) && $_GET['page'] == 'past-events' ? 'selected' : ''; ?>>Past Events</option>
                <option value="products-list" <?php echo isset($_GET['page']) && $_GET['page'] == 'products-list' ? 'selected' : ''; ?>>Products</option>
                <option value="contact" <?php echo isset($_GET['page']) && $_GET['page'] == 'contact' ? 'selected' : ''; ?>>Contact</option>
                <option value="upload" <?php echo isset($_GET['page']) && $_GET['page'] == 'upload' ? 'selected' : ''; ?>>Upload</option>
            </select>
        </div>

        <?php if ($current_background): ?>
        <div class="current-background">
            <h3>Current Background</h3>
            <?php if ($current_background['background_type'] === 'color'): ?>
                <p>Type: Color</p>
                <div style="width: 100px; height: 100px; background-color: <?php echo htmlspecialchars($current_background['background_value']); ?>"></div>
            <?php else: ?>
                <p>Type: Image</p>
                <img src="<?php echo htmlspecialchars('../' . $current_background['background_value']); ?>" class="current-image" alt="Current background">
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Background Type:</label>
            <input type="radio" name="background_type" value="color" <?php echo (!$current_background || $current_background['background_type'] === 'color') ? 'checked' : ''; ?>> Color
            <input type="radio" name="background_type" value="image" <?php echo ($current_background && $current_background['background_type'] === 'image') ? 'checked' : ''; ?>> Image
        </div>

        <div class="form-group color-input">
            <label for="background_color">Background Color:</label>
            <input type="color" name="background_color" id="background_color" 
                   value="<?php echo ($current_background && $current_background['background_type'] === 'color') ? htmlspecialchars($current_background['background_value']) : '#ffffff'; ?>">
        </div>

        <div class="form-group image-input" style="display:none;">
            <label for="background_image">Background Image:</label>
            <input type="file" name="background_image" id="background_image" accept="image/*">
        </div>

        <button type="submit">Save Background</button>
    </form>

    <script>
        document.querySelectorAll('input[name="background_type"]').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelector('.color-input').style.display = 
                    this.value === 'color' ? 'block' : 'none';
                document.querySelector('.image-input').style.display = 
                    this.value === 'image' ? 'block' : 'none';
            });
        });

        document.getElementById('page_name').addEventListener('change', function() {
            window.location.href = 'background-settings?page=' + this.value;
        });

        window.addEventListener('load', function() {
            const currentType = document.querySelector('input[name="background_type"]:checked').value;
            document.querySelector('.color-input').style.display = 
                currentType === 'color' ? 'block' : 'none';
            document.querySelector('.image-input').style.display = 
                currentType === 'image' ? 'block' : 'none';
        });
    </script>
</body>
</html> 