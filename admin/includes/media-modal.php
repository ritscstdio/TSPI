<?php
// Modal for selecting or uploading images in the media library
?>
<div id="media-modal" class="media-modal" data-upload-url="<?php echo SITE_URL; ?>/admin/media-upload.php" style="display:none;">
    <div class="media-modal-content">
        <span class="media-modal-close">&times;</span>
        <h2>Media Library</h2>
        <input type="file" id="media-upload-input" name="media_file" accept="image/*" style="margin:0.5rem 0;">
        <div class="media-items">
            <?php
            $media_images = $pdo->query("SELECT * FROM media WHERE mime_type LIKE 'image/%' ORDER BY uploaded_at DESC")->fetchAll();
            foreach ($media_images as $img):
                $url = SITE_URL . '/' . $img['file_path'];
            ?>
                <img src="<?php echo $url; ?>" data-url="<?php echo $url; ?>" class="media-thumb" style="width:100px; height:auto; margin:0.5rem; cursor:pointer; border:2px solid transparent;" />
            <?php endforeach; ?>
        </div>
    </div>
</div>
<style>
.media-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.media-modal-content {
    background: #fff;
    padding: 1rem;
    max-width: 80%;
    max-height: 80%;
    overflow: auto;
    position: relative;
    border-radius: 4px;
}
.media-modal-close {
    position: absolute;
    top: 0.5rem;
    right: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
}
.media-thumb:hover {
    border-color: #007bff;
}
</style> 