<?php
// components/effects/mouse.php
?>
<div class="mouse-gradient-effect"></div>

<style>
.mouse-gradient-effect {
    pointer-events: none;
    position: fixed;
    inset: 0;
    z-index: -1; /* Behind all elements */
    background: radial-gradient(
        300px at var(--mouse-x) var(--mouse-y), /* 2 times smaller than before */
        var(--primary, rgba(236, 55, 80, 0.15)),
        transparent 80%
    );
    transition: background 0.3s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const updateMousePosition = (e) => {
        document.documentElement.style.setProperty('--mouse-x', `${e.clientX}px`);
        document.documentElement.style.setProperty('--mouse-y', `${e.clientY}px`);
    };
    window.addEventListener('mousemove', updateMousePosition);
});
</script>
