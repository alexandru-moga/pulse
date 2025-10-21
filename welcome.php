<?php
require_once 'core/init.php';
checkMaintenanceMode();

// Check if user came from Discord
$fromDiscord = isset($_GET['from']) && $_GET['from'] === 'discord';

// If user is already logged in, redirect to Discord linking immediately
if (isLoggedIn()) {
    header('Location: /auth/discord/?action=link&from=welcome');
    exit();
}

// If user is not logged in, set redirect and go to login
$_SESSION['info'] = 'Please login to your Phoenix Club account to link your Discord.';
$_SESSION['redirect_after_login'] = '/auth/discord/?action=link&from=welcome';
header('Location: /dashboard/login.php');
exit();
?>

<?php include 'components/layout/header.php'; ?>

<head>
    <title>Welcome to Phoenix Club</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        .welcome-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            text-align: center;
        }
        
        .welcome-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .welcome-step {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
        }
        
        .step-number {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
        }
        
        .cta-buttons {
            margin: 2rem 0;
        }
        
        .cta-button {
            display: inline-block;
            padding: 1rem 2rem;
            margin: 0.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .cta-button:hover {
            background: #5a67d8;
        }
        
        .cta-button.secondary {
            background: #4a5568;
        }
        
        .cta-button.secondary:hover {
            background: #2d3748;
        }
    </style>
</head>

<main>
    <div class="welcome-container">
        <div class="welcome-header">
            <h1>🎉 Welcome to Phoenix Club!</h1>
            <?php if ($fromDiscord): ?>
                <p>Hi there! We're excited to have you join our Discord community.</p>
            <?php else: ?>
                <p>Join our amazing community of innovators, developers, and creators!</p>
            <?php endif; ?>
        </div>
        
        <div class="welcome-steps">
            <div class="welcome-step">
                <div class="step-number">1</div>
                <div>
                    <h3>Create Your Account</h3>
                    <p>Sign up for your Phoenix Club account to access all our features and projects.</p>
                </div>
            </div>
            
            <div class="welcome-step">
                <div class="step-number">2</div>
                <div>
                    <h3>Link Your Discord</h3>
                    <p>Connect your Discord account to get access to project roles and community features.</p>
                </div>
            </div>
            
            <div class="welcome-step">
                <div class="step-number">3</div>
                <div>
                    <h3>Get Your Roles</h3>
                    <p>Once linked, your roles will be automatically synced based on your project assignments!</p>
                </div>
            </div>
        </div>
        
        <div class="cta-buttons">
            <?php if (isLoggedIn()): ?>
                <a href="/auth/discord/?action=link&from=welcome" class="cta-button">
                    🔗 Link Discord Account
                </a>
                <a href="/dashboard/" class="cta-button secondary">
                    📊 Go to Dashboard
                </a>
            <?php else: ?>
                <a href="/login.php" class="cta-button">
                    🚪 Login to Existing Account
                </a>
                <a href="/apply.php" class="cta-button secondary">
                    📝 Create New Account
                </a>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 3rem; padding: 1.5rem; background: #f7fafc; border-radius: 10px;">
            <h3>Need Help?</h3>
            <p>If you have any questions or need assistance, feel free to:</p>
            <ul style="text-align: left; max-width: 400px; margin: 0 auto;">
                <li>Ask in our Discord community</li>
                <li>Contact our team through the website</li>
                <li>Check out our documentation and guides</li>
            </ul>
        </div>
    </div>
</main>

<?php include 'components/layout/footer.php'; ?>
