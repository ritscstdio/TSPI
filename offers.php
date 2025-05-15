<?php
$page_title = "Our Offers";
$page_description = "Explore the range of financial and social programs offered by TSPI to empower microentrepreneurs and communities.";
$body_class = "offers-page";
include 'includes/header.php';

// Fetch 3 Client Story articles for the carousel
try {
    $stmt_stories = $pdo->query("SELECT a.title, a.slug, a.thumbnail, a.content 
                                 FROM articles a
                                 JOIN article_categories ac ON a.id = ac.article_id
                                 JOIN categories c ON ac.category_id = c.id
                                 WHERE a.status = 'published' AND c.slug = 'stories'
                                 ORDER BY a.published_at DESC
                                 LIMIT 3");
    $client_stories = $stmt_stories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $client_stories = []; // Default to empty array on error
    // Optionally log error: error_log('Error fetching client stories: ' . $e->getMessage());
}

?>

<style>
    .offers-page main {
        padding-top: var(--navbar-height);
    }
    .offers-intro {
        padding: 3rem 1rem; /* Added horizontal padding */
        text-align: left; 
        background-color: #fff; 
    }
    .offers-intro > .container { /* Target direct child container for max-width and centering */
        max-width: 1200px; /* Standard max-width */
        margin: 0 auto; /* Center the container */
        display: flex; 
        align-items: center; 
        gap: 2rem; 
    }

    .offers-intro .intro-text-content {
        flex: 0 0 55%; /* Give slightly more space to text, prevent shrinking */
        max-width: 55%; 
    }

    .offers-intro .intro-text-content h1 {
        font-size: 2.8rem; 
        color: var(--primary-blue);
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .offers-intro .intro-text-content p {
        font-size: 1.1rem; 
        line-height: 1.7;
        color: var(--text-gray);
        margin-bottom: 0; 
    }

    /* Client Story Carousel Styles */
    .client-story-carousel-container {
        flex: 0 0 40%; /* Adjust flex basis, prevent shrinking */
        max-width: 40%; 
        overflow: hidden;
        position: relative;
        border-radius: 8px;
    }

    .client-story-carousel {
        display: flex;
        width: 300%; 
        animation: client-story-slide 15s infinite; 
    }

    .client-story-card {
        width: calc(100% / 3); 
        flex-shrink: 0;
        padding: 0.5rem; 
        box-sizing: border-box;
        text-align: left;
    }
    .story-card-inner {
        background-color: #f8f9fa; 
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        height: 100%; 
        display: flex;
        flex-direction: column;
    }

    .story-card-thumbnail {
        width: 100%;
        height: 150px; 
        object-fit: cover;
        border-radius: 6px;
        margin-bottom: 0.75rem;
    }

    .story-card-title {
        font-size: 1.1rem; 
        font-weight: 600;
        color: var(--dark-navy);
        margin-bottom: 0.4rem;
    }

    .story-card-excerpt {
        font-size: 0.85rem; 
        color: var(--text-gray);
        line-height: 1.4;
        flex-grow: 1;
        margin-bottom: 0.75rem;
    }
    .story-card-link {
        display: inline-block;
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.85rem;
    }
    .story-card-link:hover {
        text-decoration: underline;
    }

    @keyframes client-story-slide {
        0%, 28% { margin-left: 0; }       
        33%, 61% { margin-left: -100%; }  
        66%, 94% { margin-left: -200%; }  
        100% { margin-left: 0; }          
    }

    .program-category {
        padding: 3rem 0;
    }
    .program-category .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    .section-title {
        font-size: 2.5rem;
        color: var(--primary-blue);
        text-align: center;
        margin-bottom: 2.5rem; 
        position: relative;
        padding-bottom: 0.5rem;
    }
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background-color: var(--secondary-gold);
    }

    .offers-grid {
        display: grid;
        /* Default to auto-fit for livelihood, can be overridden */
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem; /* Slightly reduced gap for potentially more cards */
    }

    /* Specific grid for social programs to fit 4 cards */
    .social-programs .offers-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .insurance-products .offers-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Default for insurance cards (often 3 wide) */
    }

    .offer-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        padding: 1.5rem; /* Slightly reduced padding */
        display: flex;
        flex-direction: column;
        text-align: left;
        border: 1px solid #eef;
        opacity: 0; 
        transform: translateY(30px); 
        animation: fadeInUp 0.7s ease-out forwards;
        transition: transform 0.3s ease-out, box-shadow 0.3s ease-out, opacity 0.6s ease-out; 
    }

    .offer-card:nth-child(1) { animation-delay: 0.1s; }
    .offer-card:nth-child(2) { animation-delay: 0.2s; }
    .offer-card:nth-child(3) { animation-delay: 0.3s; }
    .offer-card:nth-child(4) { animation-delay: 0.4s; }
    .offer-card:nth-child(5) { animation-delay: 0.5s; } /* Stagger for up to 5, adjust if more */
    .offer-card:nth-child(6) { animation-delay: 0.6s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .offer-card:hover {
        transform: translateY(-8px) scale(1.02); /* Slightly increased scale */
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
    }

    .offer-card-header {
        display: flex;
        align-items: flex-start; 
        margin-bottom: 1rem;
    }

    .offer-card-icon {
        font-size: 2.2rem; /* Slightly reduced for potentially tighter cards */ 
        color: var(--primary-blue);
        margin-right: 1rem; 
        line-height: 1; 
        flex-shrink: 0;
        width: 30px; /* Give icon a fixed width for alignment */
        text-align: center;
    }

    .offer-card-title {
        font-size: 1.15rem; /* Slightly reduced for 4-column layout */
        font-weight: 700;
        color: var(--dark-navy);
        margin: 0; 
        line-height: 1.3; 
    }

    .offer-card-description {
        font-size: 0.9rem; /* Slightly reduced */
        line-height: 1.5;
        color: var(--text-gray);
        flex-grow: 1; 
    }
    .offer-card-description ul {
        padding-left: 20px;
        margin-top: 0.5rem;
    }
    .offer-card-description li {
        margin-bottom: 0.3rem;
    }
    
    .category-description-text { /* For the KAAGAPAY intro text */
        text-align: center;
        font-size: 1.1rem;
        color: var(--text-gray);
        margin-top: -1rem; /* Pulls it a bit closer to the title */
        margin-bottom: 2rem; 
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    @media (max-width: 1200px) { /* Adjust breakpoint for 4 columns if needed */
        .social-programs .offers-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Fallback to 2 or 3 cols */
        }
    }

    @media (max-width: 992px) {
        .offers-intro > .container { 
            flex-direction: column; 
            text-align: center; 
        }
        .offers-intro .intro-text-content,
        .client-story-carousel-container {
            flex-basis: auto; 
            max-width: 100%; 
        }
        .offers-intro .intro-text-content h1 {
            font-size: 2.2rem;
        }
        .offers-intro .intro-text-content p {
            margin-bottom: 2rem; 
        }
        .client-story-carousel {
            width: 100%; 
            animation: client-story-slide-mobile 15s infinite;
        }
        .client-story-card {
            width: 100%;
            padding: 0.25rem; 
        }
        @keyframes client-story-slide-mobile {
            0%, 28% { margin-left: 0; }      
            33%, 61% { margin-left: -100%; } 
            66%, 94% { margin-left: -200%; } 
            100% { margin-left: 0; }         
        }
        .social-programs .offers-grid,
        .insurance-products .offers-grid {
             grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* More flexible for tablets */
        }
    }

    @media (max-width: 768px) {
        .offers-intro {
            padding: 2rem 1rem; 
        }
        .offers-intro .intro-text-content h1 {
            font-size: 2rem; 
        }
        .offers-grid, /* General fallback for all grids */
        .social-programs .offers-grid,
        .insurance-products .offers-grid {
            grid-template-columns: 1fr; 
        }
        .section-title {
            font-size: 2rem;
        }
        .offer-card {
            padding: 1.5rem;
        }
        .offer-card-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }
        .offer-card-title {
            font-size: 1.1rem; /* Title was 1.15, can be slightly smaller if needed for 1-col */
        }
        .offers-intro .intro-text-content p {
            font-size: 1rem;
        }
        .story-card-thumbnail {
            height: 120px;
        }
        .story-card-title {
            font-size: 1rem;
        }
        .story-card-excerpt {
            font-size: 0.8rem;
        }
    }

</style>

<main>
    <section class="offers-intro">
        <div class="container"> 
            <div class="intro-text-content">
                 <h1>Our Commitment to Empowerment</h1> 
                 <p>TSPI empowers microentrepreneurs through integrated financial and social programs rooted in spiritual values, financial literacy, and livelihood skills development. These programs foster responsible business practices, community solidarity, and collective responsibility.</p>
            </div>

            <?php if (!empty($client_stories)): ?>
            <div class="client-story-carousel-container">
                <div class="client-story-carousel">
                    <?php foreach ($client_stories as $story): ?>
                        <div class="client-story-card">
                            <div class="story-card-inner">
                                <?php 
                                $img_url = SITE_URL . '/assets/default-thumbnail.jpg';
                                if (!empty($story['thumbnail'])) {
                                    if (preg_match('#^https?://#i', $story['thumbnail'])) {
                                        $img_url = $story['thumbnail'];
                                    } else {
                                        $img_url = SITE_URL . '/' . ltrim($story['thumbnail'], '/');
                                    }
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($img_url); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>" class="story-card-thumbnail">
                                <h4 class="story-card-title"><?php echo htmlspecialchars($story['title']); ?></h4>
                                <p class="story-card-excerpt"><?php echo htmlspecialchars(substr(strip_tags($story['content']), 0, 80)); ?>...</p> 
                                <a href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo htmlspecialchars($story['slug']); ?>" class="story-card-link">Read More &rarr;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="program-category livelihood-programs" id="livelihood-loans">
        <div class="container">
            <h2 class="section-title">Livelihood Loan Programs</h2>
            
            <div class="offers-grid">
                <!-- TSPI Kabuhayan Program (TKP) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-users offer-card-icon"></i>
                        <h3 class="offer-card-title">TSPI Kabuhayan Program (TKP)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p>Collateral-free loans up to Php 50,000 for groups (max 30) with members having at least 3-month-old businesses. Weekly payments over 3-6 months. Emphasizes group accountability, credit discipline, and solidarity. Includes microinsurance and access to healthcare, education, housing, and sanitation loans.</p>
                    </div>
                </div>

                <!-- TSPI Maunlad Program (TMP) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-chart-line offer-card-icon"></i>
                        <h3 class="offer-card-title">TSPI Maunlad Program (TMP)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p>Loans from Php 30,000 to Php 300,000 (collateral required above Php 100,000) for established microentrepreneurs (2+ years, Php 60,000+ capital), including existing TKP clients. Flexible weekly, semi-monthly, or monthly repayments over 3-24 months. Offers microinsurance and access to healthcare, education, housing, and sanitation loans.</p>
                    </div>
                </div>

                <!-- TSPI Programang Pang-Agrikultura (TPP) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-seedling offer-card-icon"></i>
                        <h3 class="offer-card-title">TSPI Programang Pang-Agrikultura (TPP)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p>Affordable production loans (up to Php 200,000 for max 5 hectares) for rice, corn, and high-value crop farmers in groups (max 30) with collective responsibility. Lump-sum repayment at harvest or multiple payments for high-value crops. Includes microinsurance, crop insurance, and access to loans for irrigation, other agri-livelihoods, healthcare, housing, and sanitation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="program-category social-programs" id="social-loans">
        <div class="container">
            <h2 class="section-title">Social Loan Programs</h2>
            
            <div class="offers-grid">
                <!-- Home Improvement and Sanitation Loan Program (HISLP) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-home offer-card-icon"></i>
                        <h3 class="offer-card-title">Home Improvement & Sanitation Loan Program (HISLP)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p>Loans for housing/toilet improvements, water source installation, and electrical connection fees. Amounts vary based on program limits, payable over 6 months to 3 years.</p>
                    </div>
                </div>

                <!-- Healthcare Loan Program -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-heartbeat offer-card-icon"></i>
                        <h3 class="offer-card-title">Healthcare Loan Program</h3>
                    </div>
                    <div class="offer-card-description">
                        <p>Facilitates access to Philhealth for organized groups. Members can finance premiums via cash deposit, capital build-up withdrawal, or a loan (up to 6 months, weekly repayment).</p>
                    </div>
                </div>

                <!-- Educational Loan Assistance Program -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-graduation-cap offer-card-icon"></i>
                        <h3 class="offer-card-title">Educational Loan Assistance Program</h3>
                    </div>
                    <div class="offer-card-description">
                        <p>Loans up to Php 20,000 (depending on education level) for school-related expenses (pre-elementary to post-graduate) with weekly repayment over 3-6 months. Covers special training for clients/families.</p>
                    </div>
                </div>

                <!-- Life Insurance and Credit Life Insurance Programs Card -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-user-shield offer-card-icon"></i>
                        <h3 class="offer-card-title">Life Insurance and Credit Life Insurance Programs</h3>
                    </div>
                    <div class="offer-card-description">
                        <p>Microinsurance services, offered through TSPI Mutual Benefit Association, Inc., provide a financial safety net for employees, members, and their immediate families to help cushion the impact of disability or death.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="program-category insurance-products" id="kaagapay-insurance">
        <div class="container">
            <h2 class="section-title">KAAGAPAY Microinsurance Products</h2>
            <p class="category-description-text">Offered through TSPI Mutual Benefit Association, Inc. (MBAI) to provide a financial safety net.</p>
            <div class="offers-grid">
                <!-- Kaagapay Basic Life Insurance Plan (BLIP) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-life-ring offer-card-icon"></i>
                        <h3 class="offer-card-title">Kaagapay Basic Life Insurance Plan (BLIP)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p><strong>Mandatory (P240/year).</strong> Covers death, accidental death/dismemberment/disablement, and total/permanent disability for member and dependents. Includes P120 equity value.</p>
                    </div>
                </div>

                <!-- Kaagapay Life Plus Insurance Plan (Life Plus) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-plus-circle offer-card-icon"></i>
                        <h3 class="offer-card-title">Kaagapay Life Plus Insurance Plan (Life Plus)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p><strong>Optional (P240/year).</strong> Double the benefits of BLIP. Up to 5 units per member.</p>
                    </div>
                </div>

                <!-- Kaagapay Life Max Insurance Plan (Life Max) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-shield-alt offer-card-icon"></i>
                        <h3 class="offer-card-title">Kaagapay Life Max Insurance Plan (Life Max)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p><strong>Optional (P650/year).</strong> Covers death, accidental death, hospital income, and total/permanent disability. Up to 5 units per member.</p>
                    </div>
                </div>

                <!-- Kaagapay Golden Life Insurance Plan (GLIP) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-gem offer-card-icon"></i>
                        <h3 class="offer-card-title">Kaagapay Golden Life Insurance Plan (GLIP)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p><strong>Optional.</strong> For long-term BLIP members (6+ years, before age 66). P9,950 premium over 10 years, coverage up to age 100.</p>
                    </div>
                </div>
                
                <!-- Kaagapay Credit Life Insurance Plan (CLIP) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-credit-card offer-card-icon"></i>
                        <h3 class="offer-card-title">Kaagapay Credit Life Insurance Plan (CLIP)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p><strong>Mandatory for loan recipients.</strong> (P1 per Php 1,000 borrowed per week), offering life and credit life insurance benefits.</p>
                    </div>
                </div>

                <!-- Kaagapay Mortgage Redemption Insurance (MRI) -->
                <div class="offer-card">
                    <div class="offer-card-header">
                        <i class="fas fa-house-damage offer-card-icon"></i>
                        <h3 class="offer-card-title">Kaagapay Mortgage Redemption Insurance (MRI)</h3>
                    </div>
                    <div class="offer-card-description">
                        <p><strong>Mandatory for mortgage borrowers.</strong> (P10 per Php 1,000 borrowed per year), covering the outstanding loan amount.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?> 