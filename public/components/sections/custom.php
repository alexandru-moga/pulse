<?php
if ($block_name == 'events_section' && is_array($block_content)): 
  $title = $block_content['title'] ?? 'Upcoming Events';
  $events = $block_content['events'] ?? [];
?>
<section class="container events-section">
  <h2 class="events-title"><?= htmlspecialchars($title) ?></h2>
  <div class="events-row">
    <?php foreach ($events as $event): ?>
    <div class="event-card">
      <h3 class="event-card-title"><?= htmlspecialchars($event['name']) ?></h3>
      <p class="event-card-date"><?= htmlspecialchars($event['date']) ?> | <?= htmlspecialchars($event['time']) ?></p>
      <p class="event-card-desc"><?= htmlspecialchars($event['description']) ?></p>
      <a class="event-card-link" href="<?= htmlspecialchars($event['button_link']) ?>">
        <?= htmlspecialchars($event['button_text']) ?> &rarr;
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php else: ?>
  <!-- Fallback for other custom blocks -->
  <div class="custom-block">
    <?php if (is_array($block_content)): ?>
      <pre><?= htmlspecialchars(json_encode($block_content, JSON_PRETTY_PRINT)) ?></pre>
    <?php else: ?>
      <?= htmlspecialchars($block_content) ?>
    <?php endif; ?>
  </div>
<?php endif; ?>
