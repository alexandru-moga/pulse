<form class="apply-form" method="POST" action="/apply.php">
  <fieldset>
    <legend>Personal Information</legend>

    <?php if (isset($_SESSION['form_errors'])): ?>
      <div class="form-errors">
        <?php foreach ($_SESSION['form_errors'] as $error): ?>
          <p class="error"><?php echo htmlspecialchars($error) ?></p>
        <?php endforeach;
        unset($_SESSION['form_errors']); ?>
      </div>
    <?php endif; ?>

    <div class="form-row">
      <div class="form-group">
        <label for="first_name">First Name<span class="required">*</span></label>
        <input type="text" id="first_name" name="first_name"
          value="<?php echo htmlspecialchars($_POST['first_name'] ?? '') ?>"
          required>
      </div>
      <div class="form-group">
        <label for="last_name">Last Name<span class="required">*</span></label>
        <input type="text" id="last_name" name="last_name"
          value="<?php echo htmlspecialchars($_POST['last_name'] ?? '') ?>"
          required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="email">Email<span class="required">*</span></label>
        <input type="email" id="email" name="email"
          value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>"
          required>
      </div>
      <div class="form-group">
        <label for="phone">Phone<span class="required">*</span></label>
        <input type="tel" id="phone" name="phone"
          value="<?php echo htmlspecialchars($_POST['phone'] ?? '') ?>"
          pattern="[0-9]{10,15}" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="birthdate">Birthdate<span class="required">*</span></label>
        <input type="date" id="birthdate" name="birthdate"
          value="<?php echo htmlspecialchars($_POST['birthdate'] ?? '') ?>"
          required>
      </div>
    </div>
  </fieldset>

  <fieldset>
    <legend>Academic Information</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="school">School<span class="required">*</span></label>
        <input type="text" id="school" name="school"
          value="<?php echo htmlspecialchars($_POST['school'] ?? '') ?>"
          required>
      </div>
      <div class="form-group">
        <label for="class">Grade/Year<span class="required">*</span></label>
        <select id="class" name="class" required>
          <option value="">Select grade</option>
          <option value="9" <?php echo ($_POST['class'] ?? '') === '9' ? 'selected' : '' ?>>9th</option>
          <option value="10" <?php echo ($_POST['class'] ?? '') === '10' ? 'selected' : '' ?>>10th</option>
          <option value="11" <?php echo ($_POST['class'] ?? '') === '11' ? 'selected' : '' ?>>11th</option>
          <option value="12" <?php echo ($_POST['class'] ?? '') === '12' ? 'selected' : '' ?>>12th</option>
          <option value="university" <?php echo ($_POST['class'] ?? '') === 'university' ? 'selected' : '' ?>>University</option>
        </select>
      </div>
    </div>

  </fieldset>

  <fieldset>
    <legend>Additional Information</legend>
    <div class="form-row">
      <div class="form-group full-width">
        <label for="superpowers">Coding Skills/Superpowers<span class="required">*</span></label>
        <textarea id="superpowers" name="superpowers" rows="4" required><?php
                                                                        echo htmlspecialchars($_POST['superpowers'] ?? '')
                                                                        ?></textarea>
      </div>
    </div>
  </fieldset>

  <button type="submit" class="cta-button">Submit Application</button>
</form>