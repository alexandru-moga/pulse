<div class="grid-bg"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  function resizeGridBg() {
    var gridBg = document.querySelector('.grid-bg');
    if (gridBg) {
      gridBg.style.height = document.documentElement.scrollHeight + 'px';
    }
  }
  resizeGridBg();
  window.addEventListener('resize', resizeGridBg);
  window.addEventListener('scroll', resizeGridBg);
});
</script>
