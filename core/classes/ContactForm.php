<?php

?>

<section class="contact-form-section">
    <?php if(isset($_SESSION['form_errors'])): ?>
        <div class="form-errors">
            <?php foreach($_SESSION['form_errors'] as $field => $error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
            <?php unset($_SESSION['form_errors']); ?>
        </div>
    <?php endif; ?>

    <form class="contact-form" method="POST" action="<?= BASE_URL ?>contact.php">
        <div class="form-group">
            <label for="name">Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" 
                   value="<?= htmlspecialchars($_SESSION['form_data']['name'] ?? '') ?>" 
                   placeholder="Your full name"
                   required>
        </div>
        
        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($_SESSION['form_data']['email'] ?? '') ?>" 
                   placeholder="your@email.com"
                   required>
        </div>
        
        <div class="form-group">
            <label for="message">Message <span class="required">*</span></label>
            <textarea id="message" name="message" rows="6" 
                      placeholder="Write your message here..."
                      required><?= htmlspecialchars($_SESSION['form_data']['message'] ?? '') ?></textarea>
        </div>
        
        <button type="submit" class="cta-button">Send Message</button>
    </form>

    <?php if(isset($_SESSION['form_data'])): ?>
        <?php unset($_SESSION['form_data']); ?>
    <?php endif; ?>
</section>
