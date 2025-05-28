<script>
    // Improved media workflow
    document.addEventListener('DOMContentLoaded', function() {
        // Function to load media library dynamically
        window.loadMediaLibrary = function() {
            const mediaItems = document.querySelector('.media-items');
            if (!mediaItems) return;
            
            // Add loading indicator
            mediaItems.innerHTML = '<div style="text-align:center;padding:2rem;">Loading media...</div>';
            
            // Fetch media items
            fetch('<?php echo SITE_URL; ?>/admin/load-media.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mediaItems.innerHTML = '';
                        data.media.forEach(item => {
                            const img = document.createElement('img');
                            img.src = item.url;
                            img.dataset.url = item.url;
                            img.classList.add('media-thumb');
                            img.style.cssText = 'width:100px; height:auto; margin:0.5rem; cursor:pointer; border:2px solid transparent;';
                            mediaItems.appendChild(img);
                        });
                        // Bind click handlers to the newly loaded images
                        window.bindMediaThumbs();
                    } else {
                        mediaItems.innerHTML = '<div style="text-align:center;padding:2rem;">Error loading media</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading media:', error);
                    mediaItems.innerHTML = '<div style="text-align:center;padding:2rem;">Error loading media</div>';
                });
        };
        
        // Override the upload complete handler to automatically select the new image
        window.onUploadComplete = function(response) {
            if (response.success) {
                // Store the newly uploaded image URL
                const imgUrl = response.file_url;
                
                // If we're in thumbnail selection mode, auto-select the new image
                if (window._selectThumbnailMode) {
                    const thumbnailSelect = document.getElementById('thumbnail_select');
                    const thumbnailPreview = document.getElementById('thumbnail-preview');
                    
                    if (thumbnailSelect) {
                        thumbnailSelect.value = imgUrl;
                    }
                    
                    if (thumbnailPreview) {
                        thumbnailPreview.src = imgUrl.startsWith('http') 
                            ? imgUrl 
                            : '<?php echo SITE_URL; ?>/' + imgUrl;
                    }
                    
                    // Close the modal after selecting
                    const modal = document.getElementById('media-modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    
                    window._selectThumbnailMode = false;
                    
                    // Show success message
                    alert('Image uploaded and selected as thumbnail successfully!');
                } else {
                    // If not in thumbnail mode, insert the image into the editor
                    document.execCommand('insertImage', false, imgUrl);
                    
                    // Close the modal
                    const modal = document.getElementById('media-modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                }
                
                // Refresh the media library
                loadMediaLibrary();
            } else {
                alert('Upload failed: ' + (response.message || 'Unknown error'));
            }
        };
        
        // Override the media upload input change handler
        const mediaUploadInput = document.getElementById('media-upload-input');
        if (mediaUploadInput) {
            mediaUploadInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                
                const formData = new FormData();
                formData.append('media_file', file);
                
                const uploadUrl = document.getElementById('media-modal').dataset.uploadUrl;
                
                fetch(uploadUrl, { 
                    method: 'POST', 
                    body: formData 
                })
                .then(res => res.json())
                .then(data => {
                    // Call our custom upload complete handler
                    window.onUploadComplete({
                        success: true,
                        file_url: data.url
                    });
                })
                .catch(err => { 
                    console.error('Upload error:', err);
                    window.onUploadComplete({
                        success: false,
                        message: 'Upload failed: ' + err.message
                    });
                });
            });
        }
        
        // Function to handle media item selection
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
                    // Ensure media library is loaded with latest items
                    loadMediaLibrary();
                }
            });
        }
        
        // Bind the media modal close button
        const mediaModalClose = document.querySelector('.media-modal-close');
        if (mediaModalClose) {
            mediaModalClose.addEventListener('click', function() {
                const modal = document.getElementById('media-modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        }
    });
</script> 