<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    die('Unauthorized');
}

try {
    $conn->begin_transaction();

    // Get all events ordered by created date
    $result = $conn->query("SELECT id FROM past_events ORDER BY created_at DESC");
    $position = 1;
    
    while ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE past_events SET position = ? WHERE id = ?");
        $stmt->bind_param("ii", $position, $row['id']);
        $stmt->execute();
        $position++;
    }

    // Do the same for banners
    $result = $conn->query("SELECT id FROM banners ORDER BY id DESC");
    $position = 1;
    
    while ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE banners SET position = ? WHERE id = ?");
        $stmt->bind_param("ii", $position, $row['id']);
        $stmt->execute();
        $position++;
    }

    $conn->commit();
    echo "Positions fixed successfully";

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} 