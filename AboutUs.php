<?php
$page_title = "About Us";
$body_class = "about-us-page"; // For any global styles if needed, or specific targeting
require_once 'includes/config.php';
include 'includes/header.php';
?>

<style>
/* General Page Styles */
.about-us-page main {
    padding-bottom: 3rem; /* Ensure space before footer */
}

.about-us-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.about-section {
    margin-bottom: 3rem;
    padding: 2rem;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.about-section h2 {
    font-size: 2.2rem;
    color: var(--primary-blue);
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--secondary-gold);
    display: inline-block;
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

/* Video Embed Responsive */
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    height: 0;
    overflow: hidden;
    max-width: 100%;
    background: #000;
    border-radius: 8px;
    margin-bottom: 1.5rem;
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
    margin-top: 1.5rem;
}

.vision-mission-flex > div {
    flex: 1;
}

/* Core Values Layout */
.core-values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.core-value-item {
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 6px;
    background-color: var(--light-blue); /* Light blue for item background */
}

.core-value-item h4 {
    font-size: 1.2rem;
    color: var(--primary-blue);
    margin-bottom: 0.5rem;
}

.core-values-foundation p {
    margin-top: 1.5rem;
    font-style: italic;
    color: var(--dark-navy);
    text-align: center;
}

/* Leaders Section Styles */
.leaders-section .leader-category {
    margin-bottom: 2.5rem;
}

.leader-category h3 { /* For "Board of Trustees" and "Senior Management Team" */
    font-size: 1.8rem;
    color: var(--primary-blue);
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
}

.leader-card-image {
    flex-shrink: 0;
    width: 120px; /* Adjust as needed */
    height: 120px;
}

.leader-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%; /* Circular images */
    border: 3px solid var(--secondary-gold);
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

.leader-bio-toggle {
    background-color: var(--secondary-gold);
    color: var(--dark-navy);
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: background-color 0.2s ease;
}

.leader-bio-toggle:hover {
    background-color: #d4a33c; /* Darker gold */
}

.leader-bio-toggle .toggle-icon {
    transition: transform 0.3s ease;
}

.leader-bio-toggle.active .toggle-icon {
    transform: rotate(180deg);
}

.leader-bio-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease-out, padding-top 0.5s ease-out, margin-top 0.5s ease-out;
    padding-top: 0;
    margin-top: 0;
    border-top: 1px solid #ddd;
}

.leader-bio-content.show {
    max-height: 2000px; /* Adjust if bios are very long */
    padding-top: 1rem;
    margin-top: 1rem;
}
.leader-bio-content ul {
    list-style-type: disc;
    padding-left: 20px;
}
.leader-bio-content li {
    margin-bottom: 0.5rem;
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
    .leader-bio-toggle {
        margin: 0 auto;
        display: table; /* Center button */
    }
    .about-section h2 {
        font-size: 1.8rem;
    }
    .about-section h3 {
        font-size: 1.4rem;
    }
}

</style>

<div class="about-us-container">

    <!-- About TSPI - NGO Section -->
    <section class="about-section" id="about-tspi-ngo">
        <h2>About TSPI (Tulay Sa Pag-unlad Inc.)</h2>
        
        <div class="video-container">
            <iframe width="562" height="316" src="https://www.youtube.com/embed/16McSRc-J34" title="TSPI (Tulay Sa Pag-unlad Inc.) - Bridging the gap between poverty and progress." frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>

        <p>Tulay sa Pag-unlad, Inc. is a Christian, non-stock, non-profit microfinance NGO engaged in social development programs and services to fulfill Christ's commission to proclaim the Good News of His saving grace and loving kindness to the poor (Luke 4:18). TSPI works with other institutions towards poverty alleviation by supporting microentrepreneurs and small farmers to grow and sustain their livelihood, while nurturing their spiritual well-being. TSPI believes that these microenterprises and farms are God's blessings to the clients and their bridge to progress (tulay sa pag-unlad).</p>
        <p>The founding of TSPI was inspired by Jesus' miracle of feeding the multitudes (Luke 9:10-17). The little that was willingly offered to the Lord, incredibly became an abundant supply that satisfied everyone. Starting with a few, TSPI provided opportunities for microenterprise development, through micro-loans and business coaching. Eventually, resources started pouring in, allowing TSPI's transformation mission to become a channel of blessings to many. God's faithfulness sustained TSPI through the decades and guided it in addressing the changing needs of its clients over the years. "I am He, I am He who will sustain you. I have made you and I will carry you; I will sustain you and I will rescue you." – God (Isaiah 46:4).</p>
        <p>TSPI was established on October 30, 1981 as the pioneer of the Philippines' microfinance industry. Inspired by its gospel-driven mission and passion to serve more communities, TSPI formed local partnerships with like-minded groups and individuals. This gave birth to six independent provincial microfinance NGO partners across the country. TSPI also spearheaded a coalition in setting up standards for microfinance NGOs to be sustainable. TSPI is a founding member of the two largest microfinance networks in the country – Alliance of Philippine Partners in Enterprise Development, Inc. (APPEND) in 1991 and Microfinance Council of the Philippines, Inc. (MCPI) in 1999.</p>

        <div class="vision-mission-flex">
            <div class="vision">
                <h3>Our Vision</h3>
                <p>To see people, live Christ-centered lives with dignity, sufficiency, integrity and hope; demonstrating this through love and service in their families and communities.</p>
            </div>
            <div class="mission">
                <h3>Our Mission</h3>
                <p>To provide individuals, families and communities the opportunities to experience fullness of life in Christ through Christian microenterprise development.</p>
            </div>
        </div>

        <h3>Our Core Values</h3>
        <p>We value Servanthood, Stewardship, Integrity, and Excellence in delivering our services to our clients and in dealing with our employees, partners and other stakeholders.</p>
        <div class="core-values-grid">
            <div class="core-value-item">
                <h4>SERVANTHOOD</h4>
                <p>Each one working with a servant heart.</p>
            </div>
            <div class="core-value-item">
                <h4>STEWARDSHIP</h4>
                <p>Each one taking responsibilities as faithful stewards.</p>
            </div>
            <div class="core-value-item">
                <h4>INTEGRITY</h4>
                <p>Each one doing what is right despite the cost even when no one is looking.</p>
            </div>
            <div class="core-value-item">
                <h4>EXCELLENCE</h4>
                <p>Each one working for the glory of God.</p>
            </div>
        </div>
        <div class="core-values-foundation">
            <p>Foundational to these core values are God-centeredness, Humility, and Synergy. TSPI's ultimate desire is to glorify God through love and service.</p>
        </div>
    </section>

    <!-- Our Leaders Section will be added here -->
    <section class="about-section leaders-section" id="our-leaders">
        <h2>Our Leaders</h2>

        <div class="leader-category">
            <h3>Board of Trustees</h3>
            <p style="text-align: center; margin-bottom: 1.5rem; color: var(--text-gray);">The TSPI Board of Trustees (BOTs) is composed of God-fearing and highly respected individuals of various expertise. Their advocacies are aligned with the God-centered mission of the Organization to serve the less privileged and marginalized sectors and to bring them the good news for God's glory.</p>

            <!-- David T. Bussau -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="David T. Bussau">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">David T. Bussau</p>
                    <p class="leader-position">Founder and Chairman Emeritus</p>
                    <p class="leader-quote">"I commend you for your continuous passion, energy and enthusiasm to make Christ known to the communities which you are part of."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Founder and Chairman Emeritus <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Mr. Bussau is TSPI's Founder and Chairman Emeritus. He left a successful business career at the age of 35 to pioneer the concept of providing marketplace solutions for social problems, which include health, education, nutrition, water, microfinance, persecution, leadership and sex trafficking. He also actively promotes good governance among not-for-profit organizations.</p>
                        <p>He is the founder of Maranatha Trust, Opportunity International Australia and 15 international movements including Wholistic Transformation Resource Center Foundation Inc. (WTRC) in the Philippines. He serves as a consultant to multinational firms and has a team of dedicated colleagues in Asia who implements and monitors development programs.</p>
                        <p>Mr. Bussau is renowned for his innovative and creative approach to post-disaster rehabilitation, contending that wealth creation and the power of market forces will accelerate poverty alleviation and nation-building. He challenges the old development paradigms and encourages fresh, exciting, audacious and bold out-of-the-box entrepreneurial ideas to liberate the poor. He wants to ignite the creative spark in people to release the amazing potential in each individual to live more dynamic, fulfilling and purpose-driven lives.</p>
                        <p>His inventive mind and passionate heart brought him a number of recognition through the years, namely: Australia's 10 Most Creative Minds (2000), Order of Australia Medal (2001), Ernst & Young Social Entrepreneur of the Year Award (2003), The First Social Enterprise to be inducted into the World Entrepreneur of the Year Academy in Monte Carlo, Monaco (2003), Australian of the Year Finalist (2005), Special Humanitarian Award in Singapore (2005), Hilton Distinguished Entrepreneur of the Year Award in USA (2005), Australian Council for International Development Sir Ron Wilson Human Rights Award (2006), Beta Gamma Sigma Medallion for Entrepreneurship in USA (2007), Senior Australian of the Year Award (2008) and Asia CEO Non-Profit Leadership Team of the Year Finalist in the Philippines (2010).</p>
                    </div>
                </div>
            </div>

            <!-- Atty. Lamberto L. Meer -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Atty. Lamberto L. Meer">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Atty. Lamberto L. Meer</p>
                    <p class="leader-position">Chairman</p>
                    <p class="leader-quote">"We are called by God in TSPI for a purpose because God loves us. He called us to serve others and He will never forsake us. Long live TSPI! Praise be to God!"</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Chairman <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Dr. Abraham F. Pascual">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Dr. Abraham F. Pascual</p>
                    <p class="leader-position">Vice Chairman</p>
                    <p class="leader-quote">"Surrender your lives to God and you will live with joy and peace. Have faith in God at all times. Here at TSPI, we have every opportunity to follow this command to "love one another", through our work of helping especially those who are in need."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Vice Chairman <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Dr. Pascual, Vice Chairman of the Board of Trustees, joined the TSPI Board of Trustees in July 2007. He is the Chair of the BOT Governance Committee.</p>
                        <p>He is a multi-awarded entrepreneur. He was a recipient of the Golden Shell Rising Award from the Department of Trade and Industry (DTI) in 1997, Philippine Marketing Association's Agora Awardee for Outstanding Achievement in Entrepreneurship (Large Scale) in 2001, Go Negosyo's Most Inspiring Bulakeño Entrepreneur in 2008 and PLDT and Go Negosyo's MVP Bossing Awardee in 2013.</p>
                        <p>Dr. Pascual's entrepreneurial expertise made him a backbone to various entities. He is the Chairman of the Board of Directors of Pascual Laboratories, Inc. (PascualLab). He also sits as Member of the Board of Directors to four other companies, namely, L & I Development Corp., Agape Development & Research Corporation, Halang East Corporation, and Octten Holdings Inc.</p>
                        <p>Dr. Pascual completed a Ph.D. in Pharmaceutical Chemistry.</p>
                    </div>
                </div>
            </div>

            <!-- Rene E. Cristobal -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Rene E. Cristobal">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Rene E. Cristobal</p>
                    <p class="leader-position">President</p>
                    <p class="leader-quote">"Let Jesus be the One we love. He must be the One we serve. To give people, to help them and to serve them, in order to know God... that is our purpose in TSPI."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About President <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Ricardo G. Lazatin">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Ricardo G. Lazatin</p>
                    <p class="leader-position">Treasurer</p>
                    <p class="leader-quote">"How can we serve our Lord? By serving those whom he loves, his people, especially the poor. When we do our work excellently, we are giving thanks to our Lord God who entrusted to us the talent and resources to accomplish our mission. Because the work we do is a blessing from our Lord."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Treasurer <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Atty. Cornelio C. Gison">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Atty. Cornelio C. Gison</p>
                    <p class="leader-position">Corporate Secretary</p>
                    <p class="leader-quote">"When I started in TSPI, I didn't have a clear idea of my role. If the call at the beginning is not clear, we pray and the Holy Spirit can make us see clearly what is that call for service."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Corporate Secretary <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Juanita D. Amatong">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Juanita D. Amatong</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"We in TSPI are not just giving material things, we are also propagating Christian values."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Jose D. Fider">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Jose D. Fider</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"God is telling us to become more loving in all that we do. He wants to bless you with the life that is full. Wait on the Lord. Be faithful to Him. And always trust in Him."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Alberto M. Malvar">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Alberto M. Malvar</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"When we pray, let us ask God what He wants us to do and tell Him "Lord, I will yield to whatever You want."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Luz A. Planas">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Luz A. Planas</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"When you work for an institution like TSPI, it is nothing about you. It is about working for an institution in reaching out to more clients so they can have better life and eventually enjoy fullness of life."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Ms. Planas joined TSPI Board of Trustees in October 2000. She is the Chair of BOT Audit and Compliance Committee. She is the current Chairperson of the Board of Trustees of TSPI Mutual Benefit Association, Inc. (TSPI MBAI).</p>
                        <p>Ms. Planas is the Chairperson of VA Alvarez Realty Corp., where she formerly served as the Treasurer (1995- 2006). She is currently a Board Member to the BF West Homeowners Association.</p>
                        <p>She was previously with the Bank of the Philippine Islands (BPI). She became President and CEO of BPI Forex Corporation from 1999 to 2004. She is actively involved in various civic and religious organizations as a Board Member. Her noteworthy contributions in community development include the renovation of the Resurrection of our Lord Parish Church in BF Parañaque and the greening of BF West Executive Village also in Parañaque City. She also partnered with a local community at her hometown in Roxas City to build the new Pueblo de Panay. She is a passionate professional dancer joining competitions locally and abroad.</p>
                        <p>Ms. Planas obtained degrees are Bachelor of Arts (A.B.), Major in Humanity and Bachelor of Business, Major in Accounting.</p>
                    </div>
                </div>
            </div>

            <!-- Florencia G. Tarriela -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Florencia G. Tarriela">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Florencia G. Tarriela</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"Know Jesus Christ, know Him as our Lord and Savior. Jesus is all we need. Because He is the answer to all our needs."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Ms. Tarriela's service with TSPI as Member of the Board of Trustees started in October 2003. She is the Chair of the BOT Investment Committee and the Vice Chair of the BOT Governance Committee. Presently, she is the Treasurer, Board of Trustees, of Tulay sa Pag-unlad Mutual Benefit Association, Inc. (TSPI MBAI).</p>
                        <p>She holds the distinction for being the first woman chairperson of the Philippine National Bank (PNB) and the first Filipina Vice President of Citibank N.A. She was a former Undersecretary of the Department of Finance and was an Alternate Monetary Board Member of Bangko Sentral ng Pilipinas (BSP), Land Bank of the Philippines (LBP) and the Philippine Deposit Insurance Corporation (PDIC). She also held several key positions as President of Bank Administration of the Philippines, Independent Director of PNB Life Insurance, Inc. and Director of Bankers Association of the Philippines.</p>
                        <p>Her other current undertakings include: Adviser of the Philippine National Bank (PNB); Independent Director of LT Group, Inc.; Director of PNB Capital and Investment Corporation; Independent Director of PNB International Investments Corporation; Columnist of "Business Options" of the Manila Bulletin and "Financial Executives Institute of the Philippines (FINEX) Folio" of Business World; Director/Vice President of Tarriela Management Company; Director/Vice President/Assistant Treasurer of Gozon Development Corporation; Life Sustaining Member of Bankers Institute of the Philippines and FINEX; Fellow at the Institute of Corporate Directors (ICD), Trustee of FINEX; President of Flor's Garden and Natural Haven's Inc., and Director of Makati Garden Club.</p>
                        <p>As a banker, entrepreneur and an environmentalist, she has been recognized as the Go Negosyo 2018 Woman Intrapreneur Awardee, Most Outstanding Citibank Philippines Alumni Awardee for Community Involvement (2014), and Distinguished Lady Banker awarded by the Bank Administration Institute of the Philippines. She is also a co-author of several inspirational and gardening books.</p>
                        <p>Ms. Tarriela obtained her Bachelor of Science in Business Administration, major in Economics from the University of the Philippines and a Master's in Economics from the University of California, Los Angeles.</p>
                    </div>
                </div>
            </div>

            <!-- Terence R. Winters -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Terence R. Winters">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Terence R. Winters</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"Our dream is that by helping a parent to build a small business, their children will grow up with a future that's full of hope."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Mr. Winters serves as the Chairman and Non-Executive Director of several Australian private companies and charities. He is currently Chairman of Converge International Pty Ltd. He also serves as a Director of Many Rivers Microfinance Limited, and was immediate past Chairman or a Director of Seeing Machines Limited, TasmaNet Pty Ltd, Intelledox Pty Ltd and Redflex Holdings Limited. After working for Motorola for 10 years, he founded Link Telecommunications Pty Ltd. in 1983 and was CEO and/or Chairman of Link at different times until 1999 when he sold his interest in the company. He led the creation of Optus Communications Pty Ltd from 1989-1992 and remained on the Optus board until 1995. Mr. Winters spent over 17 years on various boards within the Opportunity International Network before ending his term as global Chairman in 2010.</p>
                    </div>
                </div>
            </div>

            <!-- Richard Dagelet, Jr. -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Richard Dagelet, Jr.">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Richard Dagelet, Jr.</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"What sets TSPI apart is not just the work we do, but the profound ripple effect it generates in the communities we serve. Rooted in Christian values, we instill empowerment, and foster an environment defined by prosperity, dignity, and sustainable progress."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Mr. Dagelet is a new member of the TSPI Board of Trustees, joining in September 2022. He is also a member of the Advisory Council of TSPI Mutual Benefit Association, Inc. (MBAI).</p>
                        <p>He is the founder, Chairman and CEO of eScience, an IT company providing mobile solutions to over 70 companies dealing with health care, consumer goods and logistics. He also founded several companies that launched pioneering and innovative services for mobile customers, among them Smart Solutions, E-Store Exchange, and Secure Payment Networks. He has been in the IT industry since 1999. He founded the first e-commerce service in the Philippines, allowing the purchase of goods and services via mobile phone and the internet. He also created the patent for location-based services for traffic monitoring in 2005. In 1997-1998, he was CEO of Danka Philippines, the leading vendor of Kodak Digital office products. He worked in sales, marketing, and general management at Kodak Philippines from 1987 to 1996.</p>
                        <p>He is a coordinator at Ang Ligaya ng Panginoon Community (ALNP), a Resource Speaker in the Marriage and Parenting course of ANLP, Director of Sandiwaan Learning Center in Tondo, Manila, and Founder/Director of Internet of Things.</p>
                        <p>Mr. Dagelet is a graduate of Bachelor of Science in Industrial Management Engineering at the De La Salle University.</p>
                    </div>
                </div>
            </div>

            <!-- Carlos Rheal B. Cervantes -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Carlos Rheal B. Cervantes">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Carlos Rheal B. Cervantes</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"The road to financial freedom usually feels long and winding, unless you have someone working to keep you in their wings to nurture and form you until you can go head straight."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Mr. Cervantes joined the TSPI Board of Trustees in 2022. He is an investment banker who specializes in the securitization of receivables, fund raising and financial management, with over 29 years of experience in finance and banking. He is a trust professional, a financial management instructor and a former certified SEC representative for both fixed-income securities and investment company products. He has extensive experience in financial and credit arrangement advisory, marketing bank products, branch management and financial analysis.</p>
                        <p>He is Treasurer and Chief Financial Officer/Chief Operating Officer of PowerSource Group Holdings Corp. and its subsidiaries; President and Chairman of Accessus Lending Company, Inc.; Executive Vice President and COO of Argosy Finance Corp. and Home Funding (SPC), Inc. Argosy has invested in, advised on, and raised significant funds in various investment transactions in the Philippines and internationally since 1999. Mr. Cervantes previously served as First Vice President of Philippine Veterans Bank; Senior Manager, Land Bank of the Philippines; and Branch OIC of Mindanao Development Bank.</p>
                        <p>Mr. Cervantes is a graduate of B.S. Agriculture Production and Management from the University of the Philippines, Los Baños (UPLB) and holder of a Master's in Management degree from the Asian Institute of Management.</p>
                    </div>
                </div>
            </div>

            <!-- Raymond Daniel H. Cruz Jr. -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Raymond Daniel H. Cruz Jr.">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Raymond Daniel H. Cruz Jr.</p>
                    <p class="leader-position">Trustee</p>
                    <p class="leader-quote">"In any circumstance that we are in, always consult God. This is called "Discernment". God is delighted when you asked Him. When you ask, learn to listen. Wait for a while for Him to speak to you or to the people you are with. When we listen, we become confident that He is leading us and we learn to accept God's message even if it sometimes hurts."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Mr. Cruz joined the TSPI Board of Trustees in 2022. He is Director of WeGen Laudato Si, an energy company that helps Catholic dioceses transition to renewable energy, in response to the challenge of Pope Francis in his "Laudato Si" encyclical.</p>
                        <p>He is national president of the Catholic Bishops Conference of the Philippines-Episcopal Commission of the Laity-Sangguniang Laiko ng Pilipinas. He is also Executive Director of Philippine Catholic Charismatic Renewal Services (PhilCCRS); Director of Leadership Development and Mission at Ligaya ng Panginoon Community; and Catholic Coordinator of Purpose Driven Ministries Southeast Asia.</p>
                        <p>A former theology teacher at University of Sto. Tomas High School, Mr. Cruz previously served as Executive Director of the Pilipino Movement for Transformational Leadership and the Ligaya ng Panginoon Foundation, Inc; Youth Coordinator of PhilCCRS National Service Committee; and Director for youth and family life at Ligaya ng Panginoon Community.</p>
                    </div>
                </div>
            </div>

            <!-- Anna Isabel C. Sobrepeña -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Anna Isabel C. Sobrepeña">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Anna Isabel C. Sobrepeña</p>
                    <p class="leader-position">Trustee</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Trustee <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Ms. Sobrepeña joined the TSPI Board of Trustees in 2022. She is an award-winning writer and editor, and seasoned public speaker. She was named 2019 Most Influential Filipina Thought Leader and Innovator by the Foundation for Filipina Women's Network (FWN) in Paris; and 2018 Asia Leaders Awards Editor of the Year.</p>
                        <p>She was editor in chief of Lifestyle Asia from 2007 to 2018, during which she collaborated with various sectors, companies, and groups such as Philippine Business for Education, Caritas Manila, Make-A-Wish Foundation, to promote meaningful luxury through shared advocacies (e.g. tree-planting, scholarships, teacher training and children's welfare).</p>
                        <p>She previously edited True North, a Christian lifestyle magazine, nominated for best community magazine in the Catholic Mass Media Awards. Books she edited include "Wives are Lovers, Too" and the "Ang Ligaya ng Panginoon 40th Anniversary Commemorative Book". She published eight coffee table books of significant lives, Philippine homes and tablescapes which presented the good in our country and in our people.</p>
                        <p>Ms. Sobrepeña has given talks on personality development, social graces, and the Philippines as a tourist destination. She is also a well sought speaker on Christian seminars such as Christian living and improving communication in marriages.</p>
                        <p>Ms. Sobrepeña is a graduate of Bachelor of Arts, English at the University of the Philippines. She earned units in Masters of Science in Literature at the Ateneo de Manila University and Certificate of Completion for Managing the Arts Program from the Asian Institute of Management.</p>
                    </div>
                </div>
            </div>

            <!-- Alice Z. Cordero -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Alice Z. Cordero">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Alice Z. Cordero</p>
                    <p class="leader-position">Ex-Officio; Executive Director</p>
                    <p class="leader-quote">"What has been consistent in TSPI is our commitment of bringing God to everyone. The transformation framework that we have adopted in TSPI was proven overtime. As long as you are God-centered, you believe in what God has given you and you use the resources the right way, then you will succeed whatever comes to you."</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Executive Director <span class="toggle-icon">&#9660;</span></button>
                    <div class="leader-bio-content">
                        <p>Ms. Cordero joined TSPI in May 2019. She serves concurrent positions as the Executive Director of TSPI and as President and Chief Executive Officer of TSPI Mutual Benefit Association, Inc. (TSPI MBAI) – the microinsurance arm of TSPI.</p>
                        <p>Ms. Cordero gained her management and leadership expertise through her solid career in banking. She was Philippine National Bank's First Senior Vice President (FSVP) until April 2019 and was appointed as the Chief Compliance Officer (CCO) of the Bank on June 2010 with oversight of the Parent Bank, including all the subsidiaries, affiliate and foreign branches. She also served as the Corporate Governance Executive of the Bank. From 2008-2019, she served as Director and presently as Adviser of the Association of Bank Compliance Officers (ABCOMP). She obtained her Bachelor of Science in Business Economics from the University of the Philippines, and earned units in Masters in Business Administration from the Ateneo Graduate School of Business.</p>
                        <p>Prior to joining PNB, she was the CCO of Allied Banking Corporation (ABC) from 2007 to 2010. She worked with Citibank N.A. – Manila Branch for almost 20 years, from 1988 to 2007, and held various senior positions in the Consumer Banking Group, including Compliance and Control Director from 1999 to 2005 and concurrent Regional Compliance and Control Director for the Philippines and Guam in 2004. Her 40 years of banking experience include working for Philippine National Bank (PNB) from 2010 to 2019, ABC (1979-1983; 2007-2010, First National Bank of Chicago-Manila Branch (1983-1986), Far East Bank and Trust Company (1986-1988) and Citibank N.A.-Manila Branch (1988-2007), where she held department head positions in Credit Policy, Credit and Research Management, Financial Control, Corporate Regulatory Reporting, Asset Strategy, Business Deve lopment, Risk Management, and Compliance.</p>
                    </div>
                </div>
            </div>

        </div> <!-- .leader-category (Board of Trustees) -->

        <div class="leader-category">
            <h3>Senior Management Team</h3>

            <!-- Atty. Leonarda D. Banasen -->
            <div class="leader-card">
                <div class="leader-card-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Atty. Leonarda D. Banasen">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Atty. Leonarda D. Banasen</p>
                    <p class="leader-position">Head, Legal Group</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Head, Legal Group <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Ms. Lorna M. Asuncion">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Ms. Lorna M. Asuncion</p>
                    <p class="leader-position">Head, Treasury Group</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Head, Treasury Group <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Mr. Rexchell A. Querido">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Mr. Rexchell A. Querido</p>
                    <p class="leader-position">Head, Operations Group</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Head, Operations Group <span class="toggle-icon">&#9660;</span></button>
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/placeholder_leader.png" alt="Ms. Jennifer C. Abastillas">
                </div>
                <div class="leader-card-info">
                    <p class="leader-name">Ms. Jennifer C. Abastillas</p>
                    <p class="leader-position">Head, Alliance and Program Management Group</p>
                    <button class="leader-bio-toggle" aria-expanded="false">About Head, Alliance and Program Management Group <span class="toggle-icon">&#9660;</span></button>
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

        </div> <!-- .leader-category (Senior Management Team) -->
    </section>


    <!-- About TSPI - MBAI Section will be added here -->
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


</div> <!-- .about-us-container -->

<script>
// JavaScript for leader bio toggles will be added here later
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.leader-bio-toggle');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const content = this.nextElementSibling;
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            this.setAttribute('aria-expanded', !isExpanded);
            this.classList.toggle('active');
            content.classList.toggle('show');
            
            // Update icon if needed (simple text change or class for SVG)
            const icon = this.querySelector('.toggle-icon');
            if (icon) {
                icon.innerHTML = !isExpanded ? '&#9650;' : '&#9660;'; // Up/Down arrow
            }
        });
    });
});
</script>

<?php
include 'includes/footer.php';
?> 