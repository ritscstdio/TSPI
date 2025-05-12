<div id="commentPreviewModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <p id="commentModalText"></p>
    </div>
</div>

<div id="authorInfoModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h3>Author Information</h3>
        <p><strong>Name:</strong> <span id="authorModalName"></span></p>
        <p><strong>Email:</strong> <span id="authorModalEmail"></span></p>
        <p><strong>Website:</strong> <span id="authorModalWebsite"></span></p>
    </div>
</div>

<div id="userInfoModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h3>User Information</h3>
        <p><strong>Name:</strong> <span id="userModalName"></span></p>
        <p><strong>Email:</strong> <span id="userModalEmail"></span></p>
        <p><strong>Role:</strong> <span id="userModalRole"></span></p>
    </div>
</div>

<div id="editProfileModal" class="modal" style="display:none;">
    <div class="modal-content admin-form-container" style="max-width: 500px;">
        <span class="close-button">&times;</span>
        <h3>Edit Your Profile</h3>
        <form id="editProfileForm" class="admin-form" style="padding: 0;">
            <div id="editProfileMessage" class="message" style="display:none; margin-top: 1rem;"></div>
            <div class="form-group">
                <label for="profile_name">Name</label>
                <input type="text" id="profile_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="profile_email">Email</label>
                <input type="email" id="profile_email" name="email" required>
            </div>
            <hr style="margin: 1.5rem 0;">
            <p style="font-size: 0.9em; color: #666; margin-bottom: 0.5rem;">To change your email or password, please enter your current password. To change only your name, leave password fields blank.</p>
            <div class="form-group">
                <label for="profile_current_password">Current Password</label>
                <input type="password" id="profile_current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="profile_new_password">New Password (leave blank to keep current)</label>
                <input type="password" id="profile_new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="profile_confirm_password">Confirm New Password</label>
                <input type="password" id="profile_confirm_password" name="confirm_new_password">
            </div>
            <div class="form-group btn-group" style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/admin.js"></script> 