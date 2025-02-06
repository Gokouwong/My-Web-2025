<?php
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

// Handle status updates
if (isset($_POST['update_status'])) {
    $id = $_POST['upload_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE user_uploads SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

// Handle CSV Export
if (isset($_POST['export_csv'])) {
    // Get the site URL with proper protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $site_url = $protocol . $_SERVER['HTTP_HOST'];

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="uploads_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV headers
    fputcsv($output, array('ID', 'Title', 'User Name', 'Email', 'Image URL', 'Status', 'Upload Date'));
    
    // Fetch all uploads
    $query = "SELECT * FROM user_uploads ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        // Create full image URL
        $image_url = $site_url . '/' . ltrim($row['image_path'], '/');

        // Prepare row data
        $csvRow = array(
            $row['id'],
            $row['title'],
            $row['user_name'],
            $row['user_email'],
            $image_url,
            $row['status'],
            $row['created_at']
        );
        
        fputcsv($output, $csvRow);
    }
    
    // Close the output stream
    fclose($output);
    exit();
}

// Fetch all uploads
$sql = "SELECT * FROM user_uploads ORDER BY created_at DESC";
$uploads = $conn->query($sql);

require_once 'includes/header.php';
?>

<div class="cms-container">
    <h1 class="cms-mb">Manage User Uploads</h1>

    <!-- Export Button -->
    <form method="POST" style="margin-bottom: 1rem;">
        <button type="submit" name="export_csv" class="cms-button export-button">
            Export to CSV
        </button>
    </form>

    <div class="cms-content">
        <table class="cms-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($upload = $uploads->fetch_assoc()): ?>
                <tr>
                    <td><img src="../<?php echo htmlspecialchars($upload['image_path']); ?>" alt="Upload" style="max-width: 100px;"></td>
                    <td><?php echo htmlspecialchars($upload['title']); ?></td>
                    <td><?php echo htmlspecialchars($upload['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($upload['user_email']); ?></td>
                    <td><?php echo htmlspecialchars($upload['status']); ?></td>
                    <td><?php echo htmlspecialchars($upload['created_at']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="upload_id" value="<?php echo $upload['id']; ?>">
                            <select name="status" class="cms-form">
                                <option value="pending" <?php echo $upload['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $upload['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $upload['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                            <button type="submit" name="update_status" class="cms-button">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.cms-container {
    max-width: calc(100% - 4rem);
    margin: 2rem auto;
    padding: 2rem;
}

.export-button {
    background-color: #28a745;
    margin-bottom: 2rem;
}

.export-button:hover {
    background-color: #218838;
}
</style>

</body>
</html> 