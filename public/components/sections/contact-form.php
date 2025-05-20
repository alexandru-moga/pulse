<section class="contact-form-section">
    <?php if(isset($_SESSION['form_errors'])): ?>
    <div class="form-errors">
        <?php foreach($_SESSION['form_errors'] as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
        <?php unset($_SESSION['form_errors']); ?>
    </div>
    <?php endif; ?>

    <form class="contact-form" method="POST" action="<?= BASE_URL ?>contact.php">
        <fieldset>
            <legend>Contact Information</legend>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name<span class="required">*</span></label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address<span class="required">*</span></label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Your Message</legend>
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="message">Message<span class="required">*</span></label>
                    <textarea id="message" name="message" rows="6" required><?= 
                        htmlspecialchars($_POST['message'] ?? '') 
                    ?></textarea>
                </div>
            </div>
        </fieldset>

        <button type="submit" class="cta-button">Send Message</button>
    </form>
</section>
