<?php
// Content Box Component Template
$boxContent = $content ?? '<p>Your content here</p>';
$backgroundColor = $background_color ?? '#f8f9fa';
$paddingSize = $padding ?? 'medium';

$paddingClass = array(
    'small' => 'p-4',
    'medium' => 'p-6',
    'large' => 'p-8'
)[$paddingSize] ?? 'p-6';
?>

<section class="content-box <?= $paddingClass ?>" style="background-color: <?= htmlspecialchars($backgroundColor) ?>;">
    <div class="container">
        <?= $boxContent ?>
    </div>
</section>