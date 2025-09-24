<?php
// Statistics Component Template
$statsData = $stats ?? array();

// If stats is a JSON string, decode it
if (is_string($statsData)) {
    $decoded = json_decode($statsData, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $statsData = $decoded;
    }
}

// Default stats if none provided
if (empty($statsData)) {
    $statsData = array(
        array('value' => '150', 'label' => 'Active Members'),
        array('value' => '25', 'label' => 'Projects Active'),
        array('value' => '50', 'label' => 'Projects Completed')
    );
}

if (!is_array($statsData)) {
    $statsData = array();
}
?>

<section class="container mx-auto py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php foreach ($statsData as $stat): ?>
            <div class="text-center">
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <div class="text-4xl font-bold text-primary mb-2">
                        <?= htmlspecialchars($stat['value'] ?? '') ?>
                    </div>
                    <div class="text-gray-600 font-medium">
                        <?= htmlspecialchars($stat['label'] ?? '') ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
}

.stat-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #dc2626;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6b7280;
    font-weight: 500;
}
</style>