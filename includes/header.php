<?php
// Ensure config is loaded
require_once __DIR__ . '/config.php';

// Function to resolve image paths correctly in both environments
function resolve_asset_path($path) {
    // If path is empty or null, return default image path
    if (empty($path)) {
        return SITE_URL . '/src/assets/default-thumbnail.jpg';
    }
    
    // If path already starts with http:// or https://, it's an external URL
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    
    // If path already starts with SITE_URL, return as is
    if (strpos($path, SITE_URL) === 0) {
        return $path;
    }
    
    // If path starts with /TSPI/, remove it and prepend SITE_URL
    if (strpos($path, '/TSPI/') === 0) {
        return SITE_URL . substr($path, 5); // Remove '/TSPI'
    }
    
    // Handle paths to media uploads
    if (strpos($path, 'uploads/media/') !== false) {
        // Extract the filename from the path
        $filename = basename($path);
        // Return the path to the file in the Docker container
        return SITE_URL . '/uploads/media/' . $filename;
    }
    
    // Fix for Railway - handle absolute paths without hostname
    if (substr($path, 0, 1) === '/') {
        return SITE_URL . $path;
    }
    
    // Otherwise, just prepend SITE_URL
    return SITE_URL . '/' . ltrim($path, '/');
}

// Function to resolve CSS and JS paths
function resolve_css_path($path) {
    // If path already has protocol, return as is
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    
    // Check if the path contains the domain name already
    $domain = parse_url(SITE_URL, PHP_URL_HOST);
    if ($domain && strpos($path, $domain) !== false) {
        // Extract just the path part
        $parsedPath = parse_url($path);
        $path = isset($parsedPath['path']) ? $parsedPath['path'] : '/';
    }
    
    // Ensure path starts with a slash
    $path = '/' . ltrim($path, '/');
    
    // Return full URL
    return SITE_URL . $path;
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
    <meta name="description" content="<?php echo $page_description ?? 'Tulay sa Pag-unlad, Inc. | Blog and content System'; ?>" />
    <meta name="author" content="TSPI" />

    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?>" />
    <meta property="og:description" content="<?php echo $page_description ?? 'Latest news, stories and updates from Tulay sa Pag-unlad, Inc.'; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="<?php echo $page_image ?? SITE_URL . '/assets/default-thumbnail.jpg'; ?>" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?php echo resolve_css_path('/src/assets/favicon.png'); ?>">
    <link rel="stylesheet" href="<?php echo resolve_css_path('/assets/css/styles.css'); ?>">
    <link rel="stylesheet" href="<?php echo resolve_css_path('/src/css/mainstyles.css'); ?>">
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
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            width: 100%;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            border-radius: 6px !important;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2) !important;
            border: 1px solid #ddd;
        }
        
        /* User dropdown styles */
        .user-dropdown {
            position: relative;
            margin-left: 10px;
        }
        
        .user-btn {
            display: flex;
            align-items: center;
            background-color: transparent;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 1rem;
            color: #333;
            transition: color 0.3s ease;
            border-radius: 4px;
        }
        
        .user-btn:hover {
            color: #0056b3;
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .user-btn i {
            margin-right: 5px;
        }
        
        .user-btn i:last-child {
            margin-right: 0;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .user-dropdown-content {
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            min-width: 160px;
            z-index: 1000;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
            border: 1px solid #ddd;
            display: none;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        
        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }
        
        .user-dropdown-content a {
            display: block;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid #eee;
        }
        
        .user-dropdown-content a:last-child {
            border-bottom: none;
        }
        
        .user-dropdown-content a:hover {
            background-color: #e9f5ff;
            padding-left: 18px;
        }
        
        /* Fix dropdown disappearing on hover */
        .user-dropdown-content:before {
            content: '';
            position: absolute;
            top: -10px;
            right: 0;
            width: 100%;
            height: 10px;
            display: block;
        }

        /* Basic Sub-dropdown styling for desktop */
        .dropdown-content .sub-dropdown-trigger {
            position: relative;
        }

        .dropdown-content .sub-dropdown-trigger .sub-dropdown-content {
            display: none;
            position: absolute;
            left: 100%; /* Position to the right of the parent */
            top: -1px;     /* Align with the top of the parent, adjusted for border */
            background-color: #fff; /* Match main dropdown */
            min-width: 200px; /* Adjusted width */
            box-shadow: 0 6px 16px rgba(0,0,0,0.2); /* Match main dropdown */
            border: 1px solid #ddd; /* Match main dropdown */
            border-radius: 6px; /* Match main dropdown */
            padding-top: 5px;
            padding-bottom: 5px;
            z-index: 1001; /* Ensure it's above parent */
        }

        .dropdown-content .sub-dropdown-trigger:hover > .sub-dropdown-content {
            display: block;
        }

        .dropdown-content .sub-dropdown-trigger > a {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dropdown-content .sub-dropdown-trigger > a i {
            margin-left: auto; /* Pushes icon to the right */
            font-size: 0.8em;
        }
        
        /* Mobile nav adjustments for nested details */
        .mobile-nav details summary {
            /* padding: 10px 15px; Re-evaluate this if issues*/
            display: block;
            cursor: pointer;
            /* font-weight: bold; Re-evaluate */
            /* background-color: #f9f9f9; Re-evaluate */
            /* border-bottom: 1px solid #eee; Re-evaluate */
        }
        .mobile-nav details.sub-dropdown-details summary {
            padding-left: 15px; /* Indent sub-summary trigger */
            font-weight: normal; /* Sub-summary trigger less bold */
        }

        .mobile-nav details a, 
        .mobile-nav details details a {
            /* padding: 10px 15px 10px 30px; */ /* Base padding for links under details */
            /* display: block; Already there from previous general rules */
            /* text-decoration: none; Already there */
            /* color: #333; Already there */
            /* border-bottom: 1px solid #eee; Already there */
        }

        .mobile-nav details.sub-dropdown-details a {
            padding-left: 30px; /* Further indent links under sub-summary */
        }

        /* Style for the link within a summary tag */
        .summary-link-mobile {
            text-decoration: none;
            color: inherit; /* Inherit color from summary */
            display: inline; /* Allow it to flow with summary text */
            padding: 0; /* Reset padding for the link itself */
            border-bottom: none; /* Remove border from link inside summary */
            background-color: transparent; /* Ensure no background */
        }
        .mobile-nav details.sub-dropdown-details summary .summary-link-mobile {
             font-weight: bold; /* Make the About Us link bold in mobile */
        }

        /* Mobile nav visibility and toggling */
        .mobile-nav {
            display: none;
            flex-direction: column;
            background-color: #fff;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        .mobile-nav.mobile-nav-active {
            display: flex;
        }

        @media (max-width: 992px) {
            .nav-links {
                display: none;
            }
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle {
                display: none;
            }
            .mobile-nav {
                display: none !important;
            }
        }

        /* End of mobile nav visibility */
    </style>
</head>

<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
    <header class="header">
        <div class="header-top">
            <div class="header-top-inner">
                <div class="contact-info">
                    <a href="#" class="contact-link">
                        <i class="fas fa-phone"></i> (02) 8-403-8627
                    </a>
                    <a href="#" class="contact-link">
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
                <a href="<?php echo SITE_URL; ?>/homepage.php">
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
                <a href="<?php echo SITE_URL; ?>/homepage.php">Home</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Who We Are <i class="fas fa-chevron-down"></i></button>
                    <div class="dropdown-content">
                        <div class="sub-dropdown-trigger">
                            <a href="<?php echo SITE_URL; ?>/AboutUs.php">About Us <i class="fas fa-chevron-right"></i></a>
                            <div class="sub-dropdown-content">
                                <a href="<?php echo SITE_URL; ?>/AboutUs.php#vision-mission">Mission & Vision</a>
                                <a href="<?php echo SITE_URL; ?>/AboutUs.php#our-leaders">Our Leaders</a>
                                <a href="<?php echo SITE_URL; ?>/AboutUs.php#our-branches">Our Branches</a>
                                <a href="<?php echo SITE_URL; ?>/AboutUs.php#about-tspi-mbai">Our Partner (MBAI)</a>
                            </div>
                        </div>
                        <div class="sub-dropdown-trigger">
                            <a href="<?php echo SITE_URL; ?>/awards.php">Awards & Recognitions <i class="fas fa-chevron-right"></i></a>
                            <div class="sub-dropdown-content">
                                <a href="<?php echo SITE_URL; ?>/awards.php?category=org_awards">Organization</a>
                                <a href="<?php echo SITE_URL; ?>/awards.php?category=cli_awards">Clients</a>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="<?php echo SITE_URL; ?>/offers.php">What We Offer</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Our Impact <i class="fas fa-chevron-down"></i></button>
                    <div class="dropdown-content">
                        <a href="<?php echo SITE_URL; ?>/stories.php">Client Stories</a>
                        <a href="<?php echo SITE_URL; ?>/reports.php">Annual Reports</a>
                        <div class="sub-dropdown-trigger">
                            <a href="<?php echo SITE_URL; ?>/sambayanihan.php">SAMBAYANIHAN <i class="fas fa-chevron-right"></i></a>
                            <div class="sub-dropdown-content">
                                <a href="<?php echo SITE_URL; ?>/sambayanihan.php?category=sambayanihan_client">Clients</a>
                                <a href="<?php echo SITE_URL; ?>/sambayanihan.php?category=sambayanihan_employees">Employees</a>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="<?php echo SITE_URL; ?>/news.php">News</a>
                <div class="dropdown">
                    <button class="dropdown-btn">Resources <i class="fas fa-chevron-down"></i></button>
                    <div class="dropdown-content">
                        <div class="sub-dropdown-trigger">
                            <a href="<?php echo SITE_URL; ?>/publications.php">Publications <i class="fas fa-chevron-right"></i></a>
                            <div class="sub-dropdown-content">
                                <a href="<?php echo SITE_URL; ?>/publications.php?category=ann_reports">Annual Reports</a>
                                <a href="<?php echo SITE_URL; ?>/publications.php?category=aud_financial">Audited Financial Statements</a>
                                <a href="<?php echo SITE_URL; ?>/publications.php?category=newsletter">Newsletter</a>
                            </div>
                        </div>
                        <div class="sub-dropdown-trigger">
                            <a href="<?php echo SITE_URL; ?>/governance.php">Corporate Governance <i class="fas fa-chevron-right"></i></a>
                            <div class="sub-dropdown-content">
                                <a href="<?php echo SITE_URL; ?>/governance.php?category=leg_documents">Foundational Legal Documents</a>
                                <a href="<?php echo SITE_URL; ?>/governance.php?category=reg_registrations">Regulatory Compliance</a>
                                <a href="<?php echo SITE_URL; ?>/governance.php?category=gov_framework">Governance Framework</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mobile-nav">
                <a href="<?php echo SITE_URL; ?>/homepage.php" class="home-link">Home</a>
                <details class="main-dropdown-details">
                    <summary>Who We Are</summary>
                    <details class="sub-dropdown-details">
                        <summary><a href="<?php echo SITE_URL; ?>/AboutUs.php" class="summary-link-mobile">About Us</a></summary>
                        <a href="<?php echo SITE_URL; ?>/AboutUs.php#vision-mission">Mission & Vision</a>
                        <a href="<?php echo SITE_URL; ?>/AboutUs.php#our-leaders">Our Leaders</a>
                        <a href="<?php echo SITE_URL; ?>/AboutUs.php#our-branches">Our Branches</a>
                        <a href="<?php echo SITE_URL; ?>/AboutUs.php#about-tspi-mbai">Our Partner (MBAI)</a>
                    </details>
                    <details class="sub-dropdown-details">
                        <summary>Awards & Recognitions</summary>
                        <a href="<?php echo SITE_URL; ?>/awards.php?category=org_awards">Organization</a>
                        <a href="<?php echo SITE_URL; ?>/awards.php?category=cli_awards">Clients</a>
                    </details>
                </details>
                <a href="<?php echo SITE_URL; ?>/offers.php" class="what-we-offer-link">What We Offer</a>
                <details>
                    <summary>Our Impact</summary>
                    <a href="<?php echo SITE_URL; ?>/stories.php">Client Stories</a>
                    <a href="<?php echo SITE_URL; ?>/reports.php">Annual Reports</a>
                    <details class="sub-dropdown-details">
                        <summary><a href="<?php echo SITE_URL; ?>/sambayanihan.php" class="summary-link-mobile">SAMBAYANIHAN</a></summary>
                        <a href="<?php echo SITE_URL; ?>/sambayanihan.php?category=sambayanihan_client">Clients</a>
                        <a href="<?php echo SITE_URL; ?>/sambayanihan.php?category=sambayanihan_employees">Employees</a>
                    </details>
                </details>
                <a href="<?php echo SITE_URL; ?>/news.php" class="news-link">News</a>
                <details>
                    <summary>Resources</summary>
                    <details class="sub-dropdown-details">
                        <summary><a href="<?php echo SITE_URL; ?>/publications.php" class="summary-link-mobile">Publications</a></summary>
                        <a href="<?php echo SITE_URL; ?>/publications.php?category=ann_reports">Annual Reports</a>
                        <a href="<?php echo SITE_URL; ?>/publications.php?category=aud_financial">Audited Financial Statements</a>
                        <a href="<?php echo SITE_URL; ?>/publications.php?category=newsletter">Newsletter</a>
                    </details>
                    <details class="sub-dropdown-details">
                        <summary><a href="<?php echo SITE_URL; ?>/governance.php" class="summary-link-mobile">Corporate Governance</a></summary>
                        <a href="<?php echo SITE_URL; ?>/governance.php?category=leg_documents">Foundational Legal Documents</a>
                        <a href="<?php echo SITE_URL; ?>/governance.php?category=reg_registrations">Regulatory Compliance</a>
                        <a href="<?php echo SITE_URL; ?>/governance.php?category=gov_framework">Governance Framework</a>
                    </details>
                </details>
            </div>
            <div class="search-bar" style="position: relative;">
                <form id="searchForm">
                    <input type="text" id="liveSearchInput" placeholder="Search..." autocomplete="off">
                    <button type="button" id="searchButton"><i class="fas fa-search"></i></button>
                </form>
                <div id="searchResults" class="search-results" style="display: none;">
                </div>
            </div>
            <div class="user-dropdown">
                <button class="user-btn">
                    <i class="fas fa-user"></i>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown-content">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo SITE_URL; ?>/user/profile.php">My Profile</a>
                        <a href="<?php echo SITE_URL; ?>/user/logout.php">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/user/login.php">Login</a>
                        <a href="<?php echo SITE_URL; ?>/user/signup.php">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live search functionality
        const searchInput = document.getElementById('liveSearchInput');
        const searchButton = document.getElementById('searchButton');
        const searchResults = document.getElementById('searchResults');
        
        let searchTimeout = null;
        
        // Function to perform search
        function performSearch() {
            const query = searchInput.value.trim();
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            
            // Show searching indicator
            searchResults.style.display = 'block';
            searchResults.innerHTML = '<p class="searching">Searching...</p>';
            
            // Make AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '<?php echo SITE_URL; ?>/includes/search_content.php?q=' + encodeURIComponent(query), true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.status === 'success') {
                            if (response.count > 0) {
                                let html = '';
                                
                                response.results.forEach(function(content) {
                                    const date = new Date(content.published_at);
                                    const formattedDate = date.toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric'
                                    });
                                    
                                    html += `<a href="<?php echo SITE_URL; ?>/content.php?slug=${content.slug}" class="search-result-item">
                                        <span class="result-title">${content.title}</span>
                                        <span class="result-meta">${content.author} | ${formattedDate}</span>
                                    </a>`;
                                });
                                
                                searchResults.innerHTML = html;
                            } else {
                                searchResults.innerHTML = '<p class="no-results">No contents found</p>';
                            }
                        } else {
                            searchResults.innerHTML = '<p class="no-results">Error: ' + response.message + '</p>';
                        }
                    } catch (e) {
                        searchResults.innerHTML = '<p class="no-results">Error processing results</p>';
                    }
                } else {
                    searchResults.innerHTML = '<p class="no-results">Error connecting to server</p>';
                }
            };
            
            xhr.onerror = function() {
                searchResults.innerHTML = '<p class="no-results">Network error</p>';
            };
            
            xhr.send();
        }
        
        // Set up event listeners
        searchInput.addEventListener('input', function() {
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            searchTimeout = setTimeout(performSearch, 300);
        });
        
        searchButton.addEventListener('click', performSearch);
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
        
        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchButton.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Mobile menu toggle
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileNav = document.querySelector('.mobile-nav');
        const hamburgerIcon = document.querySelector('.hamburger-icon'); // For X animation

        if (mobileMenuToggle && mobileNav && hamburgerIcon) {
            mobileMenuToggle.addEventListener('click', function() {
                mobileNav.classList.toggle('mobile-nav-active');
                hamburgerIcon.classList.toggle('active'); // Toggle class for X animation
                // Optional: Toggle aria-expanded for accessibility
                const isExpanded = mobileNav.classList.contains('mobile-nav-active');
                mobileMenuToggle.setAttribute('aria-expanded', isExpanded);
                if (isExpanded) {
                    hamburgerIcon.setAttribute('aria-label', 'Close menu');
                    hamburgerIcon.setAttribute('aria-hidden', 'true'); // Hide spans if they are visually replaced by X
                } else {
                    hamburgerIcon.setAttribute('aria-label', 'Open menu');
                    hamburgerIcon.setAttribute('aria-hidden', 'false');
                }
            });
        }
    });
    </script>
</body>
</html>
