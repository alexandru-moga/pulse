<?php
$formConfig = json_decode($block_content, true);
?>
<form class="apply-form" method="POST" action="/apply.php">
  <?php foreach ($formConfig['sections'] as $section): ?>
  <fieldset>
    <legend><?= htmlspecialchars($section['title']) ?></legend>
    <div class="form-grid">
      <?php foreach ($section['fields'] as $field): ?>
      <div class="form-group">
        <label>
          <?= htmlspecialchars($field['label']) ?>
          <?= ($field['required'] ?? false) ? '<span class="required">*</span>' : '' ?>
        </label>
        
        <?php if ($field['type'] === 'select'): ?>
          <select name="<?= $field['name'] ?>" <?= ($field['required'] ?? false) ? 'required' : '' ?>>
            <?php foreach ($field['options'] as $option): ?>
            <option value="<?= htmlspecialchars($option['value']) ?>">
              <?= htmlspecialchars($option['label']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <input type="<?= $field['type'] ?>" 
                 name="<?= $field['name'] ?>" 
                 placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                 <?= ($field['required'] ?? false) ? 'required' : '' ?>
                 <?= isset($field['min']) ? "min=\"{$field['min']}\"" : '' ?>
                 <?= isset($field['max']) ? "max=\"{$field['max']}\"" : '' ?>>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </fieldset>
  <?php endforeach; ?>

  <input type="hidden" name="birthdate" value="2000-01-01">
  <input type="hidden" name="superpowers" value="Not specified">

  <button type="submit" class="cta-button">Join Us today!</button>
  <p class="form-disclaimer">
    By registering, you agree to the <a href="/terms">Terms of Service</a> and <a href="/privacy">Privacy Policy</a>
  </p>
</form>
