<?php
// Default values
$effect_type = $effect_type ?? 'trail';
$color = $color ?? '#ef4444';
?>

<div class="mouse-effects-container">
    <style>
        .mouse-trail {
            position: fixed;
            width: 10px;
            height: 10px;
            background: <?= htmlspecialchars($color) ?>;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            opacity: 0.7;
            transition: all 0.3s ease;
        }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if ('<?= $effect_type ?>' === 'trail') {
            let trail = [];
            document.addEventListener('mousemove', function(e) {
                const dot = document.createElement('div');
                dot.className = 'mouse-trail';
                dot.style.left = e.clientX + 'px';
                dot.style.top = e.clientY + 'px';
                document.body.appendChild(dot);
                
                trail.push(dot);
                if (trail.length > 10) {
                    trail.shift().remove();
                }
                
                setTimeout(() => {
                    if (dot.parentNode) {
                        dot.style.opacity = '0';
                        setTimeout(() => dot.remove(), 300);
                    }
                }, 100);
            });
        }
    });
    </script>
</div>
