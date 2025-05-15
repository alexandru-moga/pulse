<div id="particles-js" style="position:absolute; top:0; left:0; width:100vw; z-index:-2;"></div>
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
function setParticlesHeight() {
  var el = document.getElementById('particles-js');
  el.style.height = Math.max(
    document.body.scrollHeight,
    document.documentElement.scrollHeight
  ) + "px";
}
document.addEventListener('DOMContentLoaded', function() {
  setParticlesHeight();
  window.addEventListener('resize', setParticlesHeight);

  const rootStyles = getComputedStyle(document.documentElement);
  let primaryColor = rootStyles.getPropertyValue('--primary').trim() || '#ec3750';
  if (!primaryColor) primaryColor = '#ec3750';

  particlesJS("particles-js", {
    particles: {
      number: { value: 360, density: { enable: true, value_area: 900 } },
      color: { value: primaryColor },
      shape: { type: "circle" },
      opacity: { value: 0.6, random: false },
      size: { value: 3, random: true },
      line_linked: {
        enable: true,
        distance: 130,
        color: primaryColor,
        opacity: 0.4,
        width: 1
      },
      move: {
        enable: true,
        speed: 1.2,
        direction: "none",
        random: false,
        straight: false,
        out_mode: "out",
        bounce: false
      }
    },
    interactivity: {
      detect_on: "canvas",
      events: {
        onhover: { enable: true, mode: "repulse" },
        onclick: { enable: false },
        resize: true
      },
      modes: {
        repulse: { distance: 80, duration: 0.4 }
      }
    },
    retina_detect: true
  });
});
</script>
