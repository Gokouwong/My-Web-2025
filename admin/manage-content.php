<?php
require_once 'config.php';
require_once '../functions.php';

// Only start session if one hasn't been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle content updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_content'])) {
    try {
        $page = $_POST['page'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        // Check if content exists
        $stmt = $conn->prepare("SELECT id FROM website_content WHERE page = ?");
        $stmt->bind_param("s", $page);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing content
            $stmt = $conn->prepare("UPDATE website_content SET title = ?, content = ? WHERE page = ?");
            $stmt->bind_param("sss", $title, $content, $page);
        } else {
            // Insert new content
            $stmt = $conn->prepare("INSERT INTO website_content (page, title, content) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $page, $title, $content);
        }
        
        if ($stmt->execute()) {
            $message = "Content updated successfully!";
        } else {
            throw new Exception("Error updating content: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        error_log($error);
    }
}

// Fetch all content
$content_pages = $conn->query("SELECT * FROM website_content WHERE page NOT IN ('home_intro') ORDER BY page");

// Include header
require_once 'includes/header.php';
?>

<!-- Include CKEditor -->
<script src="https://cdn.ckeditor.com/4.20.1/full/ckeditor.js"></script>

<div class="cms-container">
    <h1 class="cms-mb">Manage Content</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cms-content">
        <div class="content-pages">
            <?php while ($page = $content_pages->fetch_assoc()): ?>
                <div class="content-item">
                    <h2><?php echo htmlspecialchars(ucfirst(str_replace(['_', 'about', 'contact'], ['', 'About Us', 'Contact Us'], $page['page']))); ?></h2>
                    <form method="POST" class="cms-form">
                        <input type="hidden" name="page" value="<?php echo htmlspecialchars($page['page']); ?>">
                        
                        <div class="form-group">
                            <label for="title_<?php echo $page['id']; ?>">Title:</label>
                            <input type="text" id="title_<?php echo $page['id']; ?>" 
                                   name="title" value="<?php echo htmlspecialchars($page['title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="content_<?php echo $page['id']; ?>">Content:</label>
                            <textarea id="content_<?php echo $page['id']; ?>" 
                                    name="content" 
                                    rows="10" 
                                    required><?php echo htmlspecialchars($page['content']); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_content" class="cms-button">Update Content</button>
                            <button type="button" class="cms-button preview-button" 
                                    onclick="previewContent(<?php echo $page['id']; ?>)">Preview</button>
                        </div>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="previewContent"></div>
    </div>
</div>

<style>
.cms-container {
    max-width: calc(100% - 20rem); /* 10rem margin on each side */
    margin: 0 auto;
    padding: 2rem;
}

.content-pages {
    display: grid;
    gap: 2rem;
}

.content-item {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.content-item h2 {
    margin-bottom: 1.5rem;
    color: #333;
    font-size: 1.5rem;
}

.cms-form {
    max-width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #555;
}

.form-group input[type="text"],
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group textarea {
    min-height: 150px;
    resize: vertical;
}

.cms-button {
    background: #007bff;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.cms-button:hover {
    background: #0056b3;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.preview-button {
    background: #6c757d;
}

.preview-button:hover {
    background: #5a6268;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #333;
}

#previewContent {
    margin-top: 2rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .cms-container {
        max-width: calc(100% - 4rem);
    }
}

@media (max-width: 768px) {
    .cms-container {
        max-width: 100%;
        padding: 1rem;
    }
}

/* CKEditor image alignment */
.image-align-left {
    float: left;
    margin-right: 1rem;
    margin-bottom: 1rem;
}

.image-align-right {
    float: right;
    margin-left: 1rem;
    margin-bottom: 1rem;
}

.image-align-center {
    display: block;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 1rem;
}

/* Ensure images don't overflow their containers */
.cke_editable img {
    max-width: 100%;
    height: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var textareas = document.querySelectorAll('textarea[id^="content_"]');
    textareas.forEach(function(textarea) {
        if (CKEDITOR.instances[textarea.id]) {
            CKEDITOR.instances[textarea.id].destroy();
        }
        
        CKEDITOR.replace(textarea.id, {
            height: '400px',
            toolbar: [
                { name: 'document', items: [ 'Source' ] },
                { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', '-', 'Undo', 'Redo' ] },
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'insert', items: [ 'Table', 'HorizontalRule', 'SpecialChar' ] },
                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] }
            ],
            allowedContent: true,
        });
    });

    // Modal handlers
    var closeBtn = document.querySelector('.close');
    var modal = document.getElementById('previewModal');

    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Update preview function
    window.previewContent = function(id) {
        var content = CKEDITOR.instances['content_' + id].getData();
        var title = document.getElementById('title_' + id).value;
        
        var previewContent = document.getElementById('previewContent');
        previewContent.innerHTML = `
            <h1 style="margin-bottom: 1rem;">${title}</h1>
            ${content}
        `;
        
        modal.style.display = 'block';
    }
});
</script>

</body>
</html> 