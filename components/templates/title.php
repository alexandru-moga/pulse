<?php
// Title Component Template
$titleText = $text ?? 'Section Title';
$titleLevel = $level ?? 'h2';
$titleAlign = $align ?? 'center';
$titleColor = $color ?? '#1f2937';
$useHeading3Style = $use_heading3_style ?? false;
?>

<?php if ($useHeading3Style): ?>
    <<?= $titleLevel ?> class="heading-3">
        <?= htmlspecialchars($titleText) ?>
    </<?= $titleLevel ?>>
<?php else: ?>
    <section class="section-heading">
        <div class="container">
            <<?= $titleLevel ?> style="text-align: <?= $titleAlign ?>; color: <?= $titleColor ?>; margin: 0.5em 0;">
                <?= htmlspecialchars($titleText) ?>
            </<?= $titleLevel ?>>
        </div>
    </section>
<?php endif; ?>