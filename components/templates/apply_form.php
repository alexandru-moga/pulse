<?php
// Apply Form Component Template
$sections = $sections ?? [
    [
        'title' => 'Personal Information',
        'fields' => [
            ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => 'Your first name', 'required' => true],
            ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => 'Your last name', 'required' => true],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'your.email@example.com', 'required' => true],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'placeholder' => '+40 XXX XXX XXX', 'required' => true],
            ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'placeholder' => 'mm/dd/yyyy', 'required' => true],
        ]
    ],
    [
        'title' => 'Academic Information',
        'fields' => [
            ['name' => 'school', 'label' => 'School', 'type' => 'text', 'placeholder' => 'Your school name', 'required' => true],
            ['name' => 'class', 'label' => 'Grade/Year', 'type' => 'text', 'placeholder' => 'e.g., 10th Grade, Freshman', 'required' => true],
        ]
    ],
    [
        'title' => 'Additional Information',
        'fields' => [
            ['name' => 'description', 'label' => 'Coding Skills/Superpowers', 'type' => 'textarea', 'placeholder' => 'Tell us about your coding experience, projects, or what you\'d like to learn...', 'required' => true],
        ]
    ]
];
?>

<!-- Display error messages -->
<?php if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])): ?>
    <div style="max-width: 560px; margin: 1rem auto; background: rgba(220, 38, 38, 0.1); border-left: 4px solid #dc2626; padding: 1rem; border-radius: 0.5rem;">
        <div style="color: #fca5a5;">
            <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Please correct the following errors:</h3>
            <ul style="font-size: 0.875rem; list-style: disc; padding-left: 1.5rem;">
                <?php foreach ($_SESSION['form_errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php unset($_SESSION['form_errors']); ?>
<?php endif; ?>

<form action="/apply.php" method="POST" class="apply-form">
    <?php foreach ($sections as $sectionIndex => $section): ?>
        <fieldset>
            <legend><?= htmlspecialchars($section['title']) ?></legend>
            
            <?php
            // Group fields into rows of 2
            $fields = $section['fields'];
            $fieldCount = count($fields);
            
            for ($i = 0; $i < $fieldCount; $i += 2):
                $field1 = $fields[$i];
                $field2 = isset($fields[$i + 1]) ? $fields[$i + 1] : null;
            ?>
                <div class="form-row">
                    <!-- First field -->
                    <div class="form-group">
                        <label for="<?= htmlspecialchars($field1['name']) ?>">
                            <?= htmlspecialchars($field1['label']) ?>
                            <?php if ($field1['required'] ?? false): ?><span class="required">*</span><?php endif; ?>
                        </label>
                        
                        <?php if ($field1['type'] === 'textarea'): ?>
                            <textarea
                                id="<?= htmlspecialchars($field1['name']) ?>"
                                name="<?= htmlspecialchars($field1['name']) ?>"
                                rows="4"
                                <?= ($field1['required'] ?? false) ? 'required' : '' ?>
                                placeholder="<?= htmlspecialchars($field1['placeholder'] ?? '') ?>"
                                style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid rgba(236, 55, 80, 0.15); border-radius: 0.6rem; background: rgba(255, 255, 255, 0.08); color: #fff; font-size: 1rem; font-family: inherit;"><?= isset($_SESSION['form_data'][$field1['name']]) ? htmlspecialchars($_SESSION['form_data'][$field1['name']]) : '' ?></textarea>
                        <?php else: ?>
                            <input
                                type="<?= htmlspecialchars($field1['type']) ?>"
                                id="<?= htmlspecialchars($field1['name']) ?>"
                                name="<?= htmlspecialchars($field1['name']) ?>"
                                <?= ($field1['required'] ?? false) ? 'required' : '' ?>
                                placeholder="<?= htmlspecialchars($field1['placeholder'] ?? '') ?>"
                                value="<?= isset($_SESSION['form_data'][$field1['name']]) ? htmlspecialchars($_SESSION['form_data'][$field1['name']]) : '' ?>">
                        <?php endif; ?>
                    </div>
                    
                    <!-- Second field (if exists) -->
                    <?php if ($field2): ?>
                        <div class="form-group">
                            <label for="<?= htmlspecialchars($field2['name']) ?>">
                                <?= htmlspecialchars($field2['label']) ?>
                                <?php if ($field2['required'] ?? false): ?><span class="required">*</span><?php endif; ?>
                            </label>
                            
                            <?php if ($field2['type'] === 'textarea'): ?>
                                <textarea
                                    id="<?= htmlspecialchars($field2['name']) ?>"
                                    name="<?= htmlspecialchars($field2['name']) ?>"
                                    rows="4"
                                    <?= ($field2['required'] ?? false) ? 'required' : '' ?>
                                    placeholder="<?= htmlspecialchars($field2['placeholder'] ?? '') ?>"
                                    style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid rgba(236, 55, 80, 0.15); border-radius: 0.6rem; background: rgba(255, 255, 255, 0.08); color: #fff; font-size: 1rem; font-family: inherit;"><?= isset($_SESSION['form_data'][$field2['name']]) ? htmlspecialchars($_SESSION['form_data'][$field2['name']]) : '' ?></textarea>
                            <?php else: ?>
                                <input
                                    type="<?= htmlspecialchars($field2['type']) ?>"
                                    id="<?= htmlspecialchars($field2['name']) ?>"
                                    name="<?= htmlspecialchars($field2['name']) ?>"
                                    <?= ($field2['required'] ?? false) ? 'required' : '' ?>
                                    placeholder="<?= htmlspecialchars($field2['placeholder'] ?? '') ?>"
                                    value="<?= isset($_SESSION['form_data'][$field2['name']]) ? htmlspecialchars($_SESSION['form_data'][$field2['name']]) : '' ?>">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </fieldset>
    <?php endforeach; ?>
    
    <button type="submit" class="cta-button">Submit Application</button>
</form>

<?php
// Clear form data after rendering
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>