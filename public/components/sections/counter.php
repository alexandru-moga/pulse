<?php if ($block_name == 'active_members' && empty($counterStarted)): ?>
  <section class="container" style="margin-bottom: 3rem;">
    <div class="stats-row">
      <?php $counterStarted = true; ?>
<?php endif; ?>

<div class="stats-card">
  <div class="stats-number"><?= htmlspecialchars($block_content) ?>+</div>
  <div class="stats-label">
    <?php
      $labels = [
        'active_members' => 'Active Members',
        'active_projects' => 'Projects Active',
        'completed_projects' => 'Projects Completed'
      ];
      echo $labels[$block_name] ?? ucwords(str_replace('_', ' ', $block_name));
    ?>
  </div>
</div>

<?php if ($block_name == 'completed_projects'): ?>
    </div>
  </section>
<?php endif; ?>
