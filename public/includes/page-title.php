<section class="page-title">
  <div class="container">
    <h1><?= $pageTitle ?></h1>
    <p class="subtitle"><?= $pageDescription ?></p>
  </div>
</section>

<?php if ($menuEnabled): ?>
<section class="main-navigation">
  <div class="container">
    <?= $navigation->renderMenu() ?>
  </div>
</section>
<?php endif; ?>
