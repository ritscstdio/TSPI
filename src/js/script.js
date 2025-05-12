document.addEventListener('DOMContentLoaded', function() {
    // Fade-in animation for all main sections except header/footer using IntersectionObserver
    const fadeSections = document.querySelectorAll('main > section');
    if ('IntersectionObserver' in window) {
        const observerOptions = { root: null, rootMargin: '0px 0px -10% 0px', threshold: 0 };
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, observerOptions);
        fadeSections.forEach(section => observer.observe(section));
    } else {
        // Fallback to scroll-based check
        function checkVisibility() {
            fadeSections.forEach(section => {
                const rect = section.getBoundingClientRect();
                if (rect.top <= window.innerHeight * 0.9) {
                    section.classList.add('visible');
                }
            });
        }
        window.addEventListener('scroll', checkVisibility);
        checkVisibility(); // Check on load
    }

    // Make hero h1 clickable
    const heroTitle = document.querySelector('.hero h1');
    if (heroTitle) {
        heroTitle.addEventListener('click', function() {
            window.open('https://www.tspi.org/', '_blank');
        });
    }

    // Mobile menu toggle (simple, no nested dropdowns)
    let mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    if (!mobileMenuToggle) {
        mobileMenuToggle = document.createElement('button');
        mobileMenuToggle.className = 'mobile-menu-toggle';
        mobileMenuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        document.querySelector('.main-nav').prepend(mobileMenuToggle);
    }
    mobileMenuToggle.addEventListener('click', function() {
        const mobileNav = document.querySelector('.mobile-nav');
        if (mobileNav) {
            mobileNav.classList.toggle('active');
        }
    });

    // Copy phone number functionality
    const contactLinks = document.querySelectorAll('.contact-link');
    contactLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.innerWidth > 768) { // Desktop
                e.preventDefault();
                const phoneNumber = this.getAttribute('href').replace('tel:', '');
                navigator.clipboard.writeText(phoneNumber).then(() => {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i> Copied to clipboard!';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 2000);
                });
            }
        });
    });

    // News carousel
    const newsCarousel = document.querySelector('.news-carousel');
    if (newsCarousel) {
        // Mock news data - in a real implementation, this would come from a CMS
        const newsData = [
            {
                title: 'TSPI Celebrates 40 Years of Service',
                image: 'assets/news1.jpg',
                link: '#news/40-years'
            },
            {
                title: 'New Branch Opening in Mindanao',
                image: 'assets/news2.jpg',
                link: '#news/mindanao-branch'
            },
            {
                title: 'Annual Report 2024 Released',
                image: 'assets/news3.jpg',
                link: '#news/annual-report'
            }
        ];

        newsData.forEach(news => {
            const newsItem = document.createElement('div');
            newsItem.className = 'news-item';
            newsItem.innerHTML = `
                <a href="${news.link}">
                    <img src="${news.image}" alt="${news.title}">
                    <h3>${news.title}</h3>
                </a>
            `;
            newsCarousel.appendChild(newsItem);
        });

        // Initialize carousel
        let currentIndex = 0;
        const newsItems = document.querySelectorAll('.news-item');
        
        function showNewsItem(index) {
            newsItems.forEach((item, i) => {
                item.style.display = i === index ? 'block' : 'none';
            });
        }

        // Auto-rotate news items
        setInterval(() => {
            currentIndex = (currentIndex + 1) % newsItems.length;
            showNewsItem(currentIndex);
        }, 5000);

        showNewsItem(0);
    }

    // Client stories grid
    const storiesGrid = document.querySelector('.stories-grid');
    if (storiesGrid) {
        // Mock client stories data - in a real implementation, this would come from a CMS
        const clientStories = [
            {
                name: 'Maria Santos',
                location: 'Quezon City',
                story: 'How TSPI helped me start my small business...',
                image: 'assets/client1.jpg',
                link: '#stories/maria-santos'
            },
            {
                name: 'Juan Dela Cruz',
                location: 'Cebu',
                story: 'From small loans to big dreams...',
                image: 'assets/client2.jpg',
                link: '#stories/juan-dela-cruz'
            },
            {
                name: 'Ana Reyes',
                location: 'Davao',
                story: 'Building a better future for my family...',
                image: 'assets/client3.jpg',
                link: '#stories/ana-reyes'
            }
        ];

        clientStories.forEach(story => {
            const storyCard = document.createElement('div');
            storyCard.className = 'story-card';
            storyCard.innerHTML = `
                <a href="${story.link}">
                    <img src="${story.image}" alt="${story.name}">
                    <h3>${story.name}</h3>
                    <p class="location">${story.location}</p>
                    <p class="story-preview">${story.story}</p>
                </a>
            `;
            storiesGrid.appendChild(storyCard);
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Search functionality
    const searchBar = document.querySelector('.search-bar input');
    const searchButton = document.querySelector('.search-bar button');
    
    if (searchBar && searchButton) {
        searchButton.addEventListener('click', function() {
            const searchTerm = searchBar.value.trim();
            if (searchTerm) {
                // In a real implementation, this would trigger a search API call
                console.log('Searching for:', searchTerm);
            }
        });

        searchBar.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchButton.click();
            }
        });
    }

    // Similar Posts Carousel
    const carousel = document.querySelector('.similar-posts-carousel');
    if (carousel) {
        const slidesContainer = carousel.querySelector('.carousel-slides');
        const slides = Array.from(slidesContainer.children);
        const prevButton = document.querySelector('.similar-posts-carousel-container .prev-arrow');
        const nextButton = document.querySelector('.similar-posts-carousel-container .next-arrow');
        const paginationContainer = document.querySelector('.similar-posts-carousel-container .carousel-pagination');
        
        let itemsPerView = 3;
        const totalSlides = slides.length;
        let currentCardIndex = 0; // Index of the leftmost visible card

        function setItemsPerView() {
            if (window.innerWidth <= 500) {
                itemsPerView = 1;
            } else if (window.innerWidth <= 768) {
                itemsPerView = 2;
            } else {
                itemsPerView = 3;
            }
        }

        function createPagination() {
            paginationContainer.innerHTML = ''; // Clear existing dots
            // Number of possible slide positions when scrolling one card at a time
            const totalPositions = Math.max(totalSlides - itemsPerView + 1, 1);
            for (let i = 0; i < totalPositions; i++) {
                const dot = document.createElement('button');
                dot.classList.add('pagination-dot');
                dot.addEventListener('click', () => {
                    currentCardIndex = i;
                    updateCarousel();
                });
                paginationContainer.appendChild(dot);
            }
        }

        function updatePaginationDots() {
            const paginationDots = Array.from(paginationContainer.children);
            // Highlight the dot that matches the currentCardIndex position
            paginationDots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentCardIndex);
            });
        }

        function updateCarousel() {
            setItemsPerView(); // Ensure itemsPerView is up-to-date
            const slideWidthPercentage = 100 / itemsPerView;
            slidesContainer.style.transform = `translateX(-${currentCardIndex * slideWidthPercentage}%)`;
            
            prevButton.disabled = currentCardIndex === 0;
            nextButton.disabled = currentCardIndex >= totalSlides - itemsPerView;
            updatePaginationDots();
        }

        prevButton.addEventListener('click', () => {
            if (currentCardIndex > 0) {
                currentCardIndex--;
                updateCarousel();
            }
        });

        nextButton.addEventListener('click', () => {
            if (currentCardIndex < totalSlides - itemsPerView) {
                currentCardIndex++;
                updateCarousel();
            }
        });
        
        window.addEventListener('resize', () => {
            const oldItemsPerView = itemsPerView;
            setItemsPerView();
            if (oldItemsPerView !== itemsPerView) {
                currentCardIndex = Math.min(currentCardIndex, totalSlides - itemsPerView);
                currentCardIndex = Math.max(0, currentCardIndex);
                createPagination();
                updateCarousel();
            }
        });

        setItemsPerView();
        createPagination();
        updateCarousel();
    }

    // Search results live dropdown
    const liveInput = document.getElementById('liveSearchInput');
    const resultsContainer = document.getElementById('searchResults');
    let searchTimeout;
    if (liveInput && resultsContainer) {
        liveInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            if (!query) {
                resultsContainer.style.display = 'none';
                resultsContainer.innerHTML = '';
                return;
            }
            // Debounce
            searchTimeout = setTimeout(() => {
                // Relative fetch to search.php in current directory
                fetch(`search.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const a = document.createElement('a');
                                a.href = `${window.location.origin}/TSPI/article.php?slug=${item.slug}`;
                                a.textContent = item.title;
                                resultsContainer.appendChild(a);
                            });
                        } else {
                            const p = document.createElement('p');
                            p.className = 'no-results';
                            p.textContent = 'No results found.';
                            resultsContainer.appendChild(p);
                        }
                        resultsContainer.style.display = 'block';
                    })
                    .catch(err => {
                        console.error('Search error', err);
                        resultsContainer.style.display = 'none';
                    });
            }, 300);
        });
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!resultsContainer.contains(e.target) && e.target !== liveInput) {
                resultsContainer.style.display = 'none';
            }
        });
    }
}); 