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

    // Live search for tables
    function setupLiveSearch(inputId, tableBodyId, searchableCellIndex) {
        const searchInput = document.getElementById(inputId);
        const tableBody = document.getElementById(tableBodyId);

        if (searchInput && tableBody) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const rows = tableBody.getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const cell = row.getElementsByTagName('td')[searchableCellIndex];
                    if (cell) {
                        const cellText = cell.textContent || cell.innerText;
                        if (cellText.toLowerCase().includes(searchTerm)) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    } else {
                         // If row doesn't have the target cell (e.g. colspan row), show it or decide based on context
                         // For now, assume typical rows
                    }
                }
            });
        }
    }

    // Initialize live searches
    setupLiveSearch('liveSearchArticles', 'articlesTableBody', 0); // Search by Title (index 0)
    setupLiveSearch('liveSearchMedia', 'mediaTableBody', 2);    // Search by Filename (index 2)
    setupLiveSearch('liveSearchPages', 'pagesTableBody', 0);     // Search by Title (index 0)

    setupCommentPreview(); // Initialize comment preview

    // Make stat boxes clickable
    document.querySelectorAll('.stat-box[data-link]').forEach(box => {
        box.addEventListener('click', function() {
            const link = this.dataset.link;
            if (link) {
                window.location.href = link;
            }
        });
    });

    setupAuthorInfoPreview(); // Initialize author info preview
    setupUserInfoPreview(); // Initialize user info preview for article authors
    setupEditProfileModal(); // Initialize edit profile modal
});

function setupCommentPreview() {
    const modal = document.getElementById('commentPreviewModal');
    if (!modal) return; 

    const modalText = document.getElementById('commentModalText');
    const closeButton = modal.querySelector('.close-button');

    document.querySelectorAll('.comment-text-preview').forEach(element => {
        element.addEventListener('click', function() {
            const fullComment = this.dataset.fullComment;
            modalText.innerHTML = fullComment; 
            modal.style.display = 'flex'; 
        });
    });

    if (closeButton) {
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none'; 
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none'; 
        }
    });
}

function setupAuthorInfoPreview() {
    const modal = document.getElementById('authorInfoModal');
    if (!modal) return;

    const authorNameEl = document.getElementById('authorModalName');
    const authorEmailEl = document.getElementById('authorModalEmail');
    const authorWebsiteEl = document.getElementById('authorModalWebsite');
    const closeButton = modal.querySelector('.close-button');

    document.querySelectorAll('.comment-author-name').forEach(element => {
        element.addEventListener('click', function(e) {
            // Prevent click from propagating to parent elements if any other listeners are there
            // e.stopPropagation(); // Optional: if needed
            
            authorNameEl.textContent = this.dataset.name || 'N/A';
            authorEmailEl.textContent = this.dataset.email || 'N/A';
            
            const website = this.dataset.website;
            if (website && website.trim() !== '' && website.toLowerCase() !== 'n/a') {
                // Ensure website is a full URL
                let fullWebsiteUrl = website;
                if (!website.startsWith('http://') && !website.startsWith('https://')) {
                    fullWebsiteUrl = 'http://' + website;
                }
                authorWebsiteEl.innerHTML = `<a href="${fullWebsiteUrl}" target="_blank" rel="noopener noreferrer">${website}</a>`;
            } else {
                authorWebsiteEl.textContent = 'N/A';
            }
            
            modal.style.display = 'flex';
        });
    });

    if (closeButton) {
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

function setupUserInfoPreview() {
    const modal = document.getElementById('userInfoModal');
    if (!modal) return;

    const userNameEl = document.getElementById('userModalName');
    const userEmailEl = document.getElementById('userModalEmail');
    const userRoleEl = document.getElementById('userModalRole');
    const closeButton = modal.querySelector('.close-button');

    document.querySelectorAll('.article-author-name').forEach(element => {
        element.addEventListener('click', function() {
            userNameEl.textContent = this.dataset.name || 'N/A';
            userEmailEl.textContent = this.dataset.email || 'N/A';
            userRoleEl.textContent = this.dataset.role || 'N/A';
            modal.style.display = 'flex';
        });
    });

    if (closeButton) {
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

function setupEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    if (!modal) return;

    const editProfileLink = document.getElementById('editProfileLink');
    const editProfileForm = document.getElementById('editProfileForm');
    const profileNameInput = document.getElementById('profile_name');
    const profileEmailInput = document.getElementById('profile_email');
    const currentPasswordInput = document.getElementById('profile_current_password');
    const newPasswordInput = document.getElementById('profile_new_password');
    const confirmPasswordInput = document.getElementById('profile_confirm_password');
    const messageDiv = document.getElementById('editProfileMessage');
    const closeButton = modal.querySelector('.close-button');

    if (editProfileLink) {
        editProfileLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Pre-fill form
            profileNameInput.value = this.dataset.userName || '';
            profileEmailInput.value = this.dataset.userEmail || '';
            // Clear password fields and messages
            currentPasswordInput.value = '';
            newPasswordInput.value = '';
            confirmPasswordInput.value = '';
            messageDiv.style.display = 'none';
            messageDiv.textContent = '';
            messageDiv.className = 'message'; // Reset class
            modal.style.display = 'flex';
        });
    }

    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            messageDiv.style.display = 'none';

            const formData = new FormData(this);
            fetch('actions/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.textContent = data.message;
                messageDiv.className = 'message'; // Reset class first
                if (data.success) {
                    messageDiv.classList.add('success'); // Assuming a .success class for green messages
                    // Optionally update name in header if changed
                    if (data.new_name) {
                        const headerUserName = document.querySelector('.header-user-btn span');
                        if (headerUserName) headerUserName.textContent = data.new_name;
                        // Update data attribute on link as well
                        if(editProfileLink) editProfileLink.dataset.userName = data.new_name;
                    }
                     // Clear password fields on success if they were filled
                    currentPasswordInput.value = '';
                    newPasswordInput.value = '';
                    confirmPasswordInput.value = '';
                    // setTimeout(() => { modal.style.display = 'none'; }, 2000); // Optionally close modal
                } else {
                    messageDiv.classList.add('error');
                }
                messageDiv.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.className = 'message error';
                messageDiv.style.display = 'block';
            });
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}
