<?php
global $currentUser;
?>
<nav class="dashboard-sidebar">
  <ul>
    <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Profile</a></li>
    <li><a href="change-password.php" class="<?= basename($_SERVER['PHP_SELF']) === 'change-password.php' ? 'active' : '' ?>">Change Password</a></li>
    <li><a href="projects.php" class="<?= basename($_SERVER['PHP_SELF']) === 'projects.php' ? 'active' : '' ?>">My Projects</a></li>
    <?php if ($currentUser && in_array($currentUser->role, ['Leader', 'Co-leader'])): ?>
      <li><a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">User Management</a></li>
    <li><a href="logout.php" class="<?= basename($_SERVER['PHP_SELF']) === 'logout.php' ? 'active' : '' ?>">Logout</a></li>
      <?php endif; ?>
  </ul>
</nav>
