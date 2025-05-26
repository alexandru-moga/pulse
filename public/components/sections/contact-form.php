<?php

$content = [];
$error = null;
try {
    $content = json_decode($block['content'] ?? '', true);
    if (
        !is_array($content) ||
        empty($content['fields']) ||
        !is_array($content['fields'])
    ) {
        $error = "Contact form configuration is missing or invalid. Please contact the site administrator.";
    }
} catch (Exception $e) {
    $error = "Contact form configuration error: " . htmlspecialchars($e->getMessage());
}
?>

<section class="contact-form-section">
    <?php if ($error): ?>
        <div class="form-errors">
            <p class="error"><?= $error ?></p>
        </div>
    <?php else: ?>
        <?php if (!empty($content['title'])): ?>
            <h2 class="contact-title"><?= htmlspecialchars($content['title']) ?></h2>
        <?php endif; ?>
        <?php if (!empty($content['subtitle'])): ?>
            <p class="contact-subtitle"><?= htmlspecialchars($content['subtitle']) ?></p>
        <?php endif; ?>
        <?php if (!empty($content['description'])): ?>
            <p class="contact-description"><?= htmlspecialchars($content['description']) ?></p>
        <?php endif; ?>

        <?php if(isset($_SESSION['form_errors'])): ?>
            <div class="form-errors">
                <?php foreach($_SESSION['form_errors'] as $field => $errorMsg): ?>
                    <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['form_errors']); ?>
            </div>
        <?php endif; ?>

        <form class="contact-form" method="POST" action="<?= BASE_URL ?>contact.php">
            <?php foreach ($content['fields'] as $field): ?>
                <div class="form-group">
                    <label for="<?= htmlspecialchars($field['name']) ?>">
                        <?= htmlspecialchars($field['label']) ?>
                        <?php if (!empty($field['required'])): ?>
                            <span class="required">*</span>
                        <?php endif; ?>
                    </label>
                    <?php if (($field['type'] ?? 'text') === 'textarea'): ?>
                        <textarea
                            id="<?= htmlspecialchars($field['name']) ?>"
                            name="<?= htmlspecialchars($field['name']) ?>"
                            placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                            rows="6"
                            <?= !empty($field['required']) ? 'required' : '' ?>
                        ><?= htmlspecialchars($_SESSION['form_data'][$field['name']] ?? '') ?></textarea>
                    <?php else: ?>
                        <input
                            type="<?= ($field['name'] === 'email') ? 'email' : 'text' ?>"
                            id="<?= htmlspecialchars($field['name']) ?>"
                            name="<?= htmlspecialchars($field['name']) ?>"
                            placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                            value="<?= htmlspecialchars($_SESSION['form_data'][$field['name']] ?? '') ?>"
                            <?= !empty($field['required']) ? 'required' : '' ?>
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="cta-button">
                <?= htmlspecialchars($content['button_text'] ?? 'Send Message') ?>
            </button>
        </form>

        <?php if(isset($_SESSION['form_data'])): ?>
            <?php unset($_SESSION['form_data']); ?>
        <?php endif; ?>
    <?php endif; ?>
</section>