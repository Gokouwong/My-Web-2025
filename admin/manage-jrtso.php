<?php
require_once 'config.php';

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Handle updates
if (isset($_POST['update_jrtso'])) {
    try {
        $id = $_POST['jrtso_id'];
        $number = $_POST['number'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        $stmt = $conn->prepare("UPDATE jrtso_listings SET number = ?, title = ?, description = ? WHERE id = ?");
        $stmt->bind_param("issi", $number, $title, $description, $id);
        
        if ($stmt->execute()) {
            $message = "JRTSO listing updated successfully!";
        } else {
            throw new Exception("Error updating JRTSO listing: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all JRTSO listings
$result = $conn->query("SELECT * FROM jrtso_listings ORDER BY number");
$listings = [];
while($row = $result->fetch_assoc()) {
    $listings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage JRTSO Listings</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .jrtso-section {
            background: white;
            padding: 30px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 800px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .update-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .update-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage JRTSO Listings</h1>
            <a href="dashboard.php">Back to Dashboard</a>
        </div>

        <?php if(isset($message)): ?>
            <p class="success"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php foreach($listings as $item): ?>
        <div class="jrtso-section">
            <form method="POST" class="edit-jrtso-form">
                <input type="hidden" name="jrtso_id" value="<?php echo $item['id']; ?>">
                
                <div class="form-group">
                    <label>Number:</label>
                    <input type="number" name="number" value="<?php echo htmlspecialchars($item['number']); ?>" required min="1" max="10">
                </div>
                
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" rows="4" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>
                
                <button type="submit" name="update_jrtso" class="update-btn">Update JRTSO</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html> 