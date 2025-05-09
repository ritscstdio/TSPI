
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
        // Set initial content
        editor.innerHTML = contentInput.value;
        
        // Update hidden input on editor change
        editor.addEventListener('input', function() {
            contentInput.value = this.innerHTML;
        });
        
        // Basic toolbar functionality
        const toolbarButtons = document.querySelectorAll('.toolbar-btn');
        
        toolbarButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const command = this.getAttribute('data-command');
                const value = this.getAttribute('data-value') || null;
                
                if (command === 'insertImage' || command === 'createLink') {
                    const url = prompt(command === 'insertImage' ? 'Enter image URL:' : 'Enter link URL:');
                    if (url) {
                        document.execCommand(command, false, url);
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
    }
});
