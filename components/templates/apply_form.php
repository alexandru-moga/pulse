<?php
// Apply Form Component Template
$sections = $sections ?? [
    [
        'title' => 'Personal Information',
        'fields' => [
            ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => 'Your first name', 'required' => true],
            ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => 'Your last name', 'required' => true],
        ]
    ],
    [
        'title' => 'Academic Information',
        'fields' => [
            ['name' => 'school', 'label' => 'School', 'type' => 'text', 'placeholder' => 'Your school name', 'required' => true],
        ]
    ]
];
?>

<div class="ddb-apply-form py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <form action="/apply.php" method="POST" class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($sections as $sectionIndex => $section): ?>
                    <div class="p-8">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                            <?= htmlspecialchars($section['title']) ?>
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($section['fields'] as $field): 
                                $fieldType = $field['type'] ?? 'text';
                                $isRequired = $field['required'] ?? false;
                                $placeholder = $field['placeholder'] ?? '';
                                $options = $field['options'] ?? [];
                            ?>
                                <div class="<?= (count($section['fields']) === 1) ? 'md:col-span-2' : '' ?>">
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
                                    
                                    <?php elseif ($fieldType === 'select' && !empty($options)): ?>
                                        <select 
                                            id="<?= htmlspecialchars($field['name']) ?>"
                                            name="<?= htmlspecialchars($field['name']) ?>"
                                            <?= $isRequired ? 'required' : '' ?>
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                                            <option value="">Select <?= htmlspecialchars($field['label']) ?></option>
                                            <?php foreach ($options as $value => $label): ?>
                                                <option value="<?= htmlspecialchars($value) ?>">
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    
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
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="p-8 bg-gray-50 dark:bg-gray-900">
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-md shadow-sm transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Submit Application
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
