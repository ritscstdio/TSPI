<?php // Ensure config.php is included only once
if (!defined('DB_HOST')) {
    require_once 'config.php';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Tulay sa Pag-unlad, Inc. | Blog and Article System'; ?>" />
    <meta name="author" content="TSPI" />

    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>" />
    <meta property="og:description" content="<?php echo $page_description ?? 'Latest news, stories and updates from Tulay sa Pag-unlad, Inc.'; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="<?php echo $page_image ?? SITE_URL . '/assets/default-thumbnail.jpg'; ?>" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/src/assets/favicon.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/src/css/mainstyles.css">
</head>

<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
    <header class="header">
        <div class="header-top">
            <div class="header-top-inner">
                <div class="contact-info">
                    <a href="tel:+63284038627" class="contact-link">
                        <i class="fas fa-phone"></i> (02) 8-403-8627
                    </a>
                    <a href="mailto:partners@tspi.org" class="contact-link">
                        <i class="fas fa-envelope"></i> partners@tspi.org
                    </a>
                    <a href="https://maps.app.goo.gl/RdRgzFpL6f3BpZXd9" target="_blank" class="address-link">
                        <i class="fas fa-map-marker-alt"></i> 2363 Antipolo St. Guadaluep Nuevo Makati City, Philippines
                    </a>
                </div>
            </div>
        </div>
        <nav class="main-nav">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>">
                    <img src="<?php echo SITE_URL; ?>/src/assets/logo.jpg" alt="TSPI Logo">
                </a>
                <button class="mobile-menu-toggle">
                    <span class="hamburger-icon" aria-label="Open menu" aria-hidden="false">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
            <div class="nav-links">
                <a href="<?php echo SITE_URL; ?>">Home</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Who We Are <i class="fas fa-chevron-down"></i></button>
                    <div class="dropdown-content">
                        <a href="#about">About Us</a>
                        <a href="#mission-vision">Mission & Vision</a>
                        <a href="#board-of-trustees">Board of Trustees</a>
                        <a href="#management-team">Management Team</a>
                    </div>
                </div>
                <a href="#what-we-offer">What We Offer</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Our Impact <i class="fas fa-chevron-down"></i></button>
                    <div class="dropdown-content">
                        <a href="#impact/stories">Client Success Stories</a>
                        <a href="#impact/annual-reports">Annual Reports</a>
                    </div>
                </div>
                <a href="<?php echo SITE_URL; ?>/news.php">News</a>
            </div>
            <div class="mobile-nav">
                <a href="<?php echo SITE_URL; ?>" class="home-link">Home</a>
                <details>
                    <summary>Who We Are</summary>
                    <a href="#about">About Us</a>
                    <a href="#mission-vision">Mission & Vision</a>
                    <a href="#board-of-trustees">Board of Trustees</a>
                    <a href="#management-team">Management Team</a>
                </details>
                <a href="#what-we-offer" class="what-we-offer-link">What We Offer</a>
                <details>
                    <summary>Our Impact</summary>
                    <a href="#impact/stories">Client Success Stories</a>
                    <a href="#impact/annual-reports">Annual Reports</a>
                </details>
                <a href="<?php echo SITE_URL; ?>/news.php" class="news-link">News</a>
            </div>
            <div class="search-bar" style="position: relative;">
                <form action="<?php echo SITE_URL; ?>/search.php" method="get" onsubmit="return false;">
                    <input type="text" id="liveSearchInput" name="q" placeholder="Search..." autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <div id="searchResults" class="search-results" style="position: absolute; top: 100%; left: 0; right: 0; background: #fff; z-index: 1000; display: none; max-height: 300px; overflow-y: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                </div>
            </div>
        </nav>
    </header>
