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
    <!-- Add cache control to prevent browser caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
    <style>
        /* Search results styling */
        .search-results a.search-result-item {
            display: block;
            padding: 15px;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            position: relative;
            background-color: #fff;
        }
        .search-results a.search-result-item:last-child {
            border-bottom: none;
        }
        .search-results a.search-result-item:hover {
            background-color: #e9f5ff;
            padding-left: 18px;
            box-shadow: inset 4px 0 0 0 #0056b3;
        }
        .search-results .result-title {
            font-weight: 700 !important;
            margin-bottom: 6px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #000;
            font-size: 1.05rem;
        }
        .search-results .result-meta {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 4px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .search-results p.no-results,
        .search-results p.searching {
            padding: 15px;
            color: #666;
            text-align: center;
            margin: 0;
            font-style: italic;
        }
        .search-results {
            border-radius: 6px !important;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2) !important;
            overflow: hidden;
            border: 1px solid #ddd;
        }
    </style>
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
                <form id="searchForm">
                    <input type="text" id="liveSearchInput" placeholder="Search..." autocomplete="off" disabled>
                    <button type="button"><i class="fas fa-search"></i></button>
                </form>
                <div id="searchResults" class="search-results" style="display: none;">
                </div>
            </div>
        </nav>
    </header>
</body>
</html>
