<header class="header">
    <nav class="nav-container">
        <a href="/" class="logo"><?= SITE_TITLE ?></a>
        <div class="nav-links">
            <a href="/members" class="<?= basename($_SERVER['PHP_SELF']) === 'members.php' ? 'active' : '' ?>">Members</a>
            <a href="/apply" class="<?= basename($_SERVER['PHP_SELF']) === 'apply.php' ? 'active' : '' ?>">Apply</a>
            <a href="/contact" class="<?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : '' ?>">Contact</a>
        </div>
    </nav>
</header>
