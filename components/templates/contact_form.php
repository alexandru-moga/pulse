<?php
// Contact Form Component Template
$formTitle = $title ?? 'Get in Touch';
$formSubtitle = $subtitle ?? 'We\'ll respond within 24 hours';
$formDescription = $description ?? 'Have a question or want to join our team? Fill out the form below and we\'ll get back to you.';
$buttonText = $button_text ?? 'Send Message';
$formFields = $fields ?? array();

// Default fields if none provided
if (empty($formFields)) {
    $formFields = array(
        array('name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'placeholder' => '', 'required' => true),
        array('name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'placeholder' => '', 'required' => true),
        array('name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'placeholder' => '', 'required' => true)
    );
}

if (!is_array($formFields)) {
    $formFields = array();
}
?>

<section class="contact-form-section">
    <div class="text-center" style="margin-bottom: 2rem;">
        <h2 class="contact-title"><?= htmlspecialchars($formTitle) ?></h2>
        <p class="contact-subtitle"><?= htmlspecialchars($formSubtitle) ?></p>
        <?php if (!empty($formDescription)): ?>
            <p class="contact-description"><?= htmlspecialchars($formDescription) ?></p>
        <?php endif; ?>
    </div>

    <!-- Display error messages -->
    <?php if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])): ?>
        <div class="form-errors">
            <p style="font-weight: 600; margin-bottom: 0.5rem;">Please correct the following errors:</p>
            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['form_errors']); ?>
    <?php endif; ?>

    <form action="/contact.php" method="POST" class="contact-form">
        <?php foreach ($formFields as $field):
            $fieldType = $field['type'] ?? 'text';
            $isRequired = $field['required'] ?? false;
            $placeholder = $field['placeholder'] ?? '';
        ?>
            <div class="form-group">
                <label for="<?= htmlspecialchars($field['name']) ?>">
                    <?= htmlspecialchars($field['label']) ?>
                    <?php if ($isRequired): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>

                <?php if ($fieldType === 'textarea'): ?>
                    <textarea
                        id="<?= htmlspecialchars($field['name']) ?>"
                        name="<?= htmlspecialchars($field['name']) ?>"
                        rows="6"
                        <?= $isRequired ? 'required' : '' ?>
                        placeholder="<?= htmlspecialchars($placeholder) ?>"><?= isset($_SESSION['form_data'][$field['name']]) ? htmlspecialchars($_SESSION['form_data'][$field['name']]) : '' ?></textarea>
                <?php else: ?>
                    <input
                        type="<?= htmlspecialchars($fieldType) ?>"
                        id="<?= htmlspecialchars($field['name']) ?>"
                        name="<?= htmlspecialchars($field['name']) ?>"
                        <?= $isRequired ? 'required' : '' ?>
                        placeholder="<?= htmlspecialchars($placeholder) ?>"
                        value="<?= isset($_SESSION['form_data'][$field['name']]) ? htmlspecialchars($_SESSION['form_data'][$field['name']]) : '' ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="cta-button">
            <?= htmlspecialchars($buttonText) ?>
        </button>
    </form>

    <?php
    // Clear form data after rendering
    if (isset($_SESSION['form_data'])) {
        unset($_SESSION['form_data']);
    }
    ?>
</section>