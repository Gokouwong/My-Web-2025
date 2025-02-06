<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    die('Unauthorized');
}

try {
    // Check past_events table
    $result = $conn->query("SHOW COLUMNS FROM past_events LIKE 'position'");
    echo "Position column in past_events: " . ($result->num_rows > 0 ? "exists" : "missing") . "<br>";
    
    if ($result->num_rows > 0) {
        $events = $conn->query("SELECT id, title, position FROM past_events ORDER BY position ASC");
        echo "<h3>Past Events Positions:</h3>";
        while ($row = $events->fetch_assoc()) {
            echo "ID: {$row['id']}, Title: {$row['title']}, Position: {$row['position']}<br>";
        }
    }
    
    // Check banners table
    $result = $conn->query("SHOW COLUMNS FROM banners LIKE 'position'");
    echo "<br>Position column in banners: " . ($result->num_rows > 0 ? "exists" : "missing") . "<br>";
    
    if ($result->num_rows > 0) {
        $banners = $conn->query("SELECT id, position FROM banners ORDER BY position ASC");
        echo "<h3>Banners Positions:</h3>";
        while ($row = $banners->fetch_assoc()) {
            echo "ID: {$row['id']}, Position: {$row['position']}<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 