<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    die('Unauthorized');
}

try {
    // Add position column to past_events if it doesn't exist
    $conn->query("ALTER TABLE past_events ADD COLUMN IF NOT EXISTS position INT");
    
    // Set initial positions based on created_at if position is NULL
    $conn->query("UPDATE past_events SET position = id WHERE position IS NULL");
    
    // Add position column to banners if it doesn't exist
    $conn->query("ALTER TABLE banners ADD COLUMN IF NOT EXISTS position INT");
    
    // Set initial positions based on id if position is NULL
    $conn->query("UPDATE banners SET position = id WHERE position IS NULL");
    
    echo "Position columns added successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 