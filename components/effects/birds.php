<div id="vanta-birds" style="position:fixed; inset:0; width:100vw; height:100vh; z-index:-2;"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta/dist/vanta.birds.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const rootStyles = getComputedStyle(document.documentElement);
  const primaryColor = rootStyles.getPropertyValue('--primary').trim() || '#ec3750';
  const secondaryColor = rootStyles.getPropertyValue('--text').trim() || '#ff8c37';

  const primaryInt = parseInt(primaryColor.replace('#', '0x'));
  const secondaryInt = parseInt(secondaryColor.replace('#', '0x'));

  VANTA.BIRDS({
    el: "#vanta-birds",
    mouseControls: true,
    touchControls: true,
    gyroControls: false,
    minHeight: 200.00,
    minWidth: 200.00,
    scale: 1.00,
    scaleMobile: 1.00,
    backgroundAlpha: 0,
    backgroundColor: 0x10132e,
    color: primaryInt,
    color2: secondaryInt,
    birdSize: 1.5,
    wingSpan: 20.0,
    speedLimit: 4.0,
    separation: 50.0,
    alignment: 50.0,
    cohesion: 50.0,
    quantity: 3.0 
  });
});
</script>
