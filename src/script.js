window.addEventListener('beforeunload', function () {
    window.scrollTo(0, 0);
});

document.addEventListener('DOMContentLoaded', function() {
    // Fade-in animation for all main sections except header/footer
    const fadeSections = document.querySelectorAll('main > section, .footer-content, .footer-bottom');
    function checkVisibility() {
        fadeSections.forEach(section => {
            const rect = section.getBoundingClientRect();
            const isVisible = (rect.top <= window.innerHeight * 0.9);
            if (isVisible) {
                section.classList.add('visible');
            }
        });
    }
    window.addEventListener('scroll', checkVisibility);
    checkVisibility(); // Check on load

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
        const navLinks = document.querySelector('.nav-links');
        navLinks.classList.toggle('active');
    });

    // Remove all click handlers for .dropdown-btn and .nested-btn on mobile
    // (No nested dropdowns for mobile)

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
}); 