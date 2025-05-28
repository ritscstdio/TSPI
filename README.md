# TSPI Website Project

This repository contains the code for the TSPI (Tulay sa Pag-unlad, Inc.) website.

## Project Overview

TSPI is a microfinance NGO. This website aims to provide comprehensive information about the organization, its mission, services, and impact.

Key pages include:
*   **Homepage (`index.php` or `homepage.php`):** The main landing page.
*   **About Us (`AboutUs.php`):** Detailed information about TSPI, its vision, mission, core values, leaders, branches, and TSPI-MBAI.
*   **What We Offer (`offers.php`):** Information on livelihood loan programs, social loan programs, and life insurance (KAAGAPAY).
*   **News (`news.php`):** Displays news contents, likely filterable by categories.
*   **Awards (`awards.php`):** Showcases organizational and client awards, filterable by type.
*   **Membership Form (`user/membership-form.php`):** Comprehensive form for users to join TSPI, accessible after email verification or via "Join Us" button.
*   Other pages for stories, contact information, careers, etc.

## Getting Started

These instructions will guide you to get a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

*   A web server environment that supports PHP (e.g., XAMPP, WAMP, MAMP, or a dedicated server with Apache/Nginx and PHP).
*   PHP (version compatible with the project - typically 7.x or 8.x).
*   A web browser (e.g., Chrome, Firefox, Edge).
*   Access to a database if the project uses one (e.g., MySQL, often bundled with XAMPP/WAMP/MAMP). The project seems to interact with a WordPress backend, so WordPress installation and database setup would be part of that.

### Installation

1.  **Clone the repository (or download the files):**
    ```bash
    git clone <repository-url>
    ```
    Or, if you have the files directly, place them into your web server's document root (e.g., `htdocs` for XAMPP, `www` for WAMP).

2.  **Set up the Web Server:**
    *   Ensure your web server (Apache, Nginx) is configured to serve PHP files from the project directory.
    *   If using a local development server like XAMPP, place the project folder inside the `htdocs` directory.
    *   **Include Admin Global Stylesheet**: In each admin page's `<head>`, add `<link rel="stylesheet" href="css/global.css">` before the existing `admin.css` to apply the new design system.

3.  **Database Setup (If applicable, especially for WordPress integration):**
    *   If this project is a theme or plugin for WordPress, or directly uses a WordPress database, you will need an existing WordPress installation.
    *   Import any necessary database dumps (`.sql` files) if provided, or configure connection details in the relevant PHP files (e.g., `wp-config.php` for WordPress, or other custom database connection scripts).
    *   Ensure the database user has the correct privileges.

4.  **Configure Environment (If applicable):**
    *   Check for any configuration files (e.g., `config.php`, `.env`) that might need to be set up with specific paths, URLs, or API keys. For a WordPress theme, most configuration is handled through the WordPress admin interface and `wp-config.php`.

4.1. **Comments Feature Migration (Optional but recommended):**
    *   To enable the new voting and pinning functionalities in the comment system, run the following SQL on your database:
    ```sql
    ALTER TABLE comments
        DROP COLUMN IF EXISTS upvotes,
        DROP COLUMN IF EXISTS downvotes,
        ADD COLUMN vote_score INT NOT NULL DEFAULT 0,
        ADD COLUMN pinned TINYINT(1) NOT NULL DEFAULT 0; /* Pinned remains */
    
    DROP TABLE IF EXISTS comment_votes;
    CREATE TABLE comment_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        comment_id INT NOT NULL,
        user_id INT NOT NULL,
        vote TINYINT(1) NOT NULL COMMENT '1 for upvote, -1 for downvote',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_comment_vote (comment_id, user_id),
        FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ```

4.2. **content Voting Migration (Optional):**
    *   To support the new voting system on contents for trending calculations, run the following SQL on your database:
    ```sql
    ALTER TABLE contents
        DROP COLUMN IF EXISTS upvotes,
        DROP COLUMN IF EXISTS downvotes,
        ADD COLUMN vote_score INT NOT NULL DEFAULT 0;
    
    DROP TABLE IF EXISTS content_votes;
    CREATE TABLE content_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_id INT NOT NULL,
        user_id INT NOT NULL,
        vote TINYINT(1) NOT NULL COMMENT '1 for upvote, -1 for downvote',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_content_vote (content_id, user_id),
        FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ```

4.3. **Signature Pad Setup and Upload Directory:**
    *   Add the Signature Pad library by including in your HTML (e.g., before closing `</body>`):
    ```html
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    ```
    *   Ensure the upload directory for signatures exists with write permissions:
    ```bash
    mkdir -p uploads/signatures
    ```
    On Windows (PowerShell):
    ```powershell
    New-Item -ItemType Directory -Force uploads\signatures
    ```
    *   On the server side (e.g., in `membership-form.php`), decode the Base64-encoded signature (from a hidden input like `member_signature_data`) and save to `uploads/signatures`, storing the filepath (e.g., `uploads/signatures/member_<timestamp>.png`) in the database.

4.4. **Install Required Dependencies (TCPDF for PDF Generation):**
    *   This project uses Composer to manage PHP dependencies. If you don't have Composer installed, download it from [getcomposer.org](https://getcomposer.org/download/).
    *   For convenience, you can use the provided installation scripts:
        * On Windows: Double-click `install_dependencies.bat`
        * On Linux/Mac: Run `chmod +x install_dependencies.sh && ./install_dependencies.sh` in your terminal
    *   Alternatively, run the following command manually in the project root directory:
    ```bash
    composer install
    ```
    *   This will install the TCPDF library required for PDF generation of membership applications.
    *   If you encounter errors like "TCPDF ERROR: TCPDF requires the Imagick or GD extension", you need to enable the GD extension in PHP:
        1. Open your `php.ini` file (usually in your XAMPP/PHP directory)
        2. Find the line `;extension=gd` and uncomment it by removing the semicolon
        3. Restart your web server
        4. You can verify the installation by accessing `tcpdf_test.php` in your browser

4.5. **Administrator Authentication System:**
    *   The website now uses a separate authentication system for administrators.
    *   To set up an administrator account, run the following SQL:
    ```sql
    CREATE TABLE IF NOT EXISTS `administrators` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `password` varchar(255) NOT NULL,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `role` enum('admin','editor') NOT NULL DEFAULT 'admin',
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Create a default admin user (username: admin, password: Admin123!)
    INSERT INTO `administrators` (`username`, `password`, `name`, `email`, `role`) 
    VALUES ('admin', '$2y$10$5KP.IqP.8bi3z/mCRQpa0eNUQb3wQJG1H8xVTHsoLn0UlGm3Y0t8K', 'Admin User', 'admin@example.com', 'admin');
    ```
    *   This will create an administrator with username `admin` and password `Admin123!` - CHANGE THIS PASSWORD IMMEDIATELY after first login.
    *   Access the admin panel at `/admin/login.php` using these credentials.

4.6. **Admin Membership Application Management:**
    *   The admin panel includes functionality to view, approve, reject, and generate PDFs for membership applications.
    *   Access is restricted to users with admin privileges.
    *   Ensure the TCPDF library is installed via Composer for PDF generation functionality.
    *   Access the membership application management via Admin Panel → Membership Applications.

5.  **Access the website:**
    *   Open your web browser and navigate to the project's URL (e.g., `http://localhost/your-project-folder-name/` if using XAMPP/WAMP).

### Key File Locations (Based on provided information):

*   **Main PHP Files:** Root directory (e.g., `AboutUs.php`, `offers.php`).
*   **Includes (Header, Footer, etc.):** Likely in an `includes/` directory (e.g., `includes/header.php`).
*   **CSS Stylesheets:**
    *   Global styles might be in `src/css/mainstyles.css` or similar.
    *   Page-specific styles might be embedded in `<style>` tags within PHP files or in separate CSS files linked by those pages.
*   **JavaScript Files:**
    *   May be in `src/js/` or embedded within PHP files.
*   **Assets (Images, Fonts):** Likely in `src/assets/` (e.g., `src/assets/mainbranch/` for branch images, `src/assets/leaders/` for leader images).
*   **WordPress Theme Files (If this is a theme):** Typically organized according to WordPress theme development standards (e.g., `wp-content/themes/your-theme-name/`).

## Navigation and Key Features to Test

*   **Main Navigation:** Ensure all links in the header (desktop and mobile) work correctly.
*   **Homepage:**
    *   Test the "Join Us" button to ensure it links to the membership form.
    *   Verify the news carousel and scroll-to-top functionality.
*   **`AboutUs.php`:**
    *   Test the sticky side navigation.
    *   Verify that all sections load with correct content.
    *   Check the "Our Leaders" section: bio expansion, deep linking (e.g., try accessing `AboutUs.php#leader-id` if IDs are known).
    *   Check the "Our Branches" section: head office carousel, collapsible regions/provinces, copy-to-clipboard for addresses.
    *   Ensure `scroll-margin-top` (using `--navbar-scroll-offset`) correctly prevents content from being hidden by the fixed navbar when navigating via anchor links.
*   **`offers.php`:**
    *   Verify intro section layout (text and client story carousel).
    *   Check animations and hover effects on offer cards.
    *   Ensure all program sections display correctly with their respective cards.
*   **`awards.php`:**
    *   Test filtering using URL parameters (`?type=organization`, `?type=client`).
    *   Confirm the page title changes dynamically.
*   **Membership Form (`user/membership-form.php`):**
    *   Test accessing the form via the "Join Us" button on the homepage.
    *   Test accessing the form after email verification.
    *   Verify that the spouse section only appears when "Married" is selected.
    *   Test age auto-calculation from birthdates.
    *   Test CID number auto-population from personal info to signature section.
    *   Verify form validation works correctly.
    *   Test submission with valid data.
*   **User Verification (`user/verify.php`):**
    *   Verify that the "Complete Membership Form" button appears after successful verification.
    *   Test the link to ensure it properly directs to the membership form with the verified parameter.
*   **Admin Membership Application Management:**
    *   Test the application list view (`admin/applications.php`).
    *   Verify that the detailed view (`admin/view_application.php`) displays all application data correctly.
    *   Test the PDF generation functionality.
    *   Test the approval and rejection functionalities.
*   **Responsiveness:** Check all pages on different screen sizes (desktop, tablet, mobile).

## Developer Documentation

*   General project flow and development notes: `documentation/documentation.txt`
*   "Our Leaders" section (AboutUs.php): `documentation/about_us_leaders_section.txt`
*   "Our Branches" section (AboutUs.php): `documentation/about_us_branches_section.txt`

## Database Migration & Admin Update

1. Before running any migration scripts, check your existing table constraints using:
   ```sql
   SHOW CREATE TABLE content_categories;
   SHOW CREATE TABLE content_tags; 
   SHOW CREATE TABLE content_votes;
   SHOW CREATE TABLE comments;
   ```

2. Import the SQL migration scripts in the following order:
   - First run `documentation/rename_articles_to_content.sql` to rename core content tables
   - Then run `documentation/rename_comments_articleid_to_contentid.sql` to update the comments table
   
3. If you're having issues with the terminal commands, you can run these SQL statements manually in phpMyAdmin:
   ```sql
   ALTER TABLE `comments` CHANGE `article_id` `content_id` INT(11) NOT NULL;
   ALTER TABLE `comments` ADD CONSTRAINT `comments_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content`(`id`) ON DELETE CASCADE;
   ```

4. If you encounter foreign key constraint errors, modify the scripts to match your actual constraint names.

5. The admin UI has been restructured:
   - The "Articles" section is now "Content". Files have been renamed under `admin/`.
   - The "Pages" section has been removed.
   - To manage the Content (previously Articles), use `admin/content.php`, `admin/add-content.php`, and `admin/edit-content.php`.
   - Category management now groups categories by front-end navbar sections; slug editing and delete/add are disabled in admin.

## Database Setup for Approval Workflow

The membership application approval system requires additional database setup:

1. Run the following SQL files in your database:
   - `admin/sql/alter_members_information.sql` - Adds approval columns to the members_information table
   - `admin/sql/create_branches_table.sql` - Creates the branches table and adds sample branches

These SQL scripts will:
1. Add Insurance Officer (IO) and Loan Officer (LO) approval fields to the membership table
2. Create a branches table for branch assignment during the approval process
3. Set up a trigger to automatically update the application status when both IO and LO approve

## Administrator Roles

The system includes the following administrator roles:

- **Admin**: Full access to all features
- **Editor**: Can manage content and media
- **Comment Moderator**: Can manage and moderate comments
- **Insurance Officer**: Can approve membership applications as IO
- **Loan Officer**: Can approve membership applications as LO

To add users with these roles, use the Admin panel's "Add User" functionality.

## Approval Workflow

The membership application approval process follows these steps:

1. User submits a membership application through the membership form
2. Application is initially set to 'pending' status with both IO and LO approval set to 'pending'
3. Insurance Officer reviews the application and can approve or reject
4. Loan Officer reviews the application and can approve or reject
5. Only when both IO and LO have approved the application will the overall status change to 'approved'

This README will be updated as the project evolves.
