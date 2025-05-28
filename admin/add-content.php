<?php
$page_title = "Add content";
$body_class = "admin-add-content-page";
require_once '../includes/config.php';
require_admin_login();

$current_user = get_admin_user();

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $selected_categories = $_POST['categories'] ?? [];
    $tags = $_POST['tags'] ?? '';
    
    $errors = [];
    
    // Validate required fields
    if (!$title) {
        $errors[] = "Title is required.";
    }
    
    if (!$content) {
        $errors[] = "Content is required.";
    }
    
    // Handle thumbnail upload
    $thumbnail = null;
    // If user selected existing media, use that
    $thumbnail_select = $_POST['thumbnail_select'] ?? '';
    if ($thumbnail_select) {
        $thumbnail = $thumbnail_select;
    } elseif (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = UPLOADS_DIR . '/contents/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
        $target_file = $upload_dir . $filename;
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['thumbnail']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed.";
        }
        
        // Check file size (max 5MB)
        if ($_FILES['thumbnail']['size'] > 5 * 1024 * 1024) {
            $errors[] = "File size should be less than 5MB.";
        }
        
        // Upload file if no errors
        if (empty($errors) && move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file)) {
            $thumbnail = 'uploads/contents/' . $filename;
            // Save uploaded thumbnail into media library
            $mime_type = $_FILES['thumbnail']['type'];
            $stmt = $pdo->prepare("INSERT INTO media (file_path, mime_type, uploaded_by) VALUES (?, ?, ?)");
            $stmt->execute([$thumbnail, $mime_type, $current_user['id']]);
        } else {
            $errors[] = "Failed to upload the thumbnail.";
        }
    }
    
    // If no errors, insert the content
    if (empty($errors)) {
        $slug = generate_slug($title);
        
        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM content WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . uniqid();
        }
        
        $pdo->beginTransaction();
        
        try {
            // Insert content
            $stmt = $pdo->prepare("INSERT INTO content (title, slug, content, excerpt, thumbnail, status, author_id) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $excerpt, $thumbnail, $status, $current_user['id']]);
            
            $content_id = $pdo->lastInsertId();
            
            // Insert categories
            if (!empty($selected_categories)) {
                $category_values = [];
                $category_placeholders = [];
                
                foreach ($selected_categories as $category_id) {
                    $category_values[] = $content_id;
                    $category_values[] = $category_id;
                    $category_placeholders[] = "(?, ?)";
                }
                
                $stmt = $pdo->prepare("INSERT INTO content_categories (content_id, category_id) 
                                      VALUES " . implode(', ', $category_placeholders));
                $stmt->execute($category_values);
            }
            
            // Insert tags
            if (!empty($tags)) {
                $tag_names = array_map('trim', explode(',', $tags));
                
                foreach ($tag_names as $tag_name) {
                    if (empty($tag_name)) continue;
                    
                    // Check if tag exists
                    $tag_slug = generate_slug($tag_name);
                    $stmt = $pdo->prepare("SELECT id FROM tags WHERE slug = ?");
                    $stmt->execute([$tag_slug]);
                    $tag = $stmt->fetch();
                    
                    // Create tag if it doesn't exist
                    if (!$tag) {
                        $stmt = $pdo->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
                        $stmt->execute([$tag_name, $tag_slug]);
                        $tag_id = $pdo->lastInsertId();
                    } else {
                        $tag_id = $tag['id'];
                    }
                    
                    // Link tag to content
                    $stmt = $pdo->prepare("INSERT INTO content_tags (content_id, tag_id) VALUES (?, ?)");
                    $stmt->execute([$content_id, $tag_id]);
                }
            }
            
            $pdo->commit();
            $_SESSION['message'] = "Content created successfully.";
            redirect('/admin/content.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - TSPI CMS</title>
    <link rel="icon" type="image/png" href="../src/assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* Additional styles for content editing */
        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .tag-item {
            display: inline-flex;
            align-items: center;
            background-color: var(--light-blue);
            color: var(--primary-blue);
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .tag-remove {
            margin-left: 0.5rem;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .tag-remove:hover {
            color: #dc3545;
        }

        .thumbnail-preview-container {
            margin-top: 1rem;
        }

        #thumbnail-preview {
            max-width: 300px; 
            border-radius: 4px;
        }

        .checkbox-group-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        /* Disable resizing for excerpt textarea */
        #excerpt {
            resize: none;
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="dashboard-container">
                <div class="page-header">
                    <h1><i class="fas fa-newspaper"></i> Add Content</h1>
                    <a href="content.php" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Content</a>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="admin-form-container">
                    <form action="" method="post" enctype="multipart/form-data" class="admin-form">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? sanitize($_POST['title']) : ''; ?>" required>
                            <small>The title of your content as it will appear on the site.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="thumbnail_select">Thumbnail Image</label>
                            <div class="thumbnail-controls">
                                <button type="button" id="thumbnail-select-btn" class="btn btn-secondary">
                                    <i class="fas fa-image"></i> Choose a thumbnail
                                </button>
                                <input type="hidden" id="thumbnail_select" name="thumbnail_select" value="<?php echo htmlspecialchars($_POST['thumbnail_select'] ?? ''); ?>">
                            </div>
                            <div class="thumbnail-preview-container">
                                <img id="thumbnail-preview" src="<?php echo isset($_POST['thumbnail_select']) ? SITE_URL . '/' . htmlspecialchars($_POST['thumbnail_select']) : '../assets/placeholder-image.jpg'; ?>" alt="Thumbnail Preview">
                            </div>
                            <small>Select a featured image for your content. This will be displayed as a thumbnail.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="excerpt">Excerpt (optional)</label>
                            <textarea id="excerpt" name="excerpt" rows="3"><?php echo isset($_POST['excerpt']) ? sanitize($_POST['excerpt']) : ''; ?></textarea>
                            <small>A short summary of the content. If left empty, an excerpt will be generated from the content.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="content-content-editor">Content</label>
                            <div class="editor-toolbar">
                                <button type="button" class="toolbar-btn" data-command="bold" title="Bold"><i class="fas fa-bold"></i></button>
                                <button type="button" class="toolbar-btn" data-command="italic" title="Italic"><i class="fas fa-italic"></i></button>
                                <button type="button" class="toolbar-btn" data-command="underline" title="Underline"><i class="fas fa-underline"></i></button>
                                <button type="button" class="toolbar-btn" data-command="strikeThrough" title="Strike Through"><i class="fas fa-strikethrough"></i></button>
                                <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link"><i class="fas fa-link"></i></button>
                                <button type="button" class="toolbar-btn" data-command="unlink" title="Remove Link"><i class="fas fa-unlink"></i></button>
                                <button type="button" class="toolbar-btn" data-command="insertImage" title="Insert Image"><i class="fas fa-image"></i></button>
                                <button type="button" class="toolbar-btn" data-command="resizeImage" title="Resize Image"><i class="fas fa-expand-alt"></i></button>
                                <button type="button" class="toolbar-btn" data-command="insertVideo" title="Insert Video"><i class="fas fa-video"></i></button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="H1" title="Heading 1">H1</button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="H2" title="Heading 2">H2</button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="H3" title="Heading 3">H3</button>
                                <button type="button" class="toolbar-btn" data-command="formatBlock" data-value="P" title="Paragraph">P</button>
                                <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List"><i class="fas fa-list-ul"></i></button>
                                <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List"><i class="fas fa-list-ol"></i></button>
                                <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left"><i class="fas fa-align-left"></i></button>
                                <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center"><i class="fas fa-align-center"></i></button>
                                <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right"><i class="fas fa-align-right"></i></button>
                            </div>
                            <div class="editor-content" id="content-content-editor" contenteditable="true"><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></div>
                            <input type="hidden" id="content-content" name="content" value="<?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?>">
                            <small>Use the toolbar to format your content. You can add text, images, links, and more.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Categories</label>
                            <div class="checkbox-group-container">
                                <?php foreach ($categories as $category): ?>
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="category-<?php echo $category['id']; ?>" name="categories[]" value="<?php echo $category['id']; ?>" <?php echo isset($_POST['categories']) && in_array($category['id'], $_POST['categories']) ? 'checked' : ''; ?>>
                                        <label for="category-<?php echo $category['id']; ?>"><?php echo sanitize($category['name']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small>Select one or more categories for your content. Categories help organize your content.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="tag-input">Tags</label>
                            <input type="text" id="tag-input" placeholder="Add tags... (press Enter or comma after each tag)">
                            <div id="tag-container" class="tag-container"></div>
                            <input type="hidden" id="tags" name="tags" value="<?php echo isset($_POST['tags']) ? sanitize($_POST['tags']) : ''; ?>">
                            <small>Enter tags separated by commas or press Enter after each tag. Tags help users find related content.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <?php if (in_array($current_user['role'], ['admin', 'moderator'])): ?>
                                <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                <?php endif; ?>
                                <option value="archived" <?php echo (isset($_POST['status']) && $_POST['status'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
                            </select>
                            <small>
                                Draft: only visible to administrators. 
                                <?php if (in_array($current_user['role'], ['admin', 'moderator'])): ?>
                                Published: visible to everyone. 
                                <?php else: ?>
                                <span class="text-muted">Only Admins and Moderators can publish content.</span>
                                <?php endif; ?>
                                Archived: no longer visible on the site.
                            </small>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Content</button>
                            <a href="content.php" class="btn btn-light"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <?php include 'includes/media-modal.php'; ?>

    <script src="../assets/js/admin.js"></script>
    
    <script>
    // Fix for thumbnail preview update
    document.addEventListener('DOMContentLoaded', function() {
        // Completely replace the original function to ensure it works on first click
        window.onMediaThumbClick = function(img) {
            const modal = document.getElementById('media-modal');
            const thumbnailSelect = document.getElementById('thumbnail_select');
            const thumbnailPreview = document.getElementById('thumbnail-preview');
            
            if (window._selectThumbnailMode) {
                // Update both the hidden input value and the visible preview
                if (thumbnailSelect) {
                    thumbnailSelect.value = img.dataset.url;
                }
                
                if (thumbnailPreview) {
                    // Make sure we're using the full URL for the preview
                    thumbnailPreview.src = img.dataset.url.startsWith('http') 
                        ? img.dataset.url 
                        : '<?php echo SITE_URL; ?>/' + img.dataset.url;
                }
                
                window._selectThumbnailMode = false;
            } else {
                document.execCommand('insertImage', false, img.dataset.url);
            }
            
            modal.style.display = 'none';
        };
        
        // Re-bind all media thumbs with our new function
        window.bindMediaThumbs = function() {
            document.querySelectorAll('#media-modal .media-thumb').forEach(img => {
                // Remove any existing click handlers first
                img.onclick = null;
                // Add our new click handler
                img.onclick = function() {
                    window.onMediaThumbClick(img);
                };
            });
        };
        
        // Initialize with our new bindings when media modal opens
        const thumbnailSelectBtn = document.getElementById('thumbnail-select-btn');
        if (thumbnailSelectBtn) {
            thumbnailSelectBtn.addEventListener('click', function() {
                window._selectThumbnailMode = true;
                const modal = document.getElementById('media-modal');
                if (modal) {
                    modal.style.display = 'flex';
                    window.bindMediaThumbs();
                }
            });
        }
    });
    </script>
</body>
</html>
