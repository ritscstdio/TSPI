<?php
$page_title = "Privacy Policy";
$body_class = "privacy-policy-page";
require_once 'includes/config.php';
include 'includes/header.php';
?>

<main class="container" style="padding-top: 40px; padding-bottom: 40px;">
    <h1>Privacy Policy</h1>
    <p>Effective Date: <?php echo date('F j, Y'); ?></p>

    <h2>1. Introduction</h2>
    <p>Welcome to <?php echo SITE_NAME; ?>. We are committed to protecting your personal information and your right to privacy. If you have any questions or concerns about this privacy notice, or our practices with regards to your personal information, please contact us.</p>

    <h2>2. Information We Collect</h2>
    <p>We collect personal information that you voluntarily provide to us when you register on the website, express an interest in obtaining information about us or our products and services, when you participate in activities on the website, or otherwise when you contact us.</p>
    <p>The personal information that we collect depends on the context of your interactions with us and the website, the choices you make, and the products and features you use. The personal information we collect may include the following:</p>
    <ul>
        <li>Names</li>
        <li>Email addresses</li>
        <li>Usernames</li>
        <li>Passwords</li>
        <li>Contact preferences</li>
    </ul>
    <p>We also automatically collect certain information when you visit, use, or navigate the website. This information does not reveal your specific identity (like your name or contact information) but may include device and usage information, such as your IP address, browser and device characteristics, operating system, language preferences, referring URLs, device name, country, location, information about how and when you use our website, and other technical information.</p>

    <h2>3. How We Use Your Information</h2>
    <p>We use personal information collected via our website for a variety of business purposes described below. We process your personal information for these purposes in reliance on our legitimate business interests, in order to enter into or perform a contract with you, with your consent, and/or for compliance with our legal obligations.</p>
    <ul>
        <li>To facilitate account creation and logon process.</li>
        <li>To post testimonials.</li>
        <li>Request feedback.</li>
        <li>To enable user-to-user communications.</li>
        <li>To manage user accounts.</li>
        <li>To send administrative information to you.</li>
        <li>To protect our Services.</li>
        <li>To enforce our terms, conditions and policies for business purposes, to comply with legal and regulatory requirements or in connection with our contract.</li>
        <li>To respond to legal requests and prevent harm.</li>
    </ul>

    <h2>4. Will Your Information Be Shared With Anyone?</h2>
    <p>We only share information with your consent, to comply with laws, to provide you with services, to protect your rights, or to fulfill business obligations.</p>

    <h2>5. Do We Use Cookies and Other Tracking Technologies?</h2>
    <p>We may use cookies and similar tracking technologies (like web beacons and pixels) to access or store information. Specific information about how we use such technologies and how you can refuse certain cookies is set out in our Cookie Policy section below (or will be detailed here).</p>
    <p>This website uses cookies to enhance user experience, to facilitate login, and to analyze site traffic. By clicking "Accept", you consent to the use of all cookies. You can manage your cookie preferences or learn more by visiting our Privacy Policy.</p>

    <h2>6. How Long Do We Keep Your Information?</h2>
    <p>We will only keep your personal information for as long as it is necessary for the purposes set out in this privacy notice, unless a longer retention period is required or permitted by law (such as tax, accounting or other legal requirements).</p>

    <h2>7. How Do We Keep Your Information Safe?</h2>
    <p>We aim to protect your personal information through a system of organizational and technical security measures.</p>

    <h2>8. What Are Your Privacy Rights?</h2>
    <p>In some regions, such as the European Economic Area (EEA) and United Kingdom (UK), you have rights that allow you greater access to and control over your personal information. You may review, change, or terminate your account at any time.</p>

    <h2>9. Controls for Do-Not-Track Features</h2>
    <p>Most web browsers and some mobile operating systems and mobile applications include a Do-Not-Track (“DNT”) feature or setting you can activate to signal your privacy preference not to have data about your online browsing activities monitored and collected. At this stage no uniform technology standard for recognizing and implementing DNT signals has been finalized. As such, we do not currently respond to DNT browser signals or any other mechanism that automatically communicates your choice not to be tracked online.</p>

    <h2>10. Updates to This Notice</h2>
    <p>We may update this privacy notice from time to time. The updated version will be indicated by an updated “Revised” date and the updated version will be effective as soon as it is accessible. We encourage you to review this privacy notice frequently to be informed of how we are protecting your information.</p>

    <h2>11. Contact Us</h2>
    <p>If you have questions or comments about this notice, you may email us at <?php echo ADMIN_EMAIL; ?> or by post to:<br>
    <?php echo SITE_NAME; ?><br>
    [Your Company Address Here]<br>
    [City, Postal Code]<br>
    [Country]
    </p>

</main>

<?php
include 'includes/footer.php';
?> 