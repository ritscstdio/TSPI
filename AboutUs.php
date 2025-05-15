<?php
$page_title = "About Us";
$body_class = "about-us-page"; // For any global styles if needed, or specific targeting
require_once 'includes/config.php';

// Leader image mapping
$leader_images_map = [
    "David T. Bussau" => "bussau-274x300.jpg",
    "Atty. Lamberto L. Meer" => "Meer-300x300.jpg",
    "Dr. Abraham F. Pascual" => "9-Trustee-Pascual-2-768x768.jpg",
    "Rene E. Cristobal" => "Pres.-R.-Cristobal-square-768x768.jpg",
    "Ricardo G. Lazatin" => "5-Trustee-R-Lazatin-2.jpg",
    "Atty. Cornelio C. Gison" => "6-Trustee-Gison-2.jpg",
    "Juanita D. Amatong" => "nitz-square.jpg",
    "Jose D. Fider" => "Fider.png",
    "Alberto M. Malvar" => "Trustee-A.-Malvar-2.jpg",
    "Luz A. Planas" => "10-Trustee-Planas-2.jpg",
    "Florencia G. Tarriela" => "6-Trustee-F-Tarriela-2.jpg",
    "Terence R. Winters" => "11-Trustee-T-Winters-2.jpg",
    "Richard Dagelet, Jr." => "sir-dagelet.jpg",
    "Carlos Rheal B. Cervantes" => "carlos-cervantes.jpg",
    "Raymond Daniel H. Cruz Jr." => "sir-raymond-cruz.jpg",
    "Anna Isabel C. Sobrepeña" => "ana-sobrepena.jpg",
    "Alice Z. Cordero" => "MS_ALICE_1-removebg-preview.png",
    "Atty. Leonarda D. Banasen" => "Atty.-Leah-300x292.png",
    "Ms. Lorna M. Asuncion" => "MS_LORNA_2-removebg-preview-300x292.png",
    "Mr. Rexchell A. Querido" => "r.-querido-300x300.jpg",
    "Ms. Jennifer C. Abastillas" => "abastillas.png"
];

$default_leader_image = SITE_URL . "/assets/images/placeholder_leader.png";
$leader_image_base_path = SITE_URL . "/assets/images/leaders/";

function get_leader_image_src($name, $map, $base_path, $default_img) {
    if (isset($map[$name]) && $map[$name] !== null) {
        return $base_path . $map[$name];
    }
    return $default_img;
}

include 'includes/header.php';
?>

<style>
/* General Page Styles */
.about-us-page .content-wrapper { /* Changed from 'main' to a generic class if needed, or remove if not */
    padding-bottom: 3rem; /* Ensure space before footer */
}

.about-us-container {
    max-width: 1200px;
    margin: 0 auto; /* Keep horizontal centering */
    padding: 2rem; /* Original padding for content inside the container */
    display: flex;
    flex-direction: row-reverse; /* Moves nav to the right */
    gap: 2rem; 
    align-items: flex-start; 
}

.sticky-side-nav {
    width: 200px; /* Made smaller */
    flex-shrink: 0; 
    position: sticky;
    /* top: 186px; */ /* Replaced by CSS variable */
    top: calc(var(--navbar-scroll-offset) - 4px); /* scroll-offset (190) - 4px buffer for visual = 186px */
    height: calc(100vh - (var(--navbar-scroll-offset) + 20px)); /* e.g. 100vh - (190px + 20px) */
    overflow-y: auto; 
    /* background-color: var(--light-blue); */ /* Removed background */
    /* box-shadow: 0 2px 8px rgba(0,0,0,0.05); */ /* Removed shadow */
    /* padding: 1.5rem; */ /* Removed padding for a more minimal look */
}

.sticky-side-nav h4 {
    font-size: 1.1rem;
    color: var(--primary-blue);
    margin-bottom: 1rem;
    /* padding-bottom: 0.5rem; */ /* Removed padding */
    /* border-bottom: 1px solid var(--secondary-gold); */ /* Removed border */
}

.sticky-side-nav ul {
    list-style: none;
    padding-left: 0;
}

.sticky-side-nav ul li {
    margin-bottom: 0.5rem;
}

.sticky-side-nav ul li a {
    text-decoration: none;
    color: var(--dark-navy);
    font-size: 0.95rem;
    display: block;
    padding: 0.3rem 0;
    transition: color 0.2s ease, padding-left 0.2s ease;
}

.sticky-side-nav ul li a:hover,
.sticky-side-nav ul li a.active-link { /* Combine hover and active for now */
    color: var(--secondary-gold);
    padding-left: 5px;
}

.sticky-side-nav ul ul { /* Nested lists for sub-items */
    padding-left: 1rem; /* Indent sub-items */
    margin-top: 0.3rem;
}

.sticky-side-nav ul ul li a {
    font-size: 0.9rem;
    color: var(--text-gray);
}

.main-content-area {
    flex-grow: 1; /* Allow content to take remaining space */
    min-width: 0; /* Prevent content from overflowing flex container */
}

.about-section {
    margin-bottom: 3rem;
    padding: 2rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    /* scroll-margin-top: 176px; */ /* Removed from general section, will apply to specific IDs */
}

#about-tspi-ngo, #our-leaders, #about-tspi-mbai, 
#vision-mission, #core-values, #board-of-trustees, #senior-management-team, #our-branches { /* Added #our-branches */
    /* scroll-margin-top: 190px; */ /* Replaced by CSS variable */
    scroll-margin-top: var(--navbar-scroll-offset);
}

.about-section h2 {
    font-size: 2.2rem;
    color: var(--primary-blue);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    /* border-bottom: 2px solid var(--secondary-gold); */ /* Removed underline */
    /* display: inline-block; */ /* Removed display inline-block */
}

.about-section h3 {
    font-size: 1.6rem;
    color: var(--dark-navy);
    margin-top: 1.5rem;
    margin-bottom: 0.8rem;
}

.about-section p, .about-section li {
    line-height: 1.7;
    color: var(--text-gray);
    margin-bottom: 1rem;
}

.about-section ul {
    padding-left: 20px;
    margin-bottom: 1rem;
}

/* NGO Intro Layout */
.ngo-intro-flex { 
    display: block; /* Ensure it takes up block space */
    margin-bottom: 2rem; 
    overflow: auto; /* Ensures container wraps floated children */
}

.ngo-video-wrapper {
    width: 40%; /* Give it a specific width for desktop */
    max-width: 450px; 
    float: left; 
    margin-right: 2rem; 
    margin-bottom: 1rem; 
}

.ngo-text-wrapper {
    /* overflow: hidden; */ /* Removed to allow text to wrap under floated video */
}

/* Video Embed Responsive */
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    height: 0;
    overflow: hidden;
    max-width: 100%; 
    background: #000;
    border-radius: 8px;
    margin-bottom: 0; 
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Vision & Mission Layout */
.vision-mission-flex {
    display: flex;
    gap: 2rem;
    margin-top: 2.5rem; 
    margin-bottom: 2.5rem; 
}

.vision-mission-flex > div {
    flex: 1;
}

/* Core Values Layout */
.core-values-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
    gap: 1.5rem;
    margin-top: 1.5rem;
}

@media (max-width: 992px) { 
    .core-values-grid {
        grid-template-columns: repeat(2, 1fr); 
    }
    .sticky-side-nav {
        display: none; /* Hide sticky nav on smaller screens */
    }
    .about-us-container {
        flex-direction: column; /* Stack content normally */
    }
    .main-content-area {
        width: 100%; /* Ensure main content takes full width */
    }
    #about-tspi-ngo, #our-leaders, #about-tspi-mbai, 
    #vision-mission, #core-values, #board-of-trustees, #senior-management-team, #our-branches {
        /* Ensure scroll margin is still respected on mobile if jumped to from an external link or future top-of-page nav */
        scroll-margin-top: var(--navbar-scroll-offset);
    }
}

@media (max-width: 576px) { 
    .core-values-grid {
        grid-template-columns: 1fr; 
    }
    .ngo-video-wrapper {
        float: none; 
        width: 100%; 
        max-width: 100%;
        margin-right: 0; 
        margin-bottom: 1.5rem;
    }
}

.core-value-item {
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 6px;
    background-color: var(--light-blue); 
}

.core-value-item h4 {
    font-size: 1.05rem;
    color: var(--primary-blue);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.core-value-item h4 i {
    margin-right: 0.5em;
    font-size: 1.3em;
}

.core-value-item p {
    font-size: 0.9rem;
    line-height: 1.5;
}

.core-values-foundation p {
    margin-top: 1.5rem;
    font-style: italic;
    color: var(--dark-navy);
    text-align: center;
    font-size: 1.15rem; 
}

/* Leaders Section Styles */
.leaders-section .leader-category {
    margin-bottom: 1.5rem;
    text-align: center;
}

.leader-card {
    display: flex;
    gap: 1.5rem;
    background-color: var(--light-blue);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    text-align: left; 
    position: relative; 
    cursor: pointer; /* Indicate card is clickable */
}

.leader-card-image {
    flex-shrink: 0;
    width: 120px; 
    height: 120px;
}

.leader-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%; 
    /* border: 3px solid var(--secondary-gold); */ 
}

.leader-card-info {
    flex-grow: 1;
}

.leader-card-info .leader-name {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--dark-navy);
    margin: 0 0 0.25rem 0;
}

.leader-card-info .leader-title {
    font-size: 1rem;
    color: var(--text-gray);
    margin-bottom: 0.5rem;
    font-style: italic;
}

.leader-card-info .leader-position {
    font-size: 1.1rem;
    color: var(--primary-blue);
    font-weight: 500;
    margin-bottom: 0.75rem;
}

.leader-card .leader-quote {
    font-style: italic;
    color: var(--text-gray);
    margin-bottom: 1rem;
    padding-left: 1rem;
    border-left: 3px solid var(--secondary-gold);
}

.leader-bio-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease-in-out, padding-top 0.5s ease-in-out, margin-top 0.5s ease-in-out, border-top-width 0.1s ease-out;
    padding-top: 0;
    margin-top: 0;
    border-top: 0px solid #ddd; 
    clear: both; 
}

.leader-bio-content.show {
    max-height: 2000px; 
    padding-top: 1rem;
    margin-top: 1rem;
    border-top-width: 1px; 
}
.leader-bio-content ul {
    list-style-type: disc;
    padding-left: 20px;
    margin-bottom: 0.5rem;
}
.leader-bio-content li {
    margin-bottom: 0.5rem;
}

.leader-card.active {
    /* background-color: var(--light-gold); */ 
}

.leader-card::after {
    content: '\25BC'; 
    font-size: 1.2rem;
    color: var(--primary-blue);
    position: absolute;
    right: 1.5rem;
    top: 1.5rem; 
    transition: transform 0.3s ease;
}

.leader-card.active::after {
    transform: rotate(180deg); 
}

/* MBAI Section */
.mbai-section .btn-mbai-site {
    display: inline-block;
    background-color: var(--primary-blue);
    color: #fff;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    margin-top: 1rem;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.mbai-section .btn-mbai-site:hover {
    background-color: var(--dark-navy);
    transform: translateY(-2px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .vision-mission-flex {
        flex-direction: column;
    }
    .leader-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .leader-card-image {
        margin-bottom: 1rem;
    }
    .about-section h2 {
        font-size: 1.8rem;
    }
    .about-section h3 {
        font-size: 1.4rem;
    }
}

.about-section h3 i, .core-value-item h4 i {
    margin-right: 0.75em; /* Space between icon and text */
    color: var(--secondary-gold); /* Example color, adjust as needed */
    font-size: 0.9em; /* Adjust size relative to heading */
    vertical-align: middle;
}

/* Branch Section Styles */
.branch-section-intro {
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    color: var(--text-gray);
    text-align: center;
}

.head-office-details {
    background-color: var(--light-blue);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
}

.head-office-details h3 {
    font-size: 1.5rem;
    color: var(--primary-blue);
    margin-bottom: 0.5rem;
}

#our-branches > h3.branches-main-header { /* For "TSPI BRANCHES" heading */
    font-size: 1.8rem;
    color: var(--dark-navy);
    margin-top: 2rem;
    margin-bottom: 1.5rem;
    text-align: center;
}

.region-group {
    margin-bottom: 0.75rem; /* Reduced from 2.5rem */
    padding-bottom: 0rem; /* Reduced from 1.5rem, especially as border is off */
    /* border-bottom: 1px dashed #ddd; */
}
.region-group:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.region-group > h4 { /* Region Name e.g., REGION I - (Ilocos Region) */
    font-size: 1.6rem;
    color: var(--primary-blue);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--light-gold);
}

.province-group {
    margin-bottom: 1.5rem;
    padding-left: 1rem; /* Indent provinces slightly */
}

.province-group > h5 { /* Province Name e.g., ILOCOS NORTE */
    font-size: 1.3rem;
    color: var(--dark-navy);
    margin-bottom: 1rem;
    font-weight: 600;
}

.branch-item {
    background-color: #fdfdfd; 
    padding: 0.75rem; /* Reduced padding */
    border-radius: 6px;
    margin-bottom: 0.5rem; /* Reduced margin-bottom */
    border: 1px solid #eee;
    /* box-shadow: 0 1px 3px rgba(0,0,0,0.04); */
}

.branch-name {
    font-size: 1.1rem;
    font-weight: bold;
    color: var(--primary-blue);
    margin-bottom: 0.25rem;
}

.branch-address {
    font-size: 0.95rem;
    color: var(--text-gray);
    margin-bottom: 0.25rem;
    line-height: 1.5;
}

.branch-contact {
    font-size: 0.9rem;
    color: var(--primary-blue); 
    font-weight: 500;
    cursor: pointer; /* Add cursor pointer for clickable effect */
}

.province-group > .branch-items-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* This was previously changed from 3 to 2 */
    gap: 0.5rem; /* Reduced gap */
}

@media (max-width: 992px) { /* Adjust for medium screens */
    /* This rule can be removed if 2 columns is acceptable down to 768px, 
       or adjusted if a different breakpoint for 2 columns is needed.
       For now, let's assume 2 columns is fine until the 768px breakpoint for 1 column.
    */
    /* .province-group > .branch-items-grid {
        grid-template-columns: repeat(2, 1fr);
    } */
}

@media (max-width: 768px) {
    .region-group > h4 {
        font-size: 1.4rem;
    }
    .province-group > h5 {
        font-size: 1.2rem;
    }
    .branch-item {
        padding: 0.75rem; /* Ensured padding matches for consistency */
        /* margin-bottom will be 0.5rem from the base rule, consider if 1fr needs more spacing */
    }
    .branch-name {
        font-size: 1rem;
    }
    .province-group > .branch-items-grid {
        grid-template-columns: 1fr; /* Stack on smaller screens */
        gap: 0.5rem; /* Ensure gap is also reduced here */
    }
}

/* Styles for collapsible sections */
.collapsible-header {
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem; /* Increased padding for better touch targets */
    background-color: #f8f9fa; /* Light background for the header */
    border-radius: 5px; /* Slightly rounded corners */
    margin-bottom: 0.25rem; /* Small space before content or next header */
    transition: background-color 0.2s ease;
}

.collapsible-header:hover {
    background-color: #e9ecef; /* Darker on hover */
}

/* Remove default heading margins if they are part of .region-group > h4 or .province-group > h5 */
.region-group > h4.collapsible-header,
.province-group > h5.collapsible-header {
    margin-top: 0.5rem; /* Adjusted margin specifically for collapsible headers */
    margin-bottom: 0.25rem; /* Override general h4/h5 margins */
    padding-bottom: 0.75rem; /* Ensure padding is consistent */
    padding-left: 1rem; 
    padding-right: 1rem;
    border-bottom: none; /* Remove the border-bottom, style is now on the header itself */
}

/* Specific styling for region and province headers if needed to override their base h4/h5 styles */
.region-group > h4.collapsible-header {
    font-size: 1.4rem; /* Adjusted to be slightly smaller if too large with background */
    color: var(--primary-blue);
}

.province-group > h5.collapsible-header {
    font-size: 1.2rem; /* Adjusted to be slightly smaller */
    color: var(--dark-navy);
    background-color: #ffffff; /* Different background for province level if desired */
    border: 1px solid #e9ecef;
}
.province-group > h5.collapsible-header:hover {
    background-color: #f8f9fa;
}


.collapsible-header i.toggle-icon {
    transition: transform 0.3s ease;
    font-size: 1em; /* Icon size relative to text */
    color: var(--primary-blue);
}

.collapsible-header .toggle-icon.fa-chevron-up {
    /* Optional: different color when open, if desired */
    /* color: var(--secondary-gold); */
}

.collapsible-content {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.5s ease-in-out, opacity 0.5s ease-in-out;
    border-radius: 0 0 5px 5px;
    margin-bottom: 0.5rem;
}

.collapsible-content.open {
    max-height: 5000px;
    opacity: 1;
    padding: 1rem 1.5rem;
    border: 1px solid #e9ecef;
    border-top: none;
    background-color: #fff;
}

/* Remove the original border-bottom from region group headers as it's now part of the collapsible style */
/* .region-group > h4.collapsible-header { */
    /* border-bottom: 1px solid var(--light-gold); */ /* This is now removed */
/* } */

/* End of styles for collapsible sections */

.province-group > .branch-items-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* This was previously changed from 3 to 2 */
    gap: 0.5rem; /* Reduced gap */
}

@media (max-width: 992px) { /* Adjust for medium screens */
    /* This rule can be removed if 2 columns is acceptable down to 768px, 
       or adjusted if a different breakpoint for 2 columns is needed.
       For now, let's assume 2 columns is fine until the 768px breakpoint for 1 column.
    */
    /* .province-group > .branch-items-grid {
        grid-template-columns: repeat(2, 1fr);
    } */
}

@media (max-width: 768px) {
    .region-group > h4 {
        font-size: 1.4rem;
    }
    .province-group > h5 {
        font-size: 1.2rem;
    }
    .branch-item {
        padding: 0.75rem; /* Ensured padding matches for consistency */
        /* margin-bottom will be 0.5rem from the base rule, consider if 1fr needs more spacing */
    }
    .branch-name {
        font-size: 1rem;
    }
    .province-group > .branch-items-grid {
        grid-template-columns: 1fr; /* Stack on smaller screens */
        gap: 0.5rem; /* Ensure gap is also reduced here */
    }
}

/* Carousel Styles for Head Office */
.head-office-carousel-container {
    width: 100%;
    max-width: 800px; /* Or your desired max width */
    margin: 0 auto 2rem auto; /* Centered, with margin below */
    overflow: hidden;
    position: relative;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.head-office-carousel {
    display: flex;
    width: 400%; /* 100% * number of slides */
    animation: carousel-slide 20s infinite; /* 5s per image */
}

.head-office-carousel img {
    width: 25%; /* 100% / number of slides */
    flex-shrink: 0;
    object-fit: cover; /* Ensures images cover the area nicely */
    height: 400px; /* Or your desired fixed height */
}

@keyframes carousel-slide {
    0%, 20% { margin-left: 0; }       /* Image 1 (0 to 4s) + 1s pause = 5s total */
    25%, 45% { margin-left: -100%; }  /* Image 2 (5s to 9s) + 1s pause */
    50%, 70% { margin-left: -200%; }  /* Image 3 (10s to 14s) + 1s pause */
    75%, 95% { margin-left: -300%; }  /* Image 4 (15s to 19s) + 1s pause */
    100% { margin-left: 0; }         /* Loop back to Image 1 */
}

/* End Carousel Styles */

/* Add styling for branch address links */
.branch-address a {
    color: var(--text-gray); /* Match the text color */
    text-decoration: none; /* Remove underline */
    transition: color 0.2s ease; /* Smooth transition for hover effect */
    display: inline-flex;
    align-items: center;
}

.branch-address a::before {
    content: "\f3c5"; /* Map marker icon from Font Awesome */
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    margin-right: 6px;
    font-size: 0.9em;
    color: var(--secondary-gold);
    opacity: 0.8;
}

.branch-address a:hover {
    color: var(--primary-blue); /* Change text color on hover */
}

.branch-address a:hover::before {
    opacity: 1;
    color: var(--primary-blue);
}

/* Style the head office address link */
.head-office-details .branch-address a {
    font-weight: 500;
}

.head-office-details .branch-address a::before {
    color: var(--primary-blue);
}

</style>

<main>
    <div class="about-us-container">
        <nav class="sticky-side-nav">
            <h4>On this page</h4>
            <ul>
                <li>
                    <a href="#about-tspi-ngo">About TSPI (NGO)</a>
                    <ul>
                        <li><a href="#vision-mission">Mission & Vision</a></li>
                        <li><a href="#core-values">Our Core Values</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#our-leaders">Our Leaders</a>
                    <ul>
                        <li><a href="#board-of-trustees">Board of Trustees</a></li>
                        <li><a href="#senior-management-team">Senior Management Team</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#our-branches">Our Branches</a>
                    <!-- Region sub-links were removed in a previous step -->
                </li>
                <li><a href="#about-tspi-mbai">About TSPI - MBAI</a></li>
            </ul>
        </nav>
        <div class="main-content-area">
            <!-- About TSPI - NGO Section -->
            <section class="about-section" id="about-tspi-ngo">
                <h2>About TSPI (Tulay Sa Pag-unlad Inc.)</h2>
                
                <div class="ngo-intro-flex">
                    <div class="ngo-video-wrapper">
                        <div class="video-container">
                            <iframe width="562" height="316" src="https://www.youtube.com/embed/16McSRc-J34" title="TSPI (Tulay Sa Pag-unlad Inc.) - Bridging the gap between poverty and progress." frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                    </div>
                    <div class="ngo-text-wrapper">
                        <p>Rooted in <strong><em>Christian faith</em></strong>, Tulay sa Pag-unlad, Inc. (TSPI) is a <strong><em>non-profit microfinance organization</em></strong> dedicated to social development. We empower the marginalized by offering programs and services that reflect Christ's call to share His grace and love with the poor (Luke 4:18). Partnering with various institutions, TSPI strives to <strong><em>alleviate poverty</em></strong> by equipping microentrepreneurs and small farmers to build thriving livelihoods while fostering their spiritual growth. We believe these ventures are blessings from God, serving as a "<strong><em>bridge to progress</em></strong>" (tulay sa pag-unlad) for our clients.</p>
                        <p>Inspired by Jesus' <strong><em>miraculous feeding of the multitude</em></strong> (Luke 9:10-17), TSPI began with the principle that even small offerings, when entrusted to God, can yield abundance. Starting with modest resources, we provided micro-loans and business guidance to budding entrepreneurs. Over time, as resources grew, TSPI's <strong><em>transformative mission</em></strong> expanded, becoming a conduit of blessings for numerous individuals and families. God's unwavering faithfulness has sustained TSPI through the years, guiding our response to the evolving needs of our clients. As it is written, "<strong><em>I am He, I am He who will sustain you. I have made you and I will carry you; I will sustain you and I will rescue you.</em></strong>" – God (Isaiah 46:4).</p>
                        <p>Established on <strong><em>October 30, 1981</em></strong>, TSPI <strong><em>pioneered the microfinance industry</em></strong> in the Philippines. Driven by our gospel-centered mission and a deep desire to reach more communities, TSPI forged local alliances with like-minded organizations and individuals. This led to the creation of six independent provincial microfinance NGO partners nationwide. Furthermore, TSPI played a key role in establishing <strong><em>sustainability standards</em></strong> for microfinance NGOs through a national coalition. We proudly stand as a <strong><em>founding member</em></strong> of the two largest microfinance networks in the Philippines: the Alliance of Philippine Partners in Enterprise Development, Inc. (APPEND) in 1991 and the Microfinance Council of the Philippines, Inc. (MCPI) in 1999.</p>
                    </div>
                </div>

                <div class="vision-mission-flex" id="vision-mission">
                    <div class="vision">
                        <h3><i class="fas fa-eye"></i>Our Vision</h3>
                        <p>To see people, live Christ-centered lives with dignity, sufficiency, integrity and hope; demonstrating this through love and service in their families and communities.</p>
                    </div>
                    <div class="mission">
                        <h3><i class="fas fa-bullseye"></i>Our Mission</h3>
                        <p>To provide individuals, families, and communities the opportunities to experience fullness of life in Christ through Christian microenterprise development.</p>
                    </div>
                </div>

                <h3 id="core-values">Our Core Values</h3>
                <p>We value Servanthood, Stewardship, Integrity, and Excellence in delivering our services to our clients and in dealing with our employees, partners and other stakeholders.</p>
                <div class="core-values-grid">
                    <div class="core-value-item">
                        <h4><i class="fas fa-hands-helping"></i>SERVANTHOOD</h4>
                        <p>Each one working with a servant heart.</p>
                    </div>
                    <div class="core-value-item">
                        <h4><i class="fas fa-hand-holding-usd"></i>STEWARDSHIP</h4>
                        <p>Each one taking responsibilities as faithful stewards.</p>
                    </div>
                    <div class="core-value-item">
                        <h4><i class="fas fa-shield-alt"></i>INTEGRITY</h4>
                        <p>Each one doing what is right despite the cost even when no one is looking.</p>
                    </div>
                    <div class="core-value-item">
                        <h4><i class="fas fa-star"></i>EXCELLENCE</h4>
                        <p>Each one working for the glory of God.</p>
                    </div>
                </div>
                <div class="core-values-foundation">
                    <p>Foundational to these core values are God-centeredness, Humility, and Synergy. TSPI's ultimate desire is to glorify God through love and service.</p>
                </div>
            </section>

            <!-- Our Leaders Section -->
            <section class="about-section leaders-section" id="our-leaders">
                <h2>Our Leaders</h2>

                <div class="leader-category">
                    <h3 id="board-of-trustees">Board of Trustees</h3>
                    <p style="text-align: center; margin-bottom: 1.5rem; color: var(--text-gray);">The TSPI Board of Trustees (BOTs) is composed of God-fearing and highly respected individuals of various expertise. Their advocacies are aligned with the God-centered mission of the Organization to serve the less privileged and marginalized sectors and to bring them the good news for God's glory.</p>

                    <!-- David T. Bussau -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('David T. Bussau', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="David T. Bussau">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">David T. Bussau</p>
                            <p class="leader-position">Founder and Chairman Emeritus</p>
                            <p class="leader-quote">"I commend you for your continuous passion, energy and enthusiasm to make Christ known to the communities which you are part of."</p>
                            <div class="leader-bio-content">
                                <p>Mr. Bussau is TSPI's Founder and Chairman Emeritus. He left a successful business career at the age of 35 to pioneer the concept of providing marketplace solutions for social problems, which include health, education, nutrition, water, microfinance, persecution, leadership and sex trafficking. He also actively promotes good governance among not-for-profit organizations.</p>
                                <p>He is the founder of Maranatha Trust, Opportunity International Australia and 15 international movements including Wholistic Transformation Resource Center Foundation Inc. (WTRC) in the Philippines. He serves as a consultant to multinational firms and has a team of dedicated colleagues in Asia who implements and monitors development programs.</p>
                                <p>Mr. Bussau is renowned for his innovative and creative approach to post-disaster rehabilitation, contending that wealth creation and the power of market forces will accelerate poverty alleviation and nation-building. He challenges the old development paradigms and encourages fresh, exciting, audacious and bold out-of-the-box entrepreneurial ideas to liberate the poor. He wants to ignite the creative spark in people to release the amazing potential in each individual to live more dynamic, fulfilling and purpose-driven lives.</p>
                                <p>His inventive mind and passionate heart brought him a number of recognition through the years, namely: Australia's 10 Most Creative Minds (2000), Order of Australia Medal (2001), Ernst & Young Social Entrepreneur of the Year Award (2003), The First Social Enterprise to be inducted into the World Entrepreneur of the Year Academy in Monte Carlo, Monaco (2003), Australian of the Year Finalist (2005), Special Humanitarian Award in Singapore (2005), Hilton Distinguished Entrepreneur of the Year Award in USA (2005), Australian Council for International Development Sir Ron Wilson Human Rights Award (2006), Beta Gamma Sigma Medallion for Entrepreneurship in USA (2007), Senior Australian of the Year Award (2008) and Asia CEO Non-Profit Leadership Team of the Year Finalist in the Philippines (2010).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Atty. Lamberto L. Meer -->
                    <div class="leader-card" id="leader-atty-lamberto-l-meer">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Atty. Lamberto L. Meer', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Atty. Lamberto L. Meer">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Atty. Lamberto L. Meer</p>
                            <p class="leader-position">Chairman</p>
                            <p class="leader-quote">"We are called by God in TSPI for a purpose because God loves us. He called us to serve others and He will never forsake us. Long live TSPI! Praise be to God!"</p>
                            <div class="leader-bio-content">
                                <p>Atty. Meer is the Chairman of the Board of Trustees since 2001. He has been serving in TSPI since September 1984 where he was Corporate Secretary prior to becoming the Chairman. He succeeded the former Chairman Emmanuel N. Pelaez, His Excellency Vice President of the Philippines and Ambassador to the United States, who was his father-in-law. Currently, he is also the Chair of the BOT Executive Committee.</p>
                                <p>Atty. Meer is the Managing Partner of Meer, Meer & Meer, a 69-year-old law firm founded by his grandfather, father, and uncle. He has a deep passion for transformation work, evidenced by his active involvement in various ministries. He is the Convenor of the Pilipino Movement for Transformational Leadership (PMTL) from 2015 to present. It is one of the largest coalitions of Christian organizations in the Philippines whose focus is to form, support, and elect competent Christian servant leaders. His previous positions in line with transformation work were: Senior Head Coordinator of Ligaya ng Panginoon Community (LNP), Chapter Head of Couples for Christ (CFC), and EXCOM Member of Brotherhood of Christian Businessmen and Professionals (BCBP).</p>
                                <p>Atty. Meer is a graduate of AB Economics (Cum Laude) and Bachelor of Laws (LL.B.).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dr. Abraham F. Pascual -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Dr. Abraham F. Pascual', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Dr. Abraham F. Pascual">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Dr. Abraham F. Pascual</p>
                            <p class="leader-position">Vice Chairman</p>
                            <p class="leader-quote">"Surrender your lives to God and you will live with joy and peace. Have faith in God at all times. Here at TSPI, we have every opportunity to follow this command to "love one another", through our work of helping especially those who are in need."</p>
                            <div class="leader-bio-content">
                                <p>Dr. Pascual, Vice Chairman of the Board of Trustees, joined the TSPI Board of Trustees in July 2007. He is the Chair of the BOT Governance Committee.</p>
                                <p>He is a multi-awarded entrepreneur. He was a recipient of the Golden Shell Rising Award from the Department of Trade and Industry (DTI) in 1997, Philippine Marketing Association's Agora Awardee for Outstanding Achievement in Entrepreneurship (Large Scale) in 2001, Go Negosyo's Most Inspiring Bulakeño Entrepreneur in 2008 and PLDT and Go Negosyo's MVP Bossing Awardee in 2013.</p>
                                <p>Dr. Pascual's entrepreneurial expertise made him a backbone to various entities. He is the Chairman of the Board of Directors of Pascual Laboratories, Inc. (PascualLab). He also sits as Member of the Board of Directors to four other companies, namely, L & I Development Corp., Agape Development & Research Corporation, Halang East Corporation, and Octten Holdings Inc.</p>
                                <p>Dr. Pascual completed a Ph.D. in Pharmaceutical Chemistry.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rene E. Cristobal -->
                    <div class="leader-card" id="leader-rene-e-cristobal">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Rene E. Cristobal', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Rene E. Cristobal">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Rene E. Cristobal</p>
                            <p class="leader-position">President</p>
                            <p class="leader-quote">"Let Jesus be the One we love. He must be the One we serve. To give people, to help them and to serve them, in order to know God... that is our purpose in TSPI."</p>
                            <div class="leader-bio-content">
                                <p>Mr. Cristobal, President of the Board of Trustees, has been serving TSPI since October 2000. He is also a Member of the TSPI Mutual Benefit Association, Inc. (TSPI MBAI) Board of Trustees, and Member of Employer's Confederation of the Philippines (ECOP).</p>
                                <p>He is the Founder and Chairman of several companies such as Board of DCL Group of Companies, established since 1978, which provides overseas employment to Filipino professionals, technicians, and maritime officers and crew members in both land based and sea based sectors, mainly to European and American contractors and ship owners; Association of Professionalism in Overseas Employment (ASPROE), composed of non-fee charging and ethical recruitment agencies licensed by the Philippine Overseas Employment Administration (POEA); Philippine-Netherlands Business Council (now Dutch Chamber of Commerce in the Philippines), Family Wellness Center, Inc. (NGO), Knights of Individual Direct Scholarships Foundation (KIDS) accredited by the King Baudouin Foundation of Belgium and Multi-Savings & Loan Association (MULTISLA) for local and overseas employees. Other key organizational involvements include: Organizer and Chairman of joint venture for the "turnkey" construction of feed mills in the Philippines with the Van Aarsen International of Holland; Co-founder and Vice President of Bagong Bayani Foundation, Inc., who honors outstanding overseas Filipino workers (OFWs); Special Adviser of Labor Migration to the ASEAN Confederation of Employers (ACE); Board of Governors of ECOP; Chairman of ECOP's Corporate Social Responsibility; Commissioner of Commission on the Protection/Welfare of the Filipinos Overseas ("Gancayco" Commission) created by, then, President Fidel V. Ramos and Board of Trustee of Philippine Bible Society and of Young Men's Christian Association (YMCA) of the Philippines and Y's Men's Club (Manila & Makati).</p>
                                <p>He is also the Founder and Chief Executive Officer (CEO) of several profit and nonprofit organizations in agriculture & natural farming; innovative construction materials; property development; publishing & social media; intellectual property & copyrights; awareness & prevention of drug addictions; training of addiction counselors; and scholarship programs in public high school. He is also an advocate for the development of bamboo plantation and processed products such as charcoal and activated carbon and construction materials.</p>
                                <p>His companies have received awards from the POEA, Department of Labor and Employment (DOLE), and the Office of the President, and elevated to the "Hall of Fame". He was also Bishop Nicolas Villegas Zamora Awardee, the highest award for lay persons in the IEMELIF (The First Indigenous Evangelical Methodist Church in the Philippines; and Chairman of the Board of its Cathedral in Tondo, Manila. Most recently, he was honored with a knighthood in the Order of Orange of Nassau (The Netherlands).</p>
                                <p>Mr. Cristobal attained a BBA degree (Cum Laude) in the University of the East in 1955, Master of Arts in Economics (candidate) from the same university, and a graduate of the Second Advanced Management Program in the Far East conducted by the Harvard University Graduate School of Business in 1957, Baguio City, as predecessor of Asian Institute of Management (AIM).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ricardo G. Lazatin -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Ricardo G. Lazatin', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Ricardo G. Lazatin">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Ricardo G. Lazatin</p>
                            <p class="leader-position">Treasurer</p>
                            <p class="leader-quote">"How can we serve our Lord? By serving those whom he loves, his people, especially the poor. When we do our work excellently, we are giving thanks to our Lord God who entrusted to us the talent and resources to accomplish our mission. Because the work we do is a blessing from our Lord."</p>
                            <div class="leader-bio-content">
                                <p>Mr. Lazatin, Treasurer of TSPI Board of Trustees, joined the Organization in June 2017. He is presently the Chair of BOT Risk Committee and Vice Chair of BOT Investment Committee. He is also a Member of the TSPI Mutual Benefit Association, Inc. (TSPI MBAI) Advisory Council.</p>
                                <p>His more than 45 years in the banking industry made a mark through the top management and executive level positions he held in various companies. Currently, he is the President/Chief Executive Officer (CEO) of Power Source Group Dev. Corp and several subsidiaries and affiliates; President and Senior Managing Partner of CEOs Inc.; Senior Partner in Argosy Advisers Inc.; President/CEO of Home Funding Inc.; President/CEO of Argosy Finance Corp and Vice Chairman of GSN Land Inc.</p>
                                <p>He spent more than 30 years in three major universal banks and two major finance companies in the Philippines. His previous positions include: Senior Vice President & Group Head of Far East Bank & Trust Co; President/CEO of FEB Leasing & Finance Corp.; Executive Vice President & Group Head of Rizal Commercial Banking Corp. (RCBC); Director of Private Development Corporation of the Philippines (PDCP); Director/Board Member of several other private corporations; President of Philippine Finance Association; Vice President of Financial Executives Institute of the Philippines (FINEX) and Vice President of Asian Leasing Association. He is also involved in other socio-civic organizations.</p>
                                <p>He is an active lifetime-member of the FINEX, FINEX Foundation and Philippine Finance Association (PFA) meriting various FINEX and PFA Presidential Merit, Service and Lifetime awards for several years.</p>
                                <p>He is also actively involved in ministry works as the Chairman and President of Tahanan ng Panginoon Foundation as well as Trustee and Corporate Treasurer of Ang Ligaya ng Panginoon Foundation, Inc.</p>
                                <p>Mr. Lazatin is a graduate of Bachelor of Science in Commerce (Summa Cum Laude) and earned units in Master's degree in Business Economics.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Atty. Cornelio C. Gison -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Atty. Cornelio C. Gison', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Atty. Cornelio C. Gison">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Atty. Cornelio C. Gison</p>
                            <p class="leader-position">Corporate Secretary</p>
                            <p class="leader-quote">"When I started in TSPI, I didn't have a clear idea of my role. If the call at the beginning is not clear, we pray and the Holy Spirit can make us see clearly what is that call for service."</p>
                            <div class="leader-bio-content">
                                <p>Atty. Gison joined TSPI Board of Trustees on March 6, 2006. He is concurrently serving as a Corporate Secretary of TSPI and as Vice Chairman of TSPI Mutual Benefit Association, Inc. (TSPI MBAI) Board of Trustees.</p>
                                <p>He is of Counsel of Salvador, Llanillo and Bernardo Law Office. He is also a Member in different capacities of various groups: Board of Trustees, Andrew Gotianun Foundation, Inc.; Panel of Arbitrators, International Center for Settlement of Investment Disputes, World Bank Arbitration Body, Washington D.C.; and Tax Committee, Filinvest Group. He was a Member of Metrobank Advisory Board, Member/Consultant of its Audit Committee and Partner and Head, Tax Practice of SGV & Co. He was Director of FDC Development and Filinvest Land, and a Founding Member of the Board of Trustees of Philippine Council for NGO Certification (PCNC). He also served as the Corporate Secretary of Philippine Business for Social Progress.</p>
                                <p>Atty. Gison also served the government as Undersecretary for Revenue Operations of the Department of Finance under two administrations (Estrada and Arroyo) from 2000 to 2003. He also had a brief stint as Acting Commissioner of the Bureau of Internal Revenue and a Tax Consultant of Philippine Deposit Insurance Corp and Power Sector Assets and Liabilities Management (PSALM). He was also the former President of the Capital Markets Integrity Corp, a member of the Philippine Stock Exchange Group from 2013 to 2017.</p>
                                <p>Atty. Gison has Bachelor of Laws degree (LL.B.) and a Masters in Comparative Law (LL.C.M) on a fellowship grant. He was a Bar Topnotcher in 1963.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Juanita D. Amatong -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Juanita D. Amatong', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Juanita D. Amatong">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Juanita D. Amatong</p>
                            <p class="leader-position">Trustee</p>
                            <p class="leader-quote">"We in TSPI are not just giving material things, we are also propagating Christian values."</p>
                            <div class="leader-bio-content">
                                <p>Ms. Amatong started her service as a Member of the TSPI Board of Trustees in June 2012. She is the Vice Chair of BOT Audit & Compliance Committee. She is also a Member of the Board of Trustees of TSPI Mutual Benefit Association, Inc. (TSPI MBAI).</p>
                                <p>She is a passionate public servant. She has been in government service for most of her career. She served as Secretary of Finance from December 2003 to February 2005, before she was appointed as a Member of the Monetary Board of Bangko Sentral ng Pilipinas from 2006 to 2011. She was also a Member of the Board of Directors in the World Bank, Washington, D.C. from 1996 to 1998. Until April 2021, she was Member of the Board of Directors of Banko ng Kabuhayan (formerly Rodriguez Rural Bank, Inc). In addition, she is an Adjunct Professor of Public Finance and International Finance in Silliman University. It is a Protestant-affiliated school in Dumaguete City, where she started her career as a teacher and served as Member of the Board of Trustees for 20 years. She now serves as a Member of the Board of Trustees of the Silliman University Foundation Medical Center.</p>
                                <p>Ms. Amatong completed a Bachelor of Science in Business Administration, Master's Degree in Economics and Public Administration and a Ph.D. in Social Science.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Jose D. Fider -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Jose D. Fider', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Jose D. Fider">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Jose D. Fider</p>
                            <p class="leader-position">Trustee</p>
                            <p class="leader-quote">"God is telling us to become more loving in all that we do. He wants to bless you with the life that is full. Wait on the Lord. Be faithful to Him. And always trust in Him."</p>
                            <div class="leader-bio-content">
                                <p>Mr. Fider started serving as a Member of the TSPI Board of Trustees in August 2010. He is the Vice Chair of BOT Risk Committee.</p>
                                <p>His heart to see advancement and growth among the poor extends through his passionate service in various ministries. He is a Service Team Member at Tahanan ng Panginoon, an outreach program that helps the poor communities in Metro Manila. He is also a Trustee of Puso ng Ama Foundation, a non-profit organization serving the youth in the former Payatas dump site, and of Cradle of Joy (COJ) Catholic Progressive School, a non-profit school established by a faith-based organization. Currently, he is the President of BFL Bookstores Inc. and Trans Access Corp.</p>
                                <p>Mr. Fider took up Bachelor of Science in Business Administration at the University of the Philippines.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alberto M. Malvar -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Alberto M. Malvar', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Alberto M. Malvar">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Alberto M. Malvar</p>
                            <p class="leader-position">Trustee</p>
                            <p class="leader-quote">"When we pray, let us ask God what He wants us to do and tell Him "Lord, I will yield to whatever You want."</p>
                            <div class="leader-bio-content">
                                <p>Mr. Malvar's service in TSPI as a Member of the Board of Trustees started in June 2012. At age 40, Mr. Malvar left the corporate world and responded to God's calling to begin a full-time reforestation mission in the Upper Marikina Watershed in an effort to minimize the destructive effects of typhoons to Metro Manila. Together with his family, he founded the Mount Purro Nature Reserve (MPNR), an eco-park and a social enterprise pioneering sustainable travel destination. MPNR promotes a lifestyle of stewardship, simplicity, and sharing. They established the MPNR Foundation, an organization that advocates the rehabilitation of the Upper Marikina Watershed through the empowerment of the upland communities living within the watershed, particularly the Dumagats. Both organizations are vital to his pursuit of an overarching dream of a flood-free Metro Manila and a thriving Upper Marikina Watershed that functions as the "lungs of Metro Manila".</p>
                                <p>His environmental preservation and development work in Antipolo, Rizal has been running for over 30 years highlighting God, Nature and People as the true measures of genuine community development.</p>
                                <p>Mr. Malvar is a graduate of AB Economics with earned units in Masters in Business Administration.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Luz A. Planas -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Luz A. Planas', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Luz A. Planas">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Luz A. Planas</p>
                            <p class="leader-position">Trustee</p>
                            <div class="leader-bio-content">
                                <p>Ms. Planas joined TSPI Board of Trustees in October 2000. She is the Chair of BOT Audit and Compliance Committee. She is the current Chairperson of the Board of Trustees of TSPI Mutual Benefit Association, Inc. (TSPI MBAI).</p>
                                <p>She is the Chairperson of VA Alvarez Realty Corp., where she formerly served as the Treasurer (1995- 2006). She is currently a Board Member to the BF West Homeowners Association.</p>
                                <p>She was previously with the Bank of the Philippine Islands (BPI). She became President and CEO of BPI Forex Corporation from 1999 to 2004. She is actively involved in various civic and religious organizations as a Board Member. Her noteworthy contributions in community development include the renovation of the Resurrection of our Lord Parish Church in BF Parañaque and the greening of BF West Executive Village also in Parañaque City. She also partnered with a local community at her hometown in Roxas City to build the new Pueblo de Panay. She is a passionate professional dancer joining competitions locally and abroad.</p>
                                <p>Ms. Planas obtained degrees are Bachelor of Arts (A.B.), Major in Humanity and Bachelor of Business, Major in Accounting.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Florencia G. Tarriela -->
                    <div class="leader-card">
                        <div class="leader-card-image">
                            <img src="<?php echo get_leader_image_src('Florencia G. Tarriela', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Florencia G. Tarriela">
                        </div>
                        <div class="leader-card-info">
                            <p class="leader-name">Florencia G. Tarriela</p>
                            <p class="leader-position">Trustee</p>
                            <div class="leader-bio-content">
                                <p>Ms. Tarriela joined the TSPI Board of Trustees in October 2003. She is the Chair of the BOT Investment Committee and the Vice Chair of the BOT Governance Committee. Presently, she is the Treasurer, Board of Trustees, of Tulay sa Pag-unlad Mutual Benefit Association, Inc. (TSPI MBAI).</p>
                                <p>She holds the distinction for being the first woman chairperson of the Philippine National Bank (PNB) and the first Filipina Vice President of Citibank N.A. She was a former Undersecretary of the Department of Finance and was an Alternate Monetary Board Member of Bangko Sentral ng Pilipinas (BSP), Land Bank of the Philippines (LBP) and the Philippine Deposit Insurance Corporation (PDIC). She also held several key positions as President of Bank Administration of the Philippines, Independent Director of PNB Life Insurance, Inc. and Director of Bankers Association of the Philippines.</p>
                                <p>Her other current undertakings include: Adviser of the Philippine National Bank (PNB); Independent Director of LT Group, Inc.; Director of PNB Capital and Investment Corporation; Independent Director of PNB International Investments Corporation; Columnist of "Business Options" of the Manila Bulletin and "Financial Executives Institute of the Philippines (FINEX) Folio" of Business World; Director/Vice President of Tarriela Management Company; Director/Vice President/Assistant Treasurer of Gozon Development Corporation; Life Sustaining Member of Bankers Institute of the Philippines and FINEX; Fellow at the Institute of Corporate Directors (ICD), Trustee of FINEX; President of Flor's Garden and Natural Haven's Inc., and Director of Makati Garden Club.</p>
                                <p>As a banker, entrepreneur and an environmentalist, she has been recognized as the Go Negosyo 2018 Woman Intrapreneur Awardee, Most Outstanding Citibank Philippines Alumni Awardee for Community Involvement (2014), and Distinguished Lady Banker awarded by the Bank Administration Institute of the Philippines. She is also a co-author of several inspirational and gardening books.</p>
                                <p>Ms. Tarriela obtained her Bachelor of Science in Business Administration, major in Economics from the University of the Philippines and a Master's in Economics from the University of California, Los Angeles.</p>
                            </div>
                        </div>
                    </div>

                </div> <!-- .leader-category (Board of Trustees) -->

                <div class="leader-category">
                    <h3 id="senior-management-team">Senior Management Team</h3>
                    
                        <!-- Atty. Leonarda D. Banasen -->
                        <div class="leader-card">
                            <div class="leader-card-image">
                                <img src="<?php echo get_leader_image_src('Atty. Leonarda D. Banasen', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Atty. Leonarda D. Banasen">
                            </div>
                            <div class="leader-card-info">
                                <p class="leader-name">Atty. Leonarda D. Banasen</p>
                                <p class="leader-position">Head, Legal Group</p>
                                <div class="leader-bio-content">
                                    <ul>
                                        <li>Bachelor of Laws, Lyceum of the Philippines</li>
                                        <li>AB Legal Management, University of Sto. Tomas</li>
                                        <li>Association of Certified Fraud Examiners (ACFE) Philippines</li>
                                        <li>Integrated Bar of the Philippines</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Ms. Lorna M. Asuncion -->
                        <div class="leader-card">
                            <div class="leader-card-image">
                                <img src="<?php echo get_leader_image_src('Ms. Lorna M. Asuncion', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Ms. Lorna M. Asuncion">
                            </div>
                            <div class="leader-card-info">
                                <p class="leader-name">Ms. Lorna M. Asuncion</p>
                                <p class="leader-position">Head, Treasury Group</p>
                                <div class="leader-bio-content">
                                    <ul>
                                        <li>BSC Major in Accounting, St. Paul College, Quezon CIty</li>
                                        <li>Certified Public Accountant (CPA)</li>
                                        <li>MBA (Units earned), Ateneo Graduate School of Business</li>
                                        <li>Certified Microfinance Expert, Frankfurt School of Finance & Management (e-campus)</li>
                                        <li>Philippine Institute of Certified Public Accountants (PICPA)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mr. Rexchell A. Querido -->
                        <div class="leader-card">
                            <div class="leader-card-image">
                                <img src="<?php echo get_leader_image_src('Mr. Rexchell A. Querido', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Mr. Rexchell A. Querido">
                            </div>
                            <div class="leader-card-info">
                                <p class="leader-name">Mr. Rexchell A. Querido</p>
                                <p class="leader-position">Head, Operations Group</p>
                                <div class="leader-bio-content">
                                    <ul>
                                        <li>BS Accountancy, Urdaneta City University</li>
                                        <li>Head of the Music Ministry, Jesus is Lord Church, Urdaneta City Chapter</li>
                                        <li>Member – Church Management Team, Jesus is Lord Church, Urdaneta City Chapter</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Ms. Jennifer C. Abastillas -->
                        <div class="leader-card">
                            <div class="leader-card-image">
                                <img src="<?php echo get_leader_image_src('Ms. Jennifer C. Abastillas', $leader_images_map, $leader_image_base_path, $default_leader_image); ?>" alt="Ms. Jennifer C. Abastillas">
                            </div>
                            <div class="leader-card-info">
                                <p class="leader-name">Ms. Jennifer C. Abastillas</p>
                                <p class="leader-position">Head, Alliance and Program Management Group</p>
                                <div class="leader-bio-content">
                                    <ul>
                                        <li>B.S. Accountancy, De La Salle University-Manila</li>
                                        <li>Certified Public Accountant (CPA)</li>
                                        <li>MBA (With Distinction), De La Salle University-Manila</li>
                                        <li>Certified Project Management Professional (PMP)</li>
                                        <li>Philippine Institute of Certified Public Accountants (PICPA)</li>
                                        <li>Project Management Institute (PMI)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                </div> 
            </section>

            <!-- Our Branches Section -->
            <section class="about-section" id="our-branches">
                <h2>Our Branches</h2>
                <p class="branch-section-intro">TSPI spreads its operations in 22 provinces in the regions of Ilocos Region, Cordillera Administrative Region, Central Luzon, National Capital Region, Southern Tagalog and Bicol Region.</p>

              

                <div class="head-office-details">
                  <!-- Head Office Carousel -->
                <div class="head-office-carousel-container">
                    <div class="head-office-carousel">
                        <img src="src\assets\mainbranch\basement1.png" alt="TSPI Head Office Image 1">
                        <img src="src\assets\mainbranch\board-room-1.png" alt="TSPI Head Office Image 2">
                        <img src="src\assets\mainbranch\lobby.png" alt="TSPI Head Office Image 3">
                        <img src="src\assets\mainbranch\tspi-client-products.png" alt="TSPI Head Office Image 4">
                    </div>
                </div>
                    <h3>TSPI HEAD OFFICE</h3>
                    <p class="branch-address"><a href="https://www.google.com/maps?q=2363+Antipolo+St.+Guadalupe+Nuevo+Makati+City,+Philippines" target="_blank">2363 Antipolo St. Guadalupe Nuevo Makati City, Philippines</a></p>
                </div>

                <h3 class="branches-main-header">TSPI BRANCHES</h3>

                <!-- REGION I -->
                <div class="region-group" id="region-1">
                    <h4 class="collapsible-header">REGION I - (Ilocos Region) <i class="fas fa-chevron-down toggle-icon"></i></h4>
                    <div class="collapsible-content">
                        <div class="province-group" id="region1-ilocos-norte">
                            <h5 class="collapsible-header">ILOCOS NORTE <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">BATAC</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=City+Pearl+Complex,+National+Hi-way+%237+Caunayan,+Batac+City" target="_blank">City Pearl Complex, National Hi-way #7 Caunayan, Batac City</a></p>
                                        <p class="branch-contact">(0915)123-3381 / (077) 670-2290</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">PINILI</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=National+Highway,+Brgy.+Darat,+Pinili,+Ilocos+Norte" target="_blank">National Highway, Brgy. Darat, Pinili, Ilocos Norte</a></p>
                                        <p class="branch-contact">09773086322</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">LAOAG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Jomel+2+Bldg.,+P.+Gomez+St.,+Brgy.+23,+Laoag+City" target="_blank">Jomel 2 Bldg., P. Gomez St., Brgy. 23, Laoag City</a></p>
                                        <p class="branch-contact">(0949)700-6154 / (0918)-7492894 / (077) 670-4194</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">DINGRAS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Castro+St.,+Brgy.+Albano,+Dingras,+Ilocos+Norte" target="_blank">Castro St., Brgy. Albano, Dingras, Ilocos Norte</a></p>
                                        <p class="branch-contact">(0948) 107-9574 / (0977)365-8289</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region1-ilocos-sur">
                            <h5 class="collapsible-header">ILOCOS SUR <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">CANDON</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Cassy+and+J+Real+State,+National+Highway,+Tablac+Candon+City,+Ilocos+Sur" target="_blank">Cassy and J Real State, National Highway, Tablac Candon City, Ilocos Sur</a></p>
                                        <p class="branch-contact">(0995)772-7107 / (077) 644-0516 / (077)604-4473</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">MAGSINGAL</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Retreta+Bldg.,+Brgy.Vacunero,+Sto.Domingo,+Ilocos+Sur" target="_blank">2nd Floor Retreta Bldg., Brgy.Vacunero, Sto.Domingo, I. Sur</a></p>
                                        <p class="branch-contact">(0915) 273-2796 / (0917)623-4817</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">NARVACAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Soliven+Building+Brgy+Sta.Lucia,+Narvacan,+Ilocos+Sur" target="_blank">2nd Floor Soliven Building Brgy Sta.Lucia, Narvacan, Ilocos Sur</a></p>
                                        <p class="branch-contact">(0995) 454-0399 / (077) 604-0304</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">STA CRUZ ILOCOS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=MAJVV+Building,+Poblacion+Este+Sta+Cruz,+Ilocos+Sur" target="_blank">MAJVV Building, Poblacion Este Sta Cruz, Ilocos Sur</a></p>
                                        <p class="branch-contact">(0915)101-0837</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">VIGAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Galleria+De+Vigan+Bldg.,+Florentino+St.+corner+Governor+Reyes,+Vigan+City" target="_blank">Galleria De Vigan Bldg., 3rd Floor Florentino St. corner Governor Reyes,Vigan City</a></p>
                                        <p class="branch-contact">(0915) 101-0692 / (077) 674-1755</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">CABUGAO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=National+Highway,+Brgy.+Bonifacio+Cabugao,+Ilocos+Sur" target="_blank">National Highway, Brgy. Bonifacio Cabugao,Rebibis Bldg. Ilocos Sur</a></p>
                                        <p class="branch-contact">(0916) 1163901 / (077) 604-0082</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region1-la-union">
                            <h5 class="collapsible-header">LA UNION <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">AGOO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=RMAE+Bldg.,+San+Jose+Norte,+Agoo,+La+Union" target="_blank">2nd Floor RMAE Bldg., San Jose Norte, Agoo, La Union</a></p>
                                        <p class="branch-contact">(0917) 862-1933 / (072) 607-2582</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">ROSARIO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Ordońa+St.,+Poblacion+East,+Rosario,+La+Union" target="_blank">Ordońa St., Poblacion East, Rosario, La Union</a></p>
                                        <p class="branch-contact">(0915)084-3415 / (072) 619- 52-83</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">TUBAO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=YRQ+Building,+Poblacion,+Tubao,+La+Union" target="_blank">2nd Floor YRQ Building, Poblacion, Tubao, La Union</a></p>
                                        <p class="branch-contact">(0915)101-0091 / (072)687-0047</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BACNOTAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Yamaha+Building,+Poblacion,+Bacnotan,+La+Union" target="_blank">2nd Floor Yamaha Building, Poblacion, Bacnotan</a></p>
                                        <p class="branch-contact">(0915)102-5475 / (072) 607-2710</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BALAOAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=National+Highway,+Brgy.+San+Pablo,+Balaoan,+La+Union" target="_blank">National Highway, Brgy. San Pablo, Balaoan, La Union</a></p>
                                        <p class="branch-contact">(0915)101-0381 / (072) 607-0215</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BANGAR</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=LFLP+Bldg,+San+Pedro+St.,+Central+West,+Bangar,+La+Union" target="_blank">2nd Floor LFLP Bldg ( LOLA FRIDA and LOLO PETER), San Pedro St., Central West, Bangar, La Union</a></p>
                                        <p class="branch-contact">(0936)636-0356 / (072) 607-2036</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BAUANG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Florendo+St.,+Central+East,+Bauang,+La+Union" target="_blank">Corner Florendo St., Central East, Bauang, La Union</a></p>
                                        <p class="branch-contact">(0915)102-5003 / (072) 607-2583</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">NAGUILIAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Sobrepeña+Bldg.,+Brgy.+Ortiz,+Naguilian,+La+Union" target="_blank">Sobrepeña Bldg., Brgy. Ortiz, Naguilian, La Union</a></p>
                                        <p class="branch-contact">(0915)102-5004 / (072) 609-1478</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">SAN FERNANDO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Purok+2+Pagdaroan,+San+Fernando+City+La+Union" target="_blank">Purok 2 Pagdaroan, Saan Fernando City La Union</a></p>
                                        <p class="branch-contact">(0936)908-6341 / (072) 607-2394</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region1-pangasinan">
                            <h5 class="collapsible-header">PANGASINAN <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">URDANETA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=UDH+Site,+Dilan+Paurido,+Urdaneta+City,+Pangasinan" target="_blank">Apartment 3, UDH Site, Dilan Paurido, Urdaneta City, Pangasinan</a></p>
                                        <p class="branch-contact">(0927)231-7576 / (075) 656-0226</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">UMINGAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Delos+Santos+Bldg.,+Casadores+St.,+Poblacion+East,+Umingan,+Pangasinan" target="_blank">2nd fl Delos Santos Bldg., Casadores St., Pob East, Umingan, Pangasinan</a></p>
                                        <p class="branch-contact">(0917)704-1289 /</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">TAYUG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Magic+8+Bldg+Rizal+St.+Brgy+C,+Tayug+Pangasinan" target="_blank">2nd Flr Magic 8 Bldg Rizal St. Brgy C, Tayug Pangasinan</a></p>
                                        <p class="branch-contact">(0947) 871-5221 /</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">POZZORUBIO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Lamsen+Bldg.,+Caballero+St.,+Pozzorubio,+Pangasinan" target="_blank">2nd Floor Lamsen Bldg., Caballero St., Pozzorubio, Pangasinan</a></p>
                                        <p class="branch-contact">(0921) 865-4325 / (075) 6322097</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">SAN FABIAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Mc+Arthur+Hi-way,+Cayanga,+San+Fabian,+Pangasinan" target="_blank">Mc Arthur Hi-way, Cayanga, San Fabian, Pangasinan</a></p>
                                        <p class="branch-contact">0963-994-6260 /</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">MANAOAG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Tower+Tabayoyong+St.+Poblacion+Manaoag+Pangasinan" target="_blank">1B Tower Tabayoyong St.Poblacion Manaoag Pangasinan</a></p>
                                        <p class="branch-contact">09672879757/09090988576 /</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">MANGALDAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=RBCP+Bldg.+Rizal+St.+Poblacion,+Mangaldan+Pangasinan" target="_blank">2nd flr RBCP Bldg.Rizal St. Poblacion,Mangaldan Pangasinan</a></p>
                                        <p class="branch-contact">(0915)101-0510/09503135365</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">DAGUPAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=A+%26+G+Bldng.+Caranglaan+District+Dagupan+City" target="_blank">2nd floor A & G Bldng. Caranglaan District Dagupan City</a></p>
                                        <p class="branch-contact">(0915) 101-0741/ 09128709000 /</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BUGALLON</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Samson+Bldg.,+Romulo+Hi-way,+Poblacion,+Bugallon,+Pangasinan" target="_blank">Samson Bldg., Romulo Hi-way, Poblacion, Bugallon, Pangasinan</a></p>
                                        <p class="branch-contact">(0915)101-2697 /09368855455 / (075) 632-0405</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">LINGAYEN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=William+Gabriel+Bldg.+Avenida+Rizal+East,+Poblacion+Lingayen,+Pangasinan" target="_blank">2nd Floor 52 William Gabriel Bldg. Avenida Rizal East, Poblacion Lingayen, Pangasinan</a></p>
                                        <p class="branch-contact">09508826993 /09453100283 / (075) 529-6804</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">ALAMINOS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=34+De+Guzman+St.+Brgy+Palamis+Alaminos+City,+Pangasinan" target="_blank">#34 De Guzman St. Brgy Palamis Alaminos City, Pangasinan</a></p>
                                        <p class="branch-contact">(0930) 244-4933 /</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BOLINAO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Casas+Blgd.+P+Deperio+St.,+Germinal,+Poblacion+Bolinao,+Pangasinan" target="_blank">2nd Floor Casas Blgd. P Deperio St., Germinal, Poblacion Bolinao, Pangasinan (beside town market and basketball court)</a></p>
                                        <p class="branch-contact">(0967) 429-0015 / (075) 636-0264</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">DASOL</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Casolming+St.+Poblacion+Dasol,+Pangasinan" target="_blank">Casolming St. Poblacion Dasol, Pangasinan (beside Dasol Municipal Hall)</a></p>
                                        <p class="branch-contact">09475671687</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">MANGATAREM</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Tagorda+Bldg.,+Lone+Palm+Aqua+Center,+Plaza+Rizal,+Poblacion,+Mangatarem,+Pangasinan" target="_blank">2nd Floor Tagorda Bldg., Lone Palm Aqua Center, Plaza Rizal,Poblacion, Mangatarem, Pangasinan</a></p>
                                        <p class="branch-contact">(0956)759-1339, (0912) 515-1527 / (075) 633-0194</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">CALASIAO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=S+%26+R+Bldg+Nalsian,+Calasiao,+Pangasinan" target="_blank">S & R Bldg Nalsian, Calasiao, Pangasinan</a></p>
                                        <p class="branch-contact">0995-663-0313 / (075) 615-23-06</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">MALASIQUI</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=JPAS+Commercial+Bldg.,+Magsaysay+Street,+Poblacion,+Malasiqui,+Pangasinan" target="_blank">2/F JPAS Commercial Bldg., Magsaysay Street, Poblacion, Malasiqui,Pangasinan.</a></p>
                                        <p class="branch-contact">0938-690-3436 / (075) 632-3252</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">SAN CARLOS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Caranto+Bldg.,+Burgos-Posadas+Street,+San+Carlos,+Pangasinan" target="_blank">Caranto Bldg., Burgos-Posadas Street, San Carlos, Pangasinan</a></p>
                                        <p class="branch-contact">0915-1025-422/09708317315 / (075) 634-1590</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BAYAMBANG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Mayo+Bldg.,+Magsaysay+St.,+Bayambang,+Pangasinan" target="_blank">Mayo Bldg., Magsaysay St., Bayambang, Pangasinan</a></p>
                                        <p class="branch-contact">(0995-336-6257) / (075) 568-6185</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REGION II -->
                <div class="region-group" id="region-2">
                    <h4 class="collapsible-header">REGION II – (CAGAYAN VALLEY) <i class="fas fa-chevron-down toggle-icon"></i></h4>
                    <div class="collapsible-content">
                        <div class="province-group" id="region2-nueva-vizcaya">
                            <h5 class="collapsible-header">NUEVA VIZCAYA <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">SOLANO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Fugaban+Bldg.+Binacao+Street,+Brgy.+Roxas+Solano+Nueva+Vizcaya" target="_blank">Fugaban Bldg.Binacao Street, Brgy. Roxas Solano Nueva Vizcaya (beside house of Mayor Dacayo)</a></p>
                                        <p class="branch-contact">(0926) 861-0473 (0935)184 1826</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region2-isabela">
                            <h5 class="collapsible-header">ISABELA <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">SANTIAGO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Villarica+Bldg.+City+Road+Centro+West,+Santiago+City,+Isabela" target="_blank">3rd flr. Villarica Bldg. City Road Centro West, Santiago City</a></p>
                                        <p class="branch-contact">(0917) 702-6946 / (078) 682-7085</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">ALICIA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Adenas+Bldg.,+Maharlika+Hi-way,+Antonino,+Alicia,+Isabela" target="_blank">2nd floor Adenas Bldg., Maharlika Hi-way, Antonino, Alicia, Isabela</a></p>
                                        <p class="branch-contact">09066682791 / (078) 323-0362</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">CABATUAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=67+Zamora+St.+Purok+3+San+Andres,+Cabatuan+Isabela" target="_blank"># 67 Zamora St. Purok 3 San Andres, Cabatuan Isabela</a></p>
                                        <p class="branch-contact">(0975)445-7653 / (078) 652-5032</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">ILAGAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=JBR+Bldg.,+Calamagui+1st,+Ilagan,+Isabela" target="_blank">2nd Floor JBR Bldg., Calamagui 1st, Ilagan, Isabela</a></p>
                                        <p class="branch-contact">09174194094 / (078) 624-0047</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">CAUAYAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=OH.+Bldg.+Cabatuan+Road+San+Fermin+Cauayan+City,+Isabela" target="_blank">1st floor OH. Bldg. Cabatuan Road San Fermin Cauayan City</a></p>
                                        <p class="branch-contact">(0956) 126-1611 / (078) 6521151</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">ROXAS (SATELITE OFFICE)</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Purok+5+Brgy+Vira,+Roxas+Isabela" target="_blank">Purok 5 Brgy Vira, Roxas Isabela</a></p>
                                        <p class="branch-contact">(0915)104-1990 / (078) 664-2754</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region2-cagayan">
                            <h5 class="collapsible-header">CAGAYAN <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">TUGUEGARAO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=NP+Baccay+Bldg.+118+Balzain+Road,+Balzain+West,+Tuguegarao+City" target="_blank">3/F NP Baccay Bldg. 118 Balzain Road, Balzain West, Tuguegarao City</a></p>
                                        <p class="branch-contact">(0975) 328-0565 / (078) 844-1441</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region2-quirino">
                            <h5 class="collapsible-header">QUIRINO <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">CABARROGUIS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Brgy.+Mangandinay,+Cabarroguis,+Quirino+Province" target="_blank">Brgy. Mangandinay, Cabarroguis, Quirino Province</a></p>
                                        <p class="branch-contact">(0997) 652-3890 (0907)939-4319</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CAR -->
                <div class="region-group" id="region-car">
                    <h4 class="collapsible-header">CAR (CORDILLERA ADMINISTRATIVE REGION) <i class="fas fa-chevron-down toggle-icon"></i></h4>
                    <div class="collapsible-content">
                        <div class="province-group" id="car-benguet">
                            <h5 class="collapsible-header">BENGUET <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">BAGUIO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Luy+Wing+Building,+Magsaysay+Avenue,+Baguio+City" target="_blank">3rd Floor Luy Wing Building, Magsaysay Avenue, Baguio City</a></p>
                                        <p class="branch-contact">(0915) 101-2360 / (074) 665-4504</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REGION III -->
                <div class="region-group" id="region-3">
                    <h4 class="collapsible-header">REGION III – (CENTRAL LUZON) <i class="fas fa-chevron-down toggle-icon"></i></h4>
                    <div class="collapsible-content">
                        <div class="province-group" id="region3-bulacan">
                            <h5 class="collapsible-header">BULACAN <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">SAN JOSE DEL MONTE</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=1262+Blk.+6+Lt.+41+Farmview+Subd.,+Brgy.+Tungkong+Mangga+City+of+San+Jose+Del+Monte,+Bulacan" target="_blank">#1262 Blk. 6 Lt. 41 Farmview Subd., Brgy. Tungkong Mangga City of San Jose Del Monte</a></p>
                                        <p class="branch-contact">(0915) 1010297 / 9124084087</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">STA MARIA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=6035+ME+DR.+F.+Santiago+Laguerta+Str.+Poblacion+Sta.+Maria+Bulacan" target="_blank">6035 ME DR. F. Santiago Laguerta Str. Poblacion Sta. Maria Bulacan</a></p>
                                        <p class="branch-contact">(0915 102 5766 / (044)8 7691250</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BALAGTAS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=3A's+Bldg.+209+Borol+1st,+Balagtas+Bulacan" target="_blank">2/F 3A's Bldg. 209 Borol 1st, Balagtas Bulacan</a></p>
                                        <p class="branch-contact">(0915)1012652 / (044) 8158210</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">MALOLOS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=APB+Bldg.+5630+Paseo+del+Congreso+St.,+Liang,+Malolos+City+Bulacan" target="_blank">2nd Floor, APB Bldg. #5630 Paseo del Congreso St., Liang, Malolos City Bulacan</a></p>
                                        <p class="branch-contact">(0915)1012648 / (044) 7946124</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">BALIUAG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Alexandra+Bldg.,+790+Col.+Tomacruz+St.,+Poblacion,+Baliuag,+Bulacan" target="_blank">2nd Floor Alexandra Bldg., #790 Col. Tomacruz St., Poblacion, Baliuag, Bulacan</a></p>
                                        <p class="branch-contact">(0906)690-2754 / (044) 7677072</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region3-tarlac">
                            <h5 class="collapsible-header">TARLAC <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">CAMILING</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Bonifacio+St.+Poblacion+H,+Camiling,+Tarlac" target="_blank">Bonifacio St. Poblacion H, Camiling, Tarlac</a></p>
                                        <p class="branch-contact">(0915) 101-0879 / (045) 491-0601</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">GERONA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=NJ+Bldg.,+Poblacion+3,+Gerona,+Tarlac" target="_blank">NJ Bldg., Unit 1,2,3 Poblacion 3, Gerona, Tarlac</a></p>
                                        <p class="branch-contact">(0906) 397-5021 / (0907) 452-1131 / (0997)354-5089 / (045) 931-3323</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">PANIQUI</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Jemare+Plaza,+Magallanes+St.+Poblacion+Sur+Paniqui+Tarlac" target="_blank">Unit 5 Jemare Plaza, Magallanes St. Poblacion Sur Paniqui Tarlac</a></p>
                                        <p class="branch-contact">09956213220</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">MONCADA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=BDO+Bldg.,+Mc+Arthur+Hi-way+Poblacion+1,+Moncada,+Tarlac" target="_blank">2nd Floor BDO Bldg., Mc Arthur Hi-way Poblacion 1, Moncada, Tarlac</a></p>
                                        <p class="branch-contact">09167000446 / (045) 606-0224</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">TARLAC</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Clinica+Pascual+Bldg+Zamora+St.+San+Roque+Tarlac+City" target="_blank">Clinica Pascual Bldg Zamora St.San Roque Tarlac City</a></p>
                                        <p class="branch-contact">(0915) 101-2189 / (0977) 204-0166 / 0995-5904493 / (045) 982-6141</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">CAPAS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Manny+Lo+Building,+Mc+Arthur+Hi+Way,+Barangay+Cut+Cut+1st,+Capas,+Tarlac" target="_blank">2nd Floor/Manny Lo Building, Mc Arthur Hi Way, Barangay Cut Cut 1st, Capas, Tarlac</a></p>
                                        <p class="branch-contact">(0950) 811-6922 / 045-491-6244</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region3-pampanga">
                            <h5 class="collapsible-header">PAMPANGA <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">APALIT</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=St.+Jude+Bldg.,+San+Vicente+Apalit+Pampanga" target="_blank">3rd Floor St. Jude Bldg., San Vicente Apalit Pampanga</a></p>
                                        <p class="branch-contact">0915-881-3172 / (045) 6520141</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">SAN FERNANDO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Block+9,+Lot+1,+Dolores+Homesite,+Dolores,+San+Fernando,+Pampanga" target="_blank">Block 9, Lot 1, Dolores Homesite, Dolores CSFP</a></p>
                                        <p class="branch-contact">0915-101-1183 / (045) 4093543</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region3-nueva-ecija">
                            <h5 class="collapsible-header">NUEVA ECIJA <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">GAPAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Magbitang+Apartment,+San+Vicente+Gapan+City+Nueva+Ecija" target="_blank">2nd Flr. Magbitang Apartment, San Vicente Gapan City Nueva Ecija</a></p>
                                        <p class="branch-contact">09959048226 / (044)958-5623</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">PALAYAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Santos+Building,+Barangay+Malate,+Palayan+City,+Nueva+Ecija" target="_blank">Unit 2, Santos Building, Barangay Malate, Palayan City</a></p>
                                        <p class="branch-contact">0995-663-2805 / (044) 940-1627</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">CABANATUAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=BRR+Building,+H.+Concepcion+Cabanatuan+City,+Nueva+Ecija" target="_blank">Unit 1, BRR Building, H. Concepcion Cabanatuan City</a></p>
                                        <p class="branch-contact">0915-102-5089 / (044) 9600687</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">SAN JOSE</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Sanchez+Building,+San+Roque+St.+Rafael+Rueda,+San+Jose+City,+Nueva+Ecija" target="_blank">Sanchez Building, San Roque St. Rafael Rueda, San Jose City, Nueva Ecija</a></p>
                                        <p class="branch-contact">(0906) 654 8658 / (044) 940-7233</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">GUIMBA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=CCN+Bldg.,+Ongiangco+St.+corner+Sarmiento+St.+Guimba,+Nueva+Ecija" target="_blank">2nd Floor CCN Bldg., Ongiangco St. corner Sarmiento St. Guimba, Nueva Ecija</a></p>
                                        <p class="branch-contact">09956220832 / (044)335-0422</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">TALAVERA/STO DOMINGO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Maharlika+Highway,+Calipahan,+Talavera+Nueva+Ecija" target="_blank">Maharlika Highway, Calipahan,T alavera Nueva Ecija ( Beside Ridez Lumber)</a></p>
                                        <p class="branch-contact">(0995) 622-0833 / (044) 958-3340</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REGION IV-A -->
                <div class="region-group" id="region-4a">
                    <h4 class="collapsible-header">REGION IV-A (CALABARZON) <i class="fas fa-chevron-down toggle-icon"></i></h4>
                    <div class="collapsible-content">
                        <div class="province-group" id="region4a-cavite">
                            <h5 class="collapsible-header">CAVITE <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">DBB</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Navjar+Complex,+Don+P.+Campos+Avenue,+Dasmariñas,+Cavite" target="_blank">Stall # 23&24 Navjar Complex, Don P. Campos Avenue, Dasmariñas, Cavite</a></p>
                                        <p class="branch-contact">(0915) 1543625 / (046) 4320685</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">INDANG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Miguel+Tan,+San+Gregorio+St.,+Poblacion+1,+Indang,+Cavite" target="_blank">Miguel Tan, San Gregorio St., Poblacion 1, Indang, Cavite</a></p>
                                        <p class="branch-contact">(0915) 1024868 / (046) 4432354</p>
                                    </div>
                                    <div class="branch-item">
                                        <p class="branch-name">GMA (Gen. Mariano Alvarez)</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Blk+3+lot+35+Congressional+road,+Brgy+San+Gabriel+GMA+Cavite" target="_blank">Blk 3 lot 35 Congressional road, Brgy San Gabriel GMA Cavite. (In front of BDO Office)</a></p>
                                        <p class="branch-contact">0915(1012200) / (046) 4604332</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region4a-laguna">
                            <h5 class="collapsible-header">LAGUNA <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">BIÑAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Simpeys+Bldg.+Brgy.+San+Antonio+Binan+Laguna" target="_blank">Simpeys Bldg. 3rd floor Brgy. San Antonio Binan Laguna</a></p>
                                        <p class="branch-contact">(0977) 3123288 / (049) 5211643</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region4a-batangas">
                            <h5 class="collapsible-header">BATANGAS <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">BALAYAN</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Brgy.+7,+Paz+St.+Balayan+Batangas" target="_blank">Brgy. 7, Paz St. Balayan Batangas</a></p>
                                        <p class="branch-contact">(0950)2781793 / (043) 7236960</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region4a-rizal">
                            <h5 class="collapsible-header">RIZAL <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">ANTIPOLO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=FBM+Bldg.+San+Roque+Antipolo+City" target="_blank">4th Flr. FBM Bldg.San Roque Antipolo City</a></p>
                                        <p class="branch-contact">(0915)1012200 / (046) 4604332</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region4a-quezon">
                            <h5 class="collapsible-header">QUEZON <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">CANDELARIA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Tocy+Bldg.,+Rizal+Avenue+corner+Ona+St.+Candelaria,+Quezon" target="_blank">2nd Floor Tocy Bldg., Rizal Avenue corner Ona St. Candelaria, Quezon</a></p>
                                        <p class="branch-contact">(0929)-742-4981</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Region V -->
                <div class="region-group" id="region-5">
                    <h4 class="collapsible-header">Region V – (Bicol Region) <i class="fas fa-chevron-down toggle-icon"></i></h4>
                    <div class="collapsible-content">
                        <div class="province-group" id="region5-albay">
                            <h5 class="collapsible-header">ALBAY <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">POLANGUI</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Valentin+Bldg.,+Basud,+Polangui,+Albay" target="_blank">Ground Floor Valentin Bldg., Basud, Polangui, Albay</a></p>
                                        <p class="branch-contact">(0915) 101-1005 / (0951) 952-9220</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region5-camarines-norte">
                            <h5 class="collapsible-header">CAMARINES NORTE <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">STA. ELENA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Purok+12,+Brgy.+Poblacion,+Sta.+Elena,+Camarines+Norte" target="_blank">Purok 12, Brgy. Poblacion, Sta. Elena, Camarines Norte</a></p>
                                        <p class="branch-contact">(0908) 818-9163 / (0915) 102-5497</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region5-camarines-sur">
                            <h5 class="collapsible-header">CAMARINES SUR <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">SAN FERNANDO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Zone+3+Bonifacio,+San+Fernando,+Camarines+Sur" target="_blank">Zone 3 Bonifacio, San Fernando, Camarines Sur</a></p>
                                        <p class="branch-contact">(0909) 508-3931 / (0956) 467-6611</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="region5-sorsogon">
                            <h5 class="collapsible-header">SORSOGON <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">BACACAY</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Purok+8+Bonga+Bacacay,+Albay" target="_blank">Purok 8 Bonga Bacacay, Albay</a></p>
                                        <p class="branch-contact">(0917)622-0585 / (0915) 102-5680</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NATIONAL CAPITAL REGION -->
                <div class="region-group" id="region-ncr">
                    <h4 class="collapsible-header">NATIONAL CAPITAL REGION <i class="fas fa-chevron-down toggle-icon"></i></h4>
                    <div class="collapsible-content">
                        <div class="province-group" id="ncr-las-pinas">
                            <h5 class="collapsible-header">LAS PINAS <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">LAS PINAS</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Luis+Bldg.+379+Real+St.+Talon1+Las+pinas+City" target="_blank">Rm. 8, 3rd floor Luis Bldg. 379 Real St. Talon1 Las pinas City</a></p>
                                        <p class="branch-contact">(0915)1012200 / (046) 4604332</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-paranaque">
                            <h5 class="collapsible-header">PARAÑAQUE <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">PARAÑAQUE</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Jackley+Bldg+Lot+2,+Block+17,+Press+Drive+Street+Corner+Dr.+A+Santos+Avenue+Fourth+Estate+Subdivision+Paranaque+City" target="_blank">Jackley BldgLot 2, Block 17, Press Drive Street Corner Dr. A Santos Avenue Fourth Estate Subdivision Paranaque City</a></p>
                                        <p class="branch-contact">(0915)823-8413 / (02)8 2912575</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-taguig">
                            <h5 class="collapsible-header">TAGUIG <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">TAGUIG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=302+Bravo+Cor.Salazar+St.,+Signal+Village+Taguig" target="_blank">#302 Bravo Cor.Salazar St., Signal Village Taguig</a></p>
                                        <p class="branch-contact">(0915)823-8413 / (02)8 2912575</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-makati">
                             <h5 class="collapsible-header">MAKATI <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">TSPI CORPORATE CENTER BRANCH (FORMERLY MAKATI BRANCH)</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=2363+Antipolo+street+Guadalupe+Nuevo+Makati+City" target="_blank">2363 Antipolo street Guadalupe Nuevo Makati City</a></p>
                                        <p class="branch-contact">(0915)1012200 / (046) 4604332</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-quezon-city">
                            <h5 class="collapsible-header">QUEZON CITY <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">QUEZON CITY</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=F%26L+Center+Bldg.,+2211+Commonwealth+Avenue,+Brgy+Holy+Spirit+Quezon+City" target="_blank">3/F Room 311, F&L Center Bldg., 2211 Commonwealth Avenue, Brgy Holy Spirit QC</a></p>
                                        <p class="branch-contact">(0929)-742-4981</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-caloocan">
                            <h5 class="collapsible-header">CALOOCAN CITY <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">BAGONG SILANG</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=Phase+4+Package+8-A,+Block+66+Lot+25+and+27+Bagong+Silang+Caloocan+City" target="_blank">Phase 4 Package 8-A, Block 66 Lot 25 and 27 Bagong Silang Caloocan City</a></p>
                                        <p class="branch-contact">(0915)1012200 / (046) 4604332</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-manila">
                            <h5 class="collapsible-header">MANILA <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">TONDO</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=2408+General+Lucban+Gagalangin+Tondo+Manila" target="_blank">2408 General Lucban Gagalangin Tondo Manila</a></p>
                                        <p class="branch-contact">(0915)823-8413 / (02)8 2912575</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-malabon">
                            <h5 class="collapsible-header">MALABON <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">MALABON</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=93+Bronze+Street+Lybaert+Apartelle+Tugatog+Malabon+City" target="_blank">#93 Bronze Street Lybaert Apartelle 2nd Flr Tugatog Malabon City</a></p>
                                        <p class="branch-contact">(0915)823-8413 / (02)8 2912575</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="province-group" id="ncr-valenzuela">
                            <h5 class="collapsible-header">VALENZUELA <i class="fas fa-chevron-down toggle-icon"></i></h5>
                            <div class="collapsible-content">
                                <div class="branch-items-grid">
                                    <div class="branch-item">
                                        <p class="branch-name">VALENZUELA</p>
                                        <p class="branch-address"><a href="https://www.google.com/maps?q=11+Gov.+Santiago+St.,+Malinta+Valenzuela+City" target="_blank">11 Gov. Santiago St., Malinta Valenzuela City</a></p>
                                        <p class="branch-contact">(0915)823-8413 / (02)8 2912575</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About TSPI - MBAI Section - Commented out as per user request to revert -->
            
            <section class="about-section mbai-section" id="about-tspi-mbai">
                <h2>About TSPI Mutual Benefit Association, Inc. (MBAI)</h2>
                <p>At TSPI Mutual Benefit Association, Inc. (MBAI), the microinsurance arm of TSPI NGO, our foundation is built on a powerful <strong>Vision</strong>: to see people live Christ-centered lives marked by dignity, sufficiency, integrity, and hope, expressed through love and service within their families and communities. Our <strong>Mission</strong> aligns with TSPI's, as we strive to provide opportunities for individuals, families, and communities to experience fullness of life in Christ. We achieve this by offering access to vital microinsurance products and social development services, ensuring security in times of need, including death, accident, and sickness. Officially registered as a non-stock, non-profit organization on <strong>August 31, 2005</strong>, and licensed by the Insurance Commission on <strong>December 22, 2006</strong>, we operate with a deep sense of purpose and commitment.</p>
                
                <h3>TSPI-MBAI Pledge</h3>
                <p>God loves us.</p>
                <p>Our work at TSPI MBA is a blessing from His graciousness.</p>
                <p>We are a part of the TSPI community and share its vision.</p>
                <p>It is our duty to serve with great honor and dignity so we can help in the TSPI MBA mission of spreading to our members the goodness of our God, for them to experience the true love of God, to sustain their livelihood and to provide adequate security through microinsurance products and services.</p>
                <p>All these through our continuous obedience and faithfulness to Christ and most of all, our desire to glorify God.</p>

                <a href="https://mbai.tspi.org/" target="_blank" class="btn-mbai-site">Visit TSPI-MBAI Website <i class="fas fa-external-link-alt" style="font-size: 0.8em; margin-left: 5px;"></i></a>
            </section>
            
        </div> <!-- .main-content-area -->
    </div> <!-- .about-us-container -->
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const leaderCards = document.querySelectorAll('.leader-card');

    leaderCards.forEach(card => {
        card.style.cursor = 'pointer'; 
        card.addEventListener('click', function (event) {
            if (event.target.tagName === 'A' || event.target.closest('A')) {
                return;
            }
            const content = this.querySelector('.leader-bio-content');
            if (content) {
                this.classList.toggle('active');
                content.classList.toggle('show');
            }
        });
    });

    // Check for URL hash to open a specific leader card
    if (window.location.hash) {
        const hash = window.location.hash;
        if (hash.startsWith('#leader-')) {
            try {
                const leaderCardToShow = document.querySelector(hash);
                if (leaderCardToShow && leaderCardToShow.classList.contains('leader-card')) {
                    const content = leaderCardToShow.querySelector('.leader-bio-content');
                    if (content) {
                        // Only add classes if not already open, to prevent re-triggering animation if already open
                        if (!leaderCardToShow.classList.contains('active')) {
                            leaderCardToShow.classList.add('active');
                            content.classList.add('show');
                        }
                        
                        // Enhanced scroll to view logic
                        setTimeout(() => { // Use a short timeout to ensure styles are applied and DOM is ready
                            let navbarOffsetValue = 190; // Default fallback
                            try {
                                const scrollOffsetVar = getComputedStyle(document.documentElement).getPropertyValue('--navbar-scroll-offset').trim();
                                if (scrollOffsetVar) {
                                    navbarOffsetValue = parseInt(scrollOffsetVar, 10) || navbarOffsetValue;
                                }
                            } catch (e) {
                                console.warn('Could not parse --navbar-scroll-offset, using fallback.', e);
                            }

                            const elementRect = leaderCardToShow.getBoundingClientRect();
                            const absoluteElementTop = elementRect.top + window.pageYOffset;
                            const scrollToPosition = absoluteElementTop - navbarOffsetValue;

                            window.scrollTo({
                                top: scrollToPosition,
                                behavior: 'smooth'
                            });
                        }, 100); // 100ms timeout
                    }
                }
            } catch (e) {
                console.error('Error finding or opening leader card from hash:', e);
            }
        }
    }
});
</script>

<script>
// Script for collapsible sections in "Our Branches"
document.addEventListener('DOMContentLoaded', function () {
    const collapsibleHeaders = document.querySelectorAll('#our-branches .collapsible-header');
    collapsibleHeaders.forEach(header => {
        const icon = header.querySelector('.toggle-icon');
        if (icon) {
            icon.classList.add('fa-chevron-down');
        }
        header.addEventListener('click', function () {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.toggle-icon');
            if (content && content.classList.contains('collapsible-content')) {
                content.classList.toggle('open');
                if (content.classList.contains('open')) {
                    if(icon) icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                } else {
                    if(icon) icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                }
            }
        });
    });
});
</script>

<?php
include 'includes/footer.php';
?> 