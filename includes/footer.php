    <button class="scroll-to-top-btn" id="scrollToTopBtn" aria-label="Scroll to top">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="14" cy="14" r="13" stroke="currentColor" stroke-width="2" fill="none"/>
            <path d="M9 15l5-5 5 5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Customer Service</h3>
                <p><i class="fas fa-phone"></i> +63 9178305017</p>
                <p><i class="fas fa-envelope"></i> partners@tspi.org</p>
                <p><i class="fas fa-envelope"></i> tspicustomercare@tspi.org</p>
                <p><i class="fas fa-phone"></i> (PLDT) (02) 8-403-8627</p>

                <div class="connect-section">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="https://www.facebook.com/TulaySaPagunladInc" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.youtube.com/channel/UCP6ZBA0jPQrWGTaIQuIVDeA" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Careers</h3>
                <p><i class="fas fa-phone"></i> 09178786441</p>
                <p><i class="fas fa-envelope"></i> hrg-mpm@tspi.org</p>
                <a href="<?php echo SITE_URL; ?>/careers.php" class="careers-link">View Job Openings</a>
            </div>
            
            <div class="footer-section app-download">
                <h3>Download Our App!</h3>
                <div class="app-download-row">
                    <a href="https://play.google.com/store/apps/details?id=com.tspi.mfc_app.click" target="_blank" class="app-download-link">
                        <img src="<?php echo resolve_asset_path('/src/assets/application.jpg'); ?>" alt="Download TSPI App">
                    </a>
                    <p>Where you can check your current and past loan transactions as well as track the balance of your CBU in real-time, anytime, anywhere using your smartphone.</p>
                </div>
            </div>
            
            <div class="footer-section">
                <img src="<?php echo resolve_asset_path('/src/assets/DPO Seal.png'); ?>" alt="DPO Seal">
            </div>
        </div>
        
        <div class="footer-bottom">
            <p><a href="<?php echo SITE_URL; ?>/privacy.php">TSPI Privacy Notice</a></p>
            <p>&copy; <?php echo date('Y'); ?> TSPI - Tulay sa Pag-unlad, Inc. All rights reserved.</p>
        </div>
    </footer>

    <!-- Cookie Consent Banner -->
    <div id="cookie-consent-banner" class="cookie-consent-banner">
        <p>This website uses cookies to enhance user experience, analyze site traffic, and for other purposes. By clicking "Accept", you consent to the use of all cookies. For more information, please visit our <a href="<?php echo SITE_URL; ?>/privacy.php" style="color: #e6b54c; text-decoration: underline;">Privacy Policy</a>.</p>
        <div class="cookie-actions">
            <a href="<?php echo SITE_URL; ?>/privacy.php" class="btn-privacy">Privacy Policy</a>
            <button id="cookie-accept-btn">Accept</button>
        </div>
    </div>

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/src/js/script.js"></script>
    <script>
    // Cookie Consent Banner JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const banner = document.getElementById('cookie-consent-banner');
        const acceptBtn = document.getElementById('cookie-accept-btn');
        const consentCookieName = 'tspi_cookie_consent';

        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        }

        if (!getCookie(consentCookieName)) {
            if (banner) {
                banner.style.display = 'block';
            }
        }

        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                setCookie(consentCookieName, 'true', 365); // Consent for 1 year
                if (banner) {
                    banner.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>
</html>
