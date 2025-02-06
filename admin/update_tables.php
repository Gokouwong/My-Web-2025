<?php
require_once 'config.php';

try {
    // Update past_events table
    $sql = "ALTER TABLE past_events 
            MODIFY title VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            MODIFY intro TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            MODIFY content TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "Successfully updated past_events table<br>";
    } else {
        throw new Exception("Error updating past_events table: " . $conn->error);
    }

    // Update event_gallery table
    $sql = "ALTER TABLE event_gallery 
            MODIFY image_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "Successfully updated event_gallery table<br>";
    } else {
        throw new Exception("Error updating event_gallery table: " . $conn->error);
    }

    echo "All tables updated successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 