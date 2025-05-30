<?php
require_once '../../core/init.php';
require_once __DIR__ . '/../../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = $success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = "Please enter your email address.";
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure token and expiry (1 hour)
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            // Store token in DB
            $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
                ->execute([$user['id'], $token, $expires]);

            // Prepare reset link
            $resetLink = BASE_URL . "dashboard/reset.php?token=$token";

            // Load SMTP settings from DB
            $smtp = [];
            $settings = $db->query("SELECT * FROM settings")->fetchAll();
            foreach ($settings as $row) $smtp[$row['name']] = $row['value'];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['smtp_user'];
                $mail->Password = $smtp['smtp_pass'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtp['smtp_port'];

                $mail->setFrom($smtp['smtp_from'], $smtp['smtp_from_name']);
                $mail->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your PULSE Password';
                $mail->Body = "Hello,<br><br>
                    We received a request to reset your password. 
                    <b><a href=\"$resetLink\">Click here to reset your password</a></b>.<br><br>
                    This link will expire in 1 hour. If you did not request this, you can ignore this email.<br><br>
                    PULSE Team";

                $mail->send();
                $success = "A password reset link has been sent to your email address.";
            } catch (Exception $e) {
                $error = "Failed to send email. Please contact support.";
            }
        } else {
            $error = "No user found with that email address.";
        }
    }
}

include '../components/layout/header.php';
?>
<head>
    <link rel="stylesheet" href="../css/main.css">
</head>
<main>
    <section class="forgot-password-section">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <div class="form-errors"><div class="error"><?= htmlspecialchars($error) ?></div></div>
        <?php elseif ($success): ?>
            <div class="form-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Your Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit" class="cta-button">Send Reset Link</button>
        </form>
    </section>
</main>
<?php include '../components/layout/footer.php'; ?>
