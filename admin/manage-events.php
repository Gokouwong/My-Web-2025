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

// Handle event deletion
if (isset($_POST['delete_event'])) {
    $id = (int)$_POST['event_id'];
    // Delete associated gallery images first
    $stmt = $conn->prepare("SELECT image_path FROM event_gallery WHERE event_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $filepath = dirname(dirname(__FILE__)) . '/' . $row['image_path'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Delete the main event image
    $stmt = $conn->prepare("SELECT main_image FROM past_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    
    if ($event && !empty($event['main_image'])) {
        $filepath = dirname(dirname(__FILE__)) . '/' . $event['main_image'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Delete the event and its gallery (cascade will handle gallery deletion)
    $conn->query("DELETE FROM past_events WHERE id = $id");
    
    header('Location: manage-events');
    exit();
}

// Handle position updates
if (isset($_POST['action']) && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];
    $action = $_POST['action'];
    
    // Get current position
    $stmt = $conn->prepare("SELECT position FROM past_events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    
    if ($current) {
        $new_position = $current['position'];
        
        if ($action === 'move_up') {
            // Find the item above
            $stmt = $conn->prepare("SELECT id, position FROM past_events WHERE position < ? ORDER BY position DESC LIMIT 1");
            $stmt->bind_param("i", $current['position']);
        } else {
            // Find the item below
            $stmt = $conn->prepare("SELECT id, position FROM past_events WHERE position > ? ORDER BY position ASC LIMIT 1");
            $stmt->bind_param("i", $current['position']);
        }
        
        $stmt->execute();
        $swap_with = $stmt->get_result()->fetch_assoc();
        
        if ($swap_with) {
            // Swap positions
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE past_events SET position = ? WHERE id = ?");
                
                // Update current item
                $stmt->bind_param("ii", $swap_with['position'], $event_id);
                $stmt->execute();
                
                // Update swapped item
                $stmt->bind_param("ii", $current['position'], $swap_with['id']);
                $stmt->execute();
                
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Error updating positions: " . $e->getMessage());
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: manage-events");
    exit();
}

// Fetch all events
$events = $conn->query("SELECT *, COALESCE(position, id) as sort_position FROM past_events ORDER BY sort_position ASC, created_at DESC");

require_once 'includes/header.php';
?>

<div class="cms-container">
    <h1 class="cms-mb">Manage Past Events</h1>
    
    <div class="cms-actions">
        <a href="edit-event" class="cms-button">Add New Event</a>
    </div>

    <div class="cms-content">
        <table class="cms-table sortable">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Intro</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="sortable-body">
                <?php while ($event = $events->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="position-controls">
                            <form method="POST" action="manage-events" style="display: inline;">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <input type="hidden" name="action" value="move_up">
                                <button type="submit" class="position-btn">↑</button>
                            </form>
                            <form method="POST" action="manage-events" style="display: inline;">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <input type="hidden" name="action" value="move_down">
                                <button type="submit" class="position-btn">↓</button>
                            </form>
                        </div>
                    </td>
                    <td class="event-image">
                        <?php if (!empty($event['main_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($event['main_image']); ?>" 
                                 alt="Event image">
                        <?php else: ?>
                            <span class="no-image">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                    <td><?php echo htmlspecialchars(substr($event['intro'] ?? '', 0, 100)) . '...'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($event['created_at'])); ?></td>
                    <td>
                        <a href="edit-event?id=<?php echo $event['id']; ?>" 
                           class="cms-button">Edit</a>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this event?');">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" name="delete_event" class="cms-button delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Fallback form for updating positions -->
        <form id="updateOrderForm" action="update-order.php" method="POST" style="display:none;">
            <input type="hidden" name="table" value="past_events">
            <input type="hidden" name="order_data" value="">
        </form>
    </div>
</div>

<style>
.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.cms-container {
    max-width: calc(100% - 20rem);
    margin: 2rem auto;
    padding: 2rem;
}

.cms-actions {
    margin-bottom: 2rem;
}

.event-image {
    width: 100px;
}

.event-image img {
    width: 100px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
}

.no-image {
    display: inline-block;
    width: 100px;
    height: 60px;
    line-height: 60px;
    text-align: center;
    background: #f0f0f0;
    border-radius: 4px;
    color: #666;
}

.delete {
    background-color: #dc3545;
}
.delete:hover {
    background-color: #c82333;
}

.sortable .sort-handle {
    cursor: move;
    color: #999;
    width: 30px;
    text-align: center;
}

.sortable tr.dragging {
    background: #f8f9fa;
    opacity: 0.5;
}

.sortable tr.drop-target {
    border-top: 2px solid #007bff;
}

.position-controls {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.position-btn {
    padding: 0.25rem 0.5rem;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
}

.position-btn:hover {
    background: #e9ecef;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var $tbody = $('.sortable-body');
    var currentPath = window.location.pathname;
    var adminPath = currentPath.includes('/admin/') ? '/admin/' : '/';
    
    if ($tbody.length) {
        new Sortable($tbody[0], {
            handle: '.sort-handle',
            animation: 150,
            onEnd: function() {
                var positions = [];
                $tbody.find('tr').each(function(index) {
                    positions.push({
                        id: $(this).data('id'),
                        position: index + 1
                    });
                });

                $.ajax({
                    url: './update-order.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        table: 'past_events',
                        order: positions
                    }),
                    beforeSend: function() {
                        console.log('Sending request to:', this.url);
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', 'Order updated successfully');
                            window.location.reload();
                        } else {
                            showAlert('danger', 'Failed to update order: ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', error);
                        console.log('Status:', status);
                        console.log('Response:', xhr.responseText);
                        console.log('URL attempted:', this.url);
                        showAlert('danger', 'Error updating order: ' + error);
                    }
                });
            }
        });
    }

    function showAlert(type, message) {
        var $alert = $('<div>', {
            class: 'alert alert-' + type,
            text: message
        });

        $('.cms-container').find('.alert').remove();
        $('.cms-container').prepend($alert);

        setTimeout(function() {
            $alert.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>
</body>
</html> 