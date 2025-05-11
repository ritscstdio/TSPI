document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const body = document.body;
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            body.classList.toggle('body-sidebar-collapsed');
        });
    }
    
    // Mobile menu toggle (for responsive admin)
    const menuToggle = document.getElementById('menuToggle');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Delete confirmation for buttons with data-confirm attribute
    const deleteButtons = document.querySelectorAll('.delete-btn[data-confirm]');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // File input previews
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(function(input) {
        const previewId = input.getAttribute('data-preview');
        const previewElement = document.getElementById(previewId);
        
        if (previewElement) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewElement.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
    
    // Basic rich text editor for article content
    const editor = document.getElementById('article-content-editor');
    const contentInput = document.getElementById('article-content');
    
    if (editor && contentInput) {
        // Track last clicked image for resizing
        let lastClickedImage = null;
        editor.addEventListener('click', function(e) {
            if (e.target.tagName === 'IMG') {
                lastClickedImage = e.target;
            }
        });
        
        // Set initial content
        editor.innerHTML = contentInput.value;
        
        // Update hidden input on editor change
        editor.addEventListener('input', function() {
            contentInput.value = this.innerHTML;
        });
        
        // Function to handle media thumbnail click (for content or thumbnail selection)
        window.onMediaThumbClick = img => {
            const modal = document.getElementById('media-modal');
            if (window._selectThumbnailMode) {
                const select = document.getElementById('thumbnail_select');
                const preview = document.getElementById('thumbnail-preview');
                if (select) select.value = img.dataset.url;
                if (preview) preview.src = img.dataset.url;
                window._selectThumbnailMode = false;
            } else {
                document.execCommand('insertImage', false, img.dataset.url);
            }
            modal.style.display = 'none';
        };

        // Bind click on all media thumbs
        window.bindMediaThumbs = () => {
            document.querySelectorAll('#media-modal .media-thumb').forEach(img => {
                img.onclick = () => window.onMediaThumbClick(img);
            });
        };
        
        // Basic toolbar functionality
        const toolbarButtons = document.querySelectorAll('.toolbar-btn');
        
        toolbarButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const command = this.getAttribute('data-command');
                const value = this.getAttribute('data-value') || null;
                
                if (command === 'createLink') {
                    const url = prompt('Enter link URL:');
                    if (url) {
                        document.execCommand('createLink', false, url);
                    }
                } else if (command === 'insertImage') {
                    const modal = document.getElementById('media-modal');
                    if (modal) {
                        modal.style.display = 'flex';
                        window.bindMediaThumbs();
                        const closeBtn = modal.querySelector('.media-modal-close');
                        if (closeBtn) closeBtn.onclick = () => { modal.style.display = 'none'; };
                    } else {
                        const url = prompt('Enter image URL:');
                        if (url) document.execCommand('insertImage', false, url);
                    }
                } else if (command === 'resizeImage') {
                    // Resize image using tracked click
                    const img = lastClickedImage;
                    if (img) {
                        const currentWidth = img.style.width.replace('px','') || img.width || '';
                        const newWidth = prompt('Enter new width in pixels (without unit):', currentWidth);
                        if (newWidth) img.style.width = parseInt(newWidth) + 'px';
                    } else {
                        alert('Click on an inserted image first, then click Resize.');
                    }
                } else if (command === 'insertVideo') {
                    // Insert video embed
                    const videoUrl = prompt('Enter video URL:');
                    if (videoUrl) {
                        let embedUrl = videoUrl;
                        if (videoUrl.includes('youtube.com/watch')) {
                            const videoId = videoUrl.split('v=')[1].split('&')[0];
                            embedUrl = 'https://www.youtube.com/embed/' + videoId;
                        } else if (videoUrl.includes('youtu.be/')) {
                            const videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
                            embedUrl = 'https://www.youtube.com/embed/' + videoId;
                        }
                        const iframeHtml = '<iframe src="' + embedUrl + '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                        const wrappedHtml = '<div class="video-embed-container">' + iframeHtml + '</div>';
                        document.execCommand('insertHTML', false, wrappedHtml);
                    }
                } else {
                    document.execCommand(command, false, value);
                }
                
                editor.focus();
            });
        });
    }
    
    // Tags input enhancement
    const tagInput = document.getElementById('tag-input');
    const tagContainer = document.getElementById('tag-container');
    const tagsHiddenInput = document.getElementById('tags');
    
    if (tagInput && tagContainer && tagsHiddenInput) {
        const addTag = function(text) {
            const tag = document.createElement('span');
            tag.classList.add('tag-item');
            tag.textContent = text;
            
            const removeBtn = document.createElement('span');
            removeBtn.classList.add('tag-remove');
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', function() {
                tagContainer.removeChild(tag);
                updateTags();
            });
            
            tag.appendChild(removeBtn);
            tagContainer.appendChild(tag);
            updateTags();
        };
        
        const updateTags = function() {
            const tags = [];
            document.querySelectorAll('.tag-item').forEach(function(tag) {
                tags.push(tag.textContent.replace('×', '').trim());
            });
            tagsHiddenInput.value = tags.join(',');
        };
        
        // Add tag when pressing Enter
        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                
                const text = this.value.trim();
                
                if (text) {
                    addTag(text);
                    this.value = '';
                }
            }
        });
        
        // Add tag when clicking outside
        tagInput.addEventListener('blur', function() {
            const text = this.value.trim();
            
            if (text) {
                addTag(text);
                this.value = '';
            }
        });
        
        // Add initial tags if any
        if (tagsHiddenInput.value) {
            const initialTags = tagsHiddenInput.value.split(',');
            initialTags.forEach(function(tag) {
                if (tag.trim()) {
                    addTag(tag.trim());
                }
            });
        }

        // Media upload in modal
        const mediaModal = document.getElementById('media-modal');
        const mediaUploadInput = document.getElementById('media-upload-input');
        if (mediaModal && mediaUploadInput) {
            const uploadUrl = mediaModal.dataset.uploadUrl;
            mediaUploadInput.addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('media_file', file);
                fetch(uploadUrl, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.url) {
                            const img = document.createElement('img');
                            img.src = data.url;
                            img.dataset.url = data.url;
                            img.classList.add('media-thumb');
                            img.style.cssText = 'width:100px; height:auto; margin:0.5rem; cursor:pointer; border:2px solid transparent;';
                            img.addEventListener('click', function() {
                                document.execCommand('insertImage', false, this.dataset.url);
                                mediaModal.style.display = 'none';
                            });
                            mediaModal.querySelector('.media-items').prepend(img);
                            mediaUploadInput.value = '';
                        } else {
                            alert(data.error || 'Upload failed');
                        }
                    })
                    .catch(err => { alert('Upload error'); console.error(err); });
            });
        }

        // Thumbnail select button
        const thumbBtn = document.getElementById('thumbnail-select-btn');
        if (thumbBtn) {
            thumbBtn.addEventListener('click', function() {
                window._selectThumbnailMode = true;
                const modal = document.getElementById('media-modal');
                if (modal) {
                    modal.style.display = 'flex';
                    window.bindMediaThumbs();
                }
            });
        }
    }

    // Show existing thumbnail preview on page load (for edit-article page)
    const thumbnailSelectInput = document.getElementById('thumbnail_select');
    const thumbnailPreview = document.getElementById('thumbnail-preview');
    if (thumbnailSelectInput && thumbnailSelectInput.value && thumbnailPreview) {
        thumbnailPreview.src = thumbnailSelectInput.value;
    }

    // Global media modal close binding
    const globalModal = document.getElementById('media-modal');
    if (globalModal) {
        const modalClose = globalModal.querySelector('.media-modal-close');
        if (modalClose) {
            modalClose.addEventListener('click', function() {
                globalModal.style.display = 'none';
            });
        }
    }
});
