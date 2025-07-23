<?php
// Ensure $stats is defined, fallback to empty array if not
if (!isset($stats) || !is_array($stats)) {
    $stats = [];
}

// Default stats structure if empty
if (empty($stats)) {
    $stats = [
        ['number' => '0', 'label' => 'Placeholder Stat 1'],
        ['number' => '0', 'label' => 'Placeholder Stat 2'],
        ['number' => '0', 'label' => 'Placeholder Stat 3'],
        ['number' => '0', 'label' => 'Placeholder Stat 4']
    ];
}
?>

<div class="bg-white py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:max-w-none">
            <div class="text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Statistics</h2>
                <p class="mt-4 text-lg leading-8 text-gray-600">Our achievements in numbers</p>
            </div>
            <dl class="mt-16 grid grid-cols-1 gap-0.5 overflow-hidden rounded-2xl text-center sm:grid-cols-2 lg:grid-cols-4">
                <?php foreach ($stats as $stat): ?>
                    <div class="flex flex-col bg-gray-400/5 p-8">
                        <dt class="text-sm font-semibold leading-6 text-gray-600"><?= htmlspecialchars($stat['label'] ?? '') ?></dt>
                        <dd class="order-first text-3xl font-semibold tracking-tight text-gray-900"><?= htmlspecialchars($stat['number'] ?? '0') ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
</div>
