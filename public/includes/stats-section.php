<section class="container" style="margin-bottom: 3rem;">
  <div class="stats-row">
    <?php 
    $stats = [
        ['number' => INDEX_ACTIVE_STUDENTS, 'label' => 'Active Members'],
        ['number' => INDEX_ACTIVE_PROJECTS, 'label' => 'Projects Active'],
        ['number' => INDEX_COMPLETED_PROJECTS, 'label' => 'Projects Completed']
    ];
    foreach ($stats as $stat): ?>
    <div class="stats-card">
      <div class="stats-number"><?= $stat['number'] ?>+</div>
      <div class="stats-label"><?= $stat['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
