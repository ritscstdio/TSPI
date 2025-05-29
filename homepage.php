<?php
$body_class = 'homepage-body';
$page_title = "Homepage";
$page_description = "Tulay sa Pag-unlad, Inc. | Empowering communities through financial inclusion and sustainable development.";
include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero" style="position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; min-height: 350px;">
        <video class="hero-bg-video" autoplay muted loop playsinline preload="auto" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0; opacity: 0; transition: opacity 1.5s ease-in-out;">
            <source src="<?php echo resolve_asset_path('/src/assets/TSPI Intro.mp4'); ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-bg-fallback" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: var(--primary-blue); z-index: 0;"></div>
        <div class="hero-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to right, var(--primary-blue), var(--dark-navy)); opacity: 0.7; z-index: 1;"></div>
        <div class="hero-fade-up" style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; text-align: center;">
            <h1>TSPI CELEBRATES GOD'S FAITHFULNESS THROUGH THE DECADES</h1>
            <p>Tulay sa Pag-unlad, Inc. (TSPI) is a microfinance NGO in the Philippines dedicated to empowering communities through financial inclusion and sustainable development. For decades, we have been committed to providing financial services and support to underserved communities, helping them build better futures.</p>
            <a href="<?php echo SITE_URL; ?>/user/membership-form.php?join=true" class="cta-button">Join Us!</a>
        </div>
    </section>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top-btn" id="scrollToTopBtn" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Scroll to top">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="14" cy="14" r="13" stroke="currentColor" stroke-width="2" fill="none"/>
            <path d="M9 15l5-5 5 5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <!-- News Grid Section -->
    <section class="news-grid-section">
        <h2 style="text-align: center; margin-top: 2rem;">Latest TSPI News!</h2>
        <?php
        // Fetch latest published contents
        $stmt = $pdo->prepare("SELECT title, slug, thumbnail, content, published_at FROM content WHERE status = 'published' ORDER BY published_at DESC LIMIT 3");
        $stmt->execute();
        $latest_contents = $stmt->fetchAll();
        ?>
        <div class="news-grid">
        <?php foreach ($latest_contents as $art): ?>
            <?php
            // Improved thumbnail handling
            if ($art['thumbnail']) {
                if (preg_match('#^https?://#i', $art['thumbnail'])) {
                    $bg = $art['thumbnail'];
                } else {
                    // Check if it's a path to an upload in the media directory
                    if (strpos($art['thumbnail'], 'uploads/media/') !== false) {
                        $filename = basename($art['thumbnail']);
                        $bg = SITE_URL . '/uploads/media/' . $filename;
                    } else if (strpos($art['thumbnail'], 'src/assets/') !== false) {
                        $filename = basename($art['thumbnail']);
                        $bg = SITE_URL . '/src/assets/' . $filename;
                    } else {
                        $bg = resolve_asset_path($art['thumbnail']);
                    }
                }
            } else {
                $bg = SITE_URL . '/src/assets/default-thumbnail.jpg';
            }
            $excerpt = substr(strip_tags($art['content']), 0, 100);
            ?>
            <div class="news-card" style="background-image: url('<?php echo $bg; ?>');">
                <div class="news-card-overlay"></div>
                <div class="news-card-title"><?php echo sanitize($art['title']); ?></div>
                <div class="news-card-desc">
                    <div class="news-card-desc-text"><?php echo sanitize($excerpt); ?>...</div>
                    <div class="news-card-meta"><?php echo date('M j, Y', strtotime($art['published_at'])); ?></div>
                    <a href="<?php echo SITE_URL; ?>/content.php?slug=<?php echo $art['slug']; ?>" class="cta-button read-this-btn">Read this!</a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="<?php echo SITE_URL; ?>/news.php" class="read-more-news-link">Read more?</a>
        </div>
    </section>

    <!-- Message Sections -->
    <section class="message-section">
        <div class="message-content">
            <div class="message-image">
                <img src="<?php echo resolve_asset_path('/src/assets/chairman.jpg'); ?>" alt="Chairman">
            </div>
            <div class="message-text">
                <h2>THE TRANSFORMING POWER OF GOD'S LOVE</h2>
                <h3>CHAIRMAN | ATTY. LAMBERTO L. MEER</h3>
                <p>We are called by God for a purpose, to serve as responsible stewards of His work because He loves us. Reflecting back many years ago, I had a vague picture of God's love. Until one day in January, 1981, my wife and I joined around a dozen other couples for a "Marriage Encounter Weekend" in Tagaytay. It was during that weekend that I had a deeply personal and life-changing encounter with my God and felt the transforming power of His love. Since then, I have allowed God to take control of my life and have become increasingly aware of His work in all aspects of my life – family, career and civic work, and I have seen how God has started to use me in His vineyard...</p>
                <a href="<?php echo SITE_URL; ?>/AboutUs.php#leader-atty-lamberto-l-meer" class="read-more">Read More <span class="read-more-arrow">→</span></a>
            </div>
        </div>
    </section>

    <section class="message-section reverse">
        <div class="message-content">
            <div class="message-text">
                <h2>KEEPING THE SAMBAYANIHAN SPIRIT ALIVE</h2>
                <h3>PRESIDENT | RENE E. CRISTOBAL</h3>
                <p>The Bible speaks of "40" as a symbol of new life, growth, transformation and transition from completing a great task to gearing up for a greater task. In 2021, TSPI celebrated its 40th Anniversary, which marked the unchanging mission of bringing Good News to the less privileged and hope to the communities. We believe that God's faithfulness for the last 40 years allowed TSPI to build on its firm foundation that enabled it to rise and withstand all tests. As we prepare for greater tasks ahead and for reaching new milestones, we are confident that our plans and programs have evolved and aligned to the changes and challenges of time. Furthermore, our strategic alliances and innovative digitization programs usher us to see the brighter future where we can serve more as good stewards and faithful servants...</p>
                <a href="<?php echo SITE_URL; ?>/AboutUs.php#leader-rene-e-cristobal" class="read-more">Read More <span class="read-more-arrow">→</span></a>
            </div>
            <div class="message-image">
                <img src="<?php echo resolve_asset_path('/src/assets/president.jpg'); ?>" alt="President">
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="testimonial">
            <blockquote>
                "Naniniwala ako na dinala ako ng Panginoon dito. Sa 17 years ko sa company, still, nakikita ko at nararamdaman ang joy working sa company. Kahit kapagod at mahirap, God still supplies my strength and He supply and recharge me every day. He hired me to be a blessing to others."
            </blockquote>
            <cite>Noriedee Boac<br>Area Manager</cite>
        </div>
        <div class="testimonial">
            <blockquote>
                "Dahil sa Panata at sa Usapang Paglago ng TSPI na itinuturo sa amin, naisip ko na ayusin ko ang aking sarili dahil ako rin naman ang nahihirapan. Pinipilit ko na ngayong gawin kung ano ang nasa kasulatan."
            </blockquote>
            <cite>Nanay Rechelda Estoque<br>TSPI Client, Tubao Branch</cite>
        </div>
    </section>

</main>

<script>
window.addEventListener('scroll', function() {
    const btn = document.getElementById('scrollToTopBtn');
    if (window.scrollY > 80) {
        btn.classList.add('show-scroll-btn');
    } else {
        btn.classList.remove('show-scroll-btn');
    }
});

// Improved video loading handling
document.addEventListener('DOMContentLoaded', function() {
    const heroVideo = document.querySelector('.hero-bg-video');
    const fallbackBg = document.querySelector('.hero-bg-fallback');
    
    if (heroVideo) {
        // Try to load video with timeout
        const videoLoadTimeout = setTimeout(function() {
            // If video hasn't loaded after 5 seconds, show fallback
            if (heroVideo.readyState < 3) {
                console.log('Video taking too long to load, showing fallback');
                if (fallbackBg) fallbackBg.style.opacity = '1';
            }
        }, 5000);
        
        // Set up event listeners for video
        heroVideo.addEventListener('canplaythrough', function() {
            // Video can play through, fade it in
            clearTimeout(videoLoadTimeout);
            heroVideo.style.opacity = '1';
            heroVideo.style.transition = 'opacity 1.8s ease-in-out';
            // Hide fallback if it was showing
            if (fallbackBg) fallbackBg.style.opacity = '0';
        });
        
        heroVideo.addEventListener('error', function(e) {
            // Video error occurred
            console.error('Video error:', e);
            clearTimeout(videoLoadTimeout);
            // Show fallback
            if (fallbackBg) fallbackBg.style.opacity = '1';
        });
        
        // Force a reload of the video if it hasn't started loading
        if (heroVideo.networkState === HTMLMediaElement.NETWORK_EMPTY) {
            heroVideo.load();
        }
    }
});
</script>

<style>
/* Additional styles to override any underlines */
.cta-button {
    text-decoration: none !important; /* Remove underline from CTA buttons */
}

/* Video fallback styling */
.hero-bg-fallback {
    opacity: 0;
    transition: opacity 1s ease;
}
</style>

<?php include 'includes/footer.php'; ?> 