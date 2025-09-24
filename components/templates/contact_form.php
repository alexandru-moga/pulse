<?php
// Contact Form Component Template
$formTitle = $title ?? 'Contact Us';
$formSubtitle = $subtitle ?? 'Get in touch with our team';
$formDescription = $description ?? 'We would love to hear from you';
$buttonText = $button_text ?? 'Send Message';
$formFields = $fields ?? array();

// Default fields if none provided
if (empty($formFields)) {
    $formFields = array(
        array('name' => 'name', 'label' => 'Name', 'type' => 'text', 'placeholder' => 'Your name', 'required' => true),
        array('name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'your@email.com', 'required' => true),
        array('name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'placeholder' => 'Your message', 'required' => true)
    );
}

if (!is_array($formFields)) {
    $formFields = array();
}
$buttonText = $button_text ?? 'Send Message';
?>

<div class="ddb-contact-form py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($formTitle) ?></h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300"><?= htmlspecialchars($formSubtitle) ?></p>
            <?php if (!empty($formDescription)): ?>
                <p class="mt-2 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($formDescription) ?></p>
            <?php endif; ?>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">
            <form action="/contact.php" method="POST" class="space-y-6">
                <?php foreach ($fields as $field):
                    $fieldType = $field['type'] ?? 'text';
                    $isRequired = $field['required'] ?? false;
                    $placeholder = $field['placeholder'] ?? '';
                ?>
                    <div>
                        <label for="<?= htmlspecialchars($field['name']) ?>"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <?= htmlspecialchars($field['label']) ?>
                            <?php if ($isRequired): ?>
                                <span class="text-red-500">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($fieldType === 'textarea'): ?>
                            <textarea
                                id="<?= htmlspecialchars($field['name']) ?>"
                                name="<?= htmlspecialchars($field['name']) ?>"
                                rows="4"
                                <?= $isRequired ? 'required' : '' ?>
                                placeholder="<?= htmlspecialchars($placeholder) ?>"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                        <?php else: ?>
                            <input
                                type="<?= htmlspecialchars($fieldType) ?>"
                                id="<?= htmlspecialchars($field['name']) ?>"
                                name="<?= htmlspecialchars($field['name']) ?>"
                                <?= $isRequired ? 'required' : '' ?>
                                placeholder="<?= htmlspecialchars($placeholder) ?>"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-md shadow-sm transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <?= htmlspecialchars($buttonText) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>