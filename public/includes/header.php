<section class="page-title">
    <div class="container">
        <h1><?= $pageTitle ?? SITE_WELCOME_TITLE ?></h1>
        <p class="subtitle"><?= $pageDescription ?? SITE_WELCOME_DESCRIPTION ?></p>
    </div>
</section>

<?php if ($menuEnabled): ?>
    <nav class="main-menu">
        <?php foreach ((new Navigation())->renderMenu() as $item): ?>
            <a href="/<?= $item['path'] ?>"><?= $item['title'] ?></a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>
