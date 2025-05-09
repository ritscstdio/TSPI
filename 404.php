
<?php
$page_title = "Page Not Found";
$body_class = "error-page";
require_once 'includes/config.php';
include 'includes/header.php';
?>

<main>
    <div class="error-container">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you are looking for does not exist or has been moved.</p>
        <a href="<?php echo SITE_URL; ?>" class="btn">Return to Homepage</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
