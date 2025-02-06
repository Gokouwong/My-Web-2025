<?php
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
require_once 'config.php';

// Debug logging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content Type: " . $_SERVER['CONTENT_TYPE']);

// Set proper content type
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    error_log("Unauthorized access attempt");
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

try {
    // Handle both regular POST and JSON POST
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = [
            'table' => $_POST['table'],
            'order' => json_decode($_POST['order_data'], true)
        ];
    }

    error_log("Decoded data: " . print_r($data, true));

    if (!isset($data['table']) || !isset($data['order']) || !is_array($data['order'])) {
        throw new Exception('Invalid data format');
    }

    // Validate table name
    if ($data['table'] !== 'past_events' && $data['table'] !== 'banners') {
        throw new Exception('Invalid table name');
    }

    // Start transaction
    $conn->begin_transaction();

    // Update positions
    $stmt = $conn->prepare("UPDATE {$data['table']} SET position = ? WHERE id = ?");
    
    foreach ($data['order'] as $item) {
        if (!isset($item['id']) || !isset($item['position'])) {
            throw new Exception('Invalid order data structure');
        }
        
        $stmt->bind_param('ii', $item['position'], $item['id']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update position for ID {$item['id']}");
        }
    }

    // Commit changes
    $conn->commit();
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Error in update-order.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 