<?php // components/effects/background.php ?>
<div id="vanta-bg" style="position:absolute; inset:0; z-index:-2;height:100vh;"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const rootStyles = getComputedStyle(document.documentElement);
  const primaryColor = rootStyles.getPropertyValue('--primary').trim() || '#ec3750';
  const secondaryColor = rootStyles.getPropertyValue('--text').trim() || '#ff8c37';

  // Convert hex colors to integers
  const primaryInt = parseInt(primaryColor.replace('#', '0x'));
  const secondaryInt = parseInt(secondaryColor.replace('#', '0x'));

  VANTA.GLOBE({
    el: "#vanta-bg",
    mouseControls: true,
    touchControls: true,
    gyroControls: false,
    minHeight: 200.00,
    minWidth: 200.00,
    scale: 1.00,
    scaleMobile: 1.00,
    color: primaryInt,
    color2: secondaryInt,
    backgroundColor: 0x000000, // Base color (will be transparent)
    backgroundAlpha: 0, // Full transparency
    THREE: THREE // Pass Three.js reference
  });

  // Configure Three.js renderer for transparency
  const vantaEffect = document.querySelector('#vanta-bg canvas');
  if(vantaEffect) {
    vantaEffect.style.backgroundColor = 'transparent';
    vantaEffect.style.opacity = '0.8'; // Adjust opacity as needed
  }
});
</script>
