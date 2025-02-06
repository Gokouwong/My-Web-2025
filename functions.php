<?php
echo "<!-- Loading functions.php -->\n";
require_once 'admin/config.php';

// Function to get page content
function getPageContent($page) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM website_content WHERE page = ?");
    $stmt->bind_param("s", $page);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get all active products
function getProducts() {
    global $conn;
    $result = $conn->query("SELECT * FROM products WHERE active = true ORDER BY id");
    $products = [];
    while($row = $result->fetch_assoc()) {
        // Debug image path
        error_log("Product ID: " . $row['id'] . ", Image path: " . $row['image_path']);
        $products[] = $row;
    }
    return $products;
}

// Function to get a single product
function getProduct($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND active = true");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Safely render HTML content from the database
 */
function renderContent($content) {
    // Ensure content is properly decoded for HTML entities
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Get the site URL with proper protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $site_url = $protocol . $_SERVER['HTTP_HOST'];
    
    // Process image paths if they're relative
    $content = preg_replace_callback(
        '/<img[^>]+src=([\'"])((?:\.\.\/|\/)?uploads\/(?:content\/)?[^"\']+)\1/i',
        function($matches) use ($site_url) {
            $path = $matches[2];
            // Remove '../' or leading '/' if it exists at the start of the path
            $path = ltrim($path, './');
            // Add site URL if path is relative
            if (!preg_match('/^https?:\/\//', $path)) {
                $path = $site_url . '/' . $path;
            }
            return str_replace($matches[2], $path, $matches[0]);
        },
        $content
    );
    
    return $content;
}

function embedVideo($url) {
    if (empty($url)) return '';
    
    $url = trim($url);
    error_log("Processing video URL: " . $url);
    
    // Function to determine video ratio from URL parameters
    function getVideoRatio($url) {
        if (strpos($url, 'ratio=8-10') !== false) return 'ratio-8-10';
        if (strpos($url, 'ratio=9-16') !== false) return 'ratio-9-16';
        if (strpos($url, 'ratio=4-3') !== false) return 'ratio-4-3';
        return 'ratio-16-9'; // Default ratio
    }
    
    $ratio_class = getVideoRatio($url);
    $container_class = 'video-container ' . $ratio_class;
    
    // YouTube
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        // Extract video ID
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $id) ||
            preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $id) ||
            preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $id)) {
            
            $video_id = $id[1];
            error_log("YouTube ID found: " . $video_id);
            
            return sprintf(
                '<div class="%s">
                    <iframe 
                        src="https://www.youtube.com/embed/%s"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>',
                $container_class,
                htmlspecialchars($video_id)
            );
        }
    }
    
    // Vimeo
    if (strpos($url, 'vimeo.com') !== false) {
        // Extract video ID
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $id)) {
            $video_id = $id[1];
            error_log("Vimeo ID found: " . $video_id);
            
            return sprintf(
                '<div class="%s">
                    <iframe 
                        src="https://player.vimeo.com/video/%s"
                        frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>',
                $container_class,
                htmlspecialchars($video_id)
            );
        }
    }
    
    error_log("No video ID found for URL: " . $url);
    return ''; // Return empty if no valid video URL found
}

function handleImageUpload($file, $event_id, $type = 'main') {
    global $conn;
    
    $upload_dir = dirname(__FILE__) . '/uploads/past_events/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file['name']);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $image_path = 'uploads/past_events/' . $filename;
        
        if ($type === 'main') {
            $stmt = $conn->prepare("UPDATE past_events SET main_image = ? WHERE id = ?");
            $stmt->bind_param("si", $image_path, $event_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO event_gallery (event_id, image_path) VALUES (?, ?)");
            $stmt->bind_param("is", $event_id, $image_path);
            $stmt->execute();
        }
        return true;
    }
    return false;
}

function handleGalleryUploads($files, $event_id) {
    if (!is_array($files['name'])) {
        return false;
    }

    $total = count($files['name']);
    
    for ($i = 0; $i < $total; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file = array(
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            );
            
            // Debug log
            error_log("Processing gallery image: " . print_r($file, true));
            
            if (!handleImageUpload($file, $event_id, 'gallery')) {
                error_log("Failed to upload gallery image: " . $file['name']);
            }
        }
    }

    return true;
}
?> 