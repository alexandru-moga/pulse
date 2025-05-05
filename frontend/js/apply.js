document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('application-form');
    
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        reason: document.getElementById('reason').value,
        experience: document.getElementById('experience').value
      };
      
      try {
        console.log('Form submitted:', formData);
        alert('Application submitted successfully! We will contact you soon.');
        form.reset();
      } catch (error) {
        console.error('Error submitting form:', error);
        alert('There was an error submitting your application. Please try again.');
      }
    });
  });
  