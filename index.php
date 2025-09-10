<?php
<?php
require_once 'core/init.php';
checkMaintenanceMode();
?>
<?php include 'components/layout/header.php'; ?>

<!-- JukeBox Sticker Credits Announcement -->
<section class="jukebox-announcement" style="background: #f7f7f7; border: 1px solid #e0e0e0; padding: 2rem; margin: 2rem 0; border-radius: 8px;">
    <h2 style="margin-top:0;">Get $500 in Sticker Credits with JukeBox</h2>
    <p>
        Jukebox Print is committing <strong>$500 in credits</strong> per qualifying HCB organization.<br>
        The credit is valid site wide, and you can place multiple orders using the same coupon code until it runs out.
    </p>
    <ul>
        <li>You <strong>NEED 10 (ten) or more active teenagers</strong> (under 18, logged in within the last 30 days) who have accepted the invite.</li>
        <li>You must include a link in your event page, recap, or website that points to: 
            <a href="https://www.jukeboxprint.com/custom-stickers">custom stickers</a>.
        </li>
        <li>For SEO, <strong>“custom stickers”</strong> must be the only text linking to their page.</li>
        <li>Example: Big shoutout to Jukebox for our <a href="https://www.jukeboxprint.com/custom-stickers">custom stickers</a>!</li>
        <li>Do <strong>not</strong> hyperlink any other text to Jukebox.</li>
    </ul>
    <p>
        <strong>Website:</strong> phoenixclub.ro<br>
        <strong>Memorandum of Understanding:</strong> Please press "Submit" at the bottom of this form after signing and adding the link to your website. Signing the contract on its own will not complete your application.<br>
        <strong>Logo:</strong> Jukebox MOU<br>
        <strong>Contact:</strong> alexandru.moga08@gmail.com
    </p>
    <button style="background: #0078d4; color: #fff; padding: 0.5rem 1.5rem; border: none; border-radius: 4px; cursor: pointer;">Submit</button>
</section>

<head>
    <link rel="stylesheet" href="css/main.css">
</head>

<main>
    <?php foreach ($pageStructure['components'] as $component): ?>
        <?= $pageManager->renderComponent($component) ?>
    <?php endforeach; ?>
    <?php include 'components/effects/mouse.php'; ?>
    <?php include 'components/effects/globe.php'; ?>
    <?php include 'components/effects/grid.php'; ?>
</main>

<?php include 'components/layout/footer.php'; ?>