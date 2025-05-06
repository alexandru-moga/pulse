<?php
require_once '../core/init.php'; // Make sure this loads the User class

// Ensure User::getAll() is defined as a static method in the User class
$members = User::getAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Club Members</title>
    <style>
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1em;
        }
        .member-card {
            border: 1px solid #ccc;
            padding: 1em;
            border-radius: 8px;
            box-shadow: 2px 2px 8px #eee;
        }
    </style>
</head>
<body>
    <h1>Club Members</h1>
    <div class="members-grid">
        <?php foreach ($members as $member): ?>
            <div class="member-card">
                <strong><?= htmlspecialchars($member->username) ?></strong><br>
                Joined: <?= date('M Y', strtotime($member->created_at)) ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>