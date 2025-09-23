<?php
require_once 'core/init.php';
checkMaintenanceMode();

global $settings;
$pageTitle = 'Privacy Policy - ' . $settings['site_title'];
?>

<?php include 'components/layout/header.php'; ?>

<main class="privacy-policy-page">
    <div class="container">
        <div class="content-wrapper">
            <header class="page-header">
                <h1>Privacy Policy</h1>
                <p class="last-updated">Last updated: <?= date('F j, Y') ?></p>
            </header>

            <div class="privacy-content">
                <section class="privacy-section">
                    <h2>1. Information We Collect</h2>
                    <p>We collect information you provide directly to us, such as when you create an account, participate in our programs, or contact us for support.</p>

                    <h3>Personal Information</h3>
                    <ul>
                        <li>Name and contact information</li>
                        <li>Account credentials</li>
                        <li>Profile information</li>
                        <li>Project submissions and achievements</li>
                    </ul>

                    <h3>Automatically Collected Information</h3>
                    <ul>
                        <li>Log data and usage information</li>
                        <li>Device information</li>
                        <li>Cookies and similar technologies</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>2. How We Use Your Information</h2>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide and maintain our services</li>
                        <li>Process project submissions and track achievements</li>
                        <li>Communicate with you about your account and our services</li>
                        <li>Improve and personalize your experience</li>
                        <li>Ensure the security of our platform</li>
                        <li>Comply with legal obligations</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>3. Information Sharing</h2>
                    <p>We do not sell, trade, or otherwise transfer your personal information to outside parties except as described in this policy:</p>
                    <ul>
                        <li><strong>Service Providers:</strong> We may share information with trusted third parties who assist us in operating our website and services</li>
                        <li><strong>Legal Requirements:</strong> We may disclose information when required by law or to protect our rights</li>
                        <li><strong>Public Information:</strong> Some profile information and project achievements may be publicly visible</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>4. Cookies and Tracking</h2>
                    <p>We use cookies and similar technologies to enhance your experience on our website. You can manage your cookie preferences through our cookie consent banner.</p>

                    <h3>Types of Cookies We Use</h3>
                    <ul>
                        <li><strong>Essential Cookies:</strong> Necessary for the website to function properly</li>
                        <li><strong>Analytics Cookies:</strong> Help us understand how visitors use our website</li>
                        <li><strong>Functional Cookies:</strong> Remember your preferences and settings</li>
                        <li><strong>Marketing Cookies:</strong> Used for advertising and marketing purposes</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>5. Data Security</h2>
                    <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no internet transmission is completely secure.</p>
                </section>

                <section class="privacy-section">
                    <h2>6. Your Rights</h2>
                    <p>Depending on your location, you may have certain rights regarding your personal information:</p>
                    <ul>
                        <li>Access to your personal information</li>
                        <li>Correction of inaccurate information</li>
                        <li>Deletion of your information</li>
                        <li>Portability of your data</li>
                        <li>Objection to processing</li>
                        <li>Withdrawal of consent</li>
                    </ul>
                </section>

                <section class="privacy-section">
                    <h2>7. Data Retention</h2>
                    <p>We retain your personal information for as long as necessary to provide our services and comply with legal obligations. Account information is kept for the duration of your membership and may be retained for a reasonable period after account closure.</p>
                </section>

                <section class="privacy-section">
                    <h2>8. International Transfers</h2>
                    <p>Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your information in accordance with applicable data protection laws.</p>
                </section>

                <section class="privacy-section">
                    <h2>9. Children's Privacy</h2>
                    <p>Our services are not intended for children under 13. We do not knowingly collect personal information from children under 13. If you believe we have collected such information, please contact us immediately.</p>
                </section>

                <section class="privacy-section">
                    <h2>10. Changes to This Policy</h2>
                    <p>We may update this privacy policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last updated" date.</p>
                </section>

                <section class="privacy-section">
                    <h2>11. Contact Us</h2>
                    <p>If you have any questions about this privacy policy or our privacy practices, please contact us:</p>
                    <ul>
                        <li>Email: privacy@<?= htmlspecialchars(str_replace(['http://', 'https://'], '', $settings['site_url'])) ?></li>
                        <li>Through our <a href="<?= $settings['site_url'] ?>/contact.php">contact form</a></li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</main>

<style>
    .privacy-policy-page {
        min-height: 100vh;
        padding: 80px 0 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .content-wrapper {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .page-header {
        background: #1a1a1a;
        color: white;
        padding: 40px;
        text-align: center;
    }

    .page-header h1 {
        font-size: 2.5em;
        margin: 0 0 10px 0;
        font-weight: 600;
    }

    .last-updated {
        margin: 0;
        opacity: 0.8;
        font-size: 0.9em;
    }

    .privacy-content {
        padding: 40px;
    }

    .privacy-section {
        margin-bottom: 40px;
    }

    .privacy-section:last-child {
        margin-bottom: 0;
    }

    .privacy-section h2 {
        color: #1a1a1a;
        font-size: 1.5em;
        margin: 0 0 16px 0;
        font-weight: 600;
        border-bottom: 2px solid #ef4444;
        padding-bottom: 8px;
    }

    .privacy-section h3 {
        color: #333;
        font-size: 1.2em;
        margin: 24px 0 12px 0;
        font-weight: 600;
    }

    .privacy-section p {
        line-height: 1.6;
        margin: 0 0 16px 0;
        color: #555;
    }

    .privacy-section ul {
        margin: 16px 0;
        padding-left: 24px;
    }

    .privacy-section li {
        margin: 8px 0;
        line-height: 1.6;
        color: #555;
    }

    .privacy-section a {
        color: #ef4444;
        text-decoration: none;
    }

    .privacy-section a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .privacy-policy-page {
            padding: 60px 0 20px;
        }

        .container {
            padding: 0 15px;
        }

        .page-header {
            padding: 30px 20px;
        }

        .page-header h1 {
            font-size: 2em;
        }

        .privacy-content {
            padding: 30px 20px;
        }

        .privacy-section h2 {
            font-size: 1.3em;
        }
    }
</style>

<?php include 'components/layout/footer.php'; ?>