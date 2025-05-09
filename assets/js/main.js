
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (menuToggle && mobileNav) {
        menuToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
        });
    }
    
    // Scroll to top button
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');
    
    if (scrollToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.add('show-scroll-btn');
            } else {
                scrollToTopBtn.classList.remove('show-scroll-btn');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Similar posts carousel
    const carouselContainer = document.querySelector('.similar-posts-carousel');
    
    if (carouselContainer) {
        const slides = document.querySelector('.carousel-slides');
        const slideItems = document.querySelectorAll('.carousel-slide');
        const prevArrow = document.querySelector('.prev-arrow');
        const nextArrow = document.querySelector('.next-arrow');
        const pagination = document.querySelector('.carousel-pagination');
        
        let slideWidth = slideItems[0].offsetWidth;
        let currentIndex = 0;
        let slidesPerView = calculateSlidesPerView();
        const totalSlides = slideItems.length;
        const maxIndex = Math.max(0, totalSlides - slidesPerView);
        
        // Create pagination dots
        for (let i = 0; i <= maxIndex; i++) {
            const dot = document.createElement('span');
            dot.classList.add('pagination-dot');
            if (i === 0) dot.classList.add('active');
            dot.dataset.index = i;
            dot.addEventListener('click', function() {
                goToSlide(parseInt(this.dataset.index));
            });
            pagination.appendChild(dot);
        }
        
        // Update the active pagination dot
        function updatePagination() {
            const dots = document.querySelectorAll('.pagination-dot');
            dots.forEach((dot, index) => {
                if (index === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        // Go to specific slide
        function goToSlide(index) {
            currentIndex = Math.min(Math.max(index, 0), maxIndex);
            slides.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
            updatePagination();
        }
        
        // Handle navigation arrows
        if (prevArrow) {
            prevArrow.addEventListener('click', function() {
                goToSlide(currentIndex - 1);
            });
        }
        
        if (nextArrow) {
            nextArrow.addEventListener('click', function() {
                goToSlide(currentIndex + 1);
            });
        }
        
        // Calculate number of slides per view based on screen width
        function calculateSlidesPerView() {
            if (window.innerWidth < 500) {
                return 1;
            } else if (window.innerWidth < 768) {
                return 2;
            } else {
                return 3;
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            slideWidth = slideItems[0].offsetWidth;
            slidesPerView = calculateSlidesPerView();
            goToSlide(currentIndex);
        });
    }
    
    // Comment form handling
    const commentForm = document.querySelector('.comment-form');
    
    if (commentForm) {
        const saveInfoCheckbox = document.getElementById('save-info');
        
        // Check for saved commenter info in localStorage
        if (localStorage.getItem('commenterName')) {
            document.getElementById('name').value = localStorage.getItem('commenterName');
        }
        
        if (localStorage.getItem('commenterEmail')) {
            document.getElementById('email').value = localStorage.getItem('commenterEmail');
        }
        
        if (localStorage.getItem('commenterWebsite')) {
            document.getElementById('website').value = localStorage.getItem('commenterWebsite');
        }
        
        if (localStorage.getItem('saveCommenterInfo') === 'true') {
            saveInfoCheckbox.checked = true;
        }
        
        commentForm.addEventListener('submit', function(e) {
            // If save info checkbox is checked, save values to localStorage
            if (saveInfoCheckbox.checked) {
                localStorage.setItem('commenterName', document.getElementById('name').value);
                localStorage.setItem('commenterEmail', document.getElementById('email').value);
                localStorage.setItem('commenterWebsite', document.getElementById('website').value);
                localStorage.setItem('saveCommenterInfo', 'true');
            } else {
                // If unchecked, remove from localStorage
                localStorage.removeItem('commenterName');
                localStorage.removeItem('commenterEmail');
                localStorage.removeItem('commenterWebsite');
                localStorage.removeItem('saveCommenterInfo');
            }
        });
    }
});
