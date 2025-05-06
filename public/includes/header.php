<header class="header">
    <nav class="nav-container">
        <a href="/" class="logo"><?= SITE_TITLE ?></a>
        <div class="nav-links">
            <a href="/club-website/public" class="<?= basename($_SERVER['PHP_SELF']) === 'members.php' ? 'active' : '' ?>">Members</a>
            <a href="/club-website/public" class="<?= basename($_SERVER['PHP_SELF']) === 'apply.php' ? 'active' : '' ?>">Apply</a>
            <a href="/club-website/public" class="<?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : '' ?>">Contact</a>
        </div>
    </nav>
</header>
