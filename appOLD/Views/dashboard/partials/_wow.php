<?php if (empty($wow)) return; ?>

<div class="section">

    <h2>✨ WOW (16+)</h2>

    <?php
    $galleryWow = array_map(
        fn($i) => $i['photo_url'] ?? $i['thumb_url'],
        $wow
    );

    $json = htmlspecialchars(
        json_encode($galleryWow),
        ENT_QUOTES
    );
    ?>

    <div class="wow-grid">

        <?php foreach ($wow as $i => $img): ?>

            <img
                src="<?= $img['photo_url'] ?? $img['thumb_url'] ?>"
                onclick='openLightboxList(
                    <?= $json ?>,
                    <?= $i ?>
                )'>

        <?php endforeach; ?>

    </div>

</div>