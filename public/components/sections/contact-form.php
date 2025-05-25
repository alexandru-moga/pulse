<section class="contact-form-section">
    <form class="contact-form" method="POST" action="<?= BASE_URL ?>contact.php">
        <div class="form-group">
            <label for="name">Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="message">Message <span class="required">*</span></label>
            <textarea id="message" name="message" rows="6" required></textarea>
        </div>
        
        <button type="submit" class="cta-button">Send Message</button>
    </form>
</section>
