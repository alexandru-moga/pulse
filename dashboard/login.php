<?php
require_once '../core/init.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

include '../components/layout/header.php';
include '../components/effects/mouse.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
    <?php include '../components/effects/grid.php'; ?>
</head>

<main>
    <section class="contact-form-section">
        <h2>Login to PULSE</h2>
        <?php if ($error): ?>
            <div class="form-errors">
                <div class="error"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>
        <form class="login-form" method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="cta-button">Login</button>
        </form>
    </section>
</main>

<?php include '../components/layout/footer.php'; ?>
