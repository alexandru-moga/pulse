<form class="apply-form" method="POST" action="/submit-application">
  <fieldset>
    <legend>Personal Information</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="first_name">First Name<span class="required">*</span></label>
        <input type="text" id="first_name" name="first_name" placeholder="Your first name" required>
      </div>
      <div class="form-group">
        <label for="last_name">Last Name<span class="required">*</span></label>
        <input type="text" id="last_name" name="last_name" placeholder="Your last name" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="email">Email Address<span class="required">*</span></label>
        <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="Your phone number">
      </div>
    </div>
  </fieldset>

  <fieldset>
    <legend>Academic Information</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="school">School<span class="required">*</span></label>
        <input type="text" id="school" name="school" placeholder="Your school name" required>
      </div>
      <div class="form-group">
        <label for="grade">Grade/Year<span class="required">*</span></label>
        <select id="grade" name="grade" required>
          <option value="">Select grade</option>
          <option value="9">9th</option>
          <option value="10">10th</option>
          <option value="11">11th</option>
          <option value="12">12th</option>
          <option value="university">University</option>
        </select>
      </div>
      <div class="form-group">
        <label for="age">Age<span class="required">*</span></label>
        <input type="number" id="age" name="age" placeholder="Your age" min="10" max="99" required>
      </div>
    </div>
  </fieldset>

  <fieldset>
    <legend>Coding Experience</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="github">GitHub Username (if you have one)</label>
        <div class="input-prefix">
          <span>github.com/</span>
          <input type="text" id="github" name="github" placeholder="username">
        </div>
      </div>
    </div>
  </fieldset>

  <button type="submit" class="cta-button">Join Suceava Hacks</button>
  <p class="form-disclaimer">
    By registering, you agree to the <a href="/terms">Terms of Service</a> and <a href="/privacy">Privacy Policy</a>
  </p>
</form>
