<?php

?>
<div class="mouse-gradient-effect"></div>

<style>
.mouse-gradient-effect {
    pointer-events: none;
    position: fixed;
    inset: 0;
    z-index: -1;
    background: radial-gradient(
        10px at var(--mouse-x) var(--mouse-y),
        var(--primary, rgba(75, 21, 28, 0.15)),
        transparent 1000%
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
