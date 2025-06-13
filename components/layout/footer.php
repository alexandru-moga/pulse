<?php
$footer = new Footer($db);
$footerContent = $footer->getFooterContent();
?>
<footer class="club-footer">
    <div class="footer-container">
        <div class="footer-columns">
            <div class="footer-brand">
                <?php if (!empty($footerContent['logo']['path'])): ?>
                    <img src="<?= $settings['site_url'] . '/' . htmlspecialchars($footerContent['logo']['path']) ?>" 
                         alt="<?= htmlspecialchars($settings['site_title']) ?> Logo" 
                         class="footer-logo">
                <?php else: ?>
                    <h2 class="club-name"><?= htmlspecialchars($settings['site_title']) ?></h2>
                <?php endif; ?>
            </div>
            <div class="footer-nav-column">
                <h3 class="footer-heading"><?= htmlspecialchars($footerContent['links']['title'] ?? 'Explore') ?></h3>
                <nav class="footer-nav">
                    <?php foreach ($footerContent['links']['items'] ?? [] as $link): ?>
                        <a href="<?= $settings['site_url'] . htmlspecialchars($link['url']) ?>" 
                           class="footer-link">
                            <?= htmlspecialchars($link['text']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
            <div class="footer-cta">
                <h3 class="footer-heading"><?= htmlspecialchars($footerContent['cta']['title'] ?? 'Get Involved') ?></h3>
                <a href="<?= $settings['site_url'] . htmlspecialchars($footerContent['cta']['url'] ?? '/join') ?>" 
                   class="cta-button">
                    <?= htmlspecialchars($footerContent['cta']['text'] ?? 'Join Our Club') ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" 
                         fill="none" stroke="currentColor" stroke-width="2" 
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="footer-credits">
            <p class="credit-text">
                © <?= date('Y') ?> <?= htmlspecialchars($settings['site_title']) ?> • 
                Powered by <a href="https://github.com/alexandru-moga/pulse" 
                             class="credit-link">Pulse</a>
            </p>
            <?php if ($footerContent['credits']['show_attribution'] ?? false): ?>
                <p class="attribution">
                    Part of the global <a href="https://hackclub.com/" 
                                         class="credit-link">Hack Club</a> network
                </p>
            <?php endif; ?>
        </div>
    </div>
</footer>
