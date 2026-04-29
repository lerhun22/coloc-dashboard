<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<style>
    .main-content {
        max-width: 1400px;
        margin: auto;
    }

    .card-row {
        display: grid;
        grid-template-columns: 120px 2fr 120px 2fr 320px;
        gap: 20px;
        padding: 15px;
        border-bottom: 1px solid #eee;
        align-items: center;
    }

    .img-thumb {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 10px;
        cursor: pointer;
    }

    .wow-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    .wow-item {
        cursor: pointer;
    }

    .wow-item img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 8px;
    }

    /* LIGHTBOX */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .lightbox img {
        max-width: 90%;
        max-height: 90%;
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 30px;
        cursor: pointer;
    }

    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 60px;
        color: white;
        cursor: pointer;
    }

    .lightbox-nav.left {
        left: 20px;
    }

    .lightbox-nav.right {
        right: 20px;
    }

    .section-title {
        margin-top: 30px;
        font-weight: bold;
    }
</style>

<div class="main-content">

    <h1>📊 Analyse jugement</h1>

    <?php
    function renderImages($images, $label)
    {

        // 🔥 galerie locale
        $gallery = array_map(
            fn($i) =>
            $i['photo_url'] ?? $i['thumb_url'] ?? base_url('assets/img/no-image.jpg'),
            $images
        );

        $galleryJson = htmlspecialchars(json_encode($gallery), ENT_QUOTES, 'UTF-8');

        foreach ($images as $i => $img) {

            $src = $img['photo_url']
                ?? $img['thumb_url']
                ?? base_url('assets/img/no-image.jpg');

            $title = esc($img['titre'] ?? '');

            echo "<div class='card-row'>";

            // IMAGE
            echo "<div>
            <img src='$src' class='img-thumb'
            onclick='openLightboxList($galleryJson, $i)'>
        </div>";

            // TITRE
            echo "<div>
            <strong>$title</strong><br>
            <small>EAN {$img['ean']}</small>
        </div>";

            // TYPE
            echo "<div>$label</div>";

            // COMPET
            echo "<div>{$img['competition_nom']}</div>";

            // NOTES
            echo "<div>";

            foreach ($img['notes_array'] ?? [] as $note) {

                $color = '#f39c12';
                if ($note >= 16) $color = '#27ae60';
                elseif ($note <= 8) $color = '#e74c3c';

                echo "<span style='background:$color;color:#fff;padding:5px;margin:2px;border-radius:5px'>$note</span>";
            }

            echo "</div>";

            echo "</div>";
        }
    }
    ?>

    <!-- BLOCS -->
    <div class="section-title">🔥 Clivantes</div>
    <?php renderImages($jugement['top_clivantes'] ?? [], 'Clivante'); ?>

    <div class="section-title">🤝 Consensuelles</div>
    <?php renderImages($jugement['top_consensuelles'] ?? [], 'Consensuelle'); ?>

    <div class="section-title">⚖️ Juge décisif</div>
    <?php renderImages($jugement['top_juge_decisif'] ?? [], 'Juge'); ?>

    <!-- WOW -->
    <div class="section-title">✨ WOW</div>
    <?php
    $galleryWow = array_map(
        fn($i) =>
        $i['photo_url'] ?? $i['thumb_url'],
        $wow
    );
    $galleryWowJson = htmlspecialchars(json_encode($galleryWow), ENT_QUOTES, 'UTF-8');
    ?>

    <div class="wow-grid">
        <?php foreach ($wow as $i => $img):
            $src = $img['photo_url'] ?? $img['thumb_url'];
        ?>
            <div class="wow-item"
                onclick='openLightboxList(<?= $galleryWowJson ?>, <?= $i ?>)'>
                <img src="<?= $src ?>">
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- LIGHTBOX -->
<div id="lightbox" class="lightbox">

    <span class="lightbox-close" onclick="closeLightbox()">✕</span>

    <div class="lightbox-nav left" onclick="prevImage()">‹</div>

    <img id="lightbox-img">

    <div class="lightbox-nav right" onclick="nextImage()">›</div>

    <div style="position:absolute;bottom:20px;color:white">
        ← → naviguer | espace = diaporama
    </div>

</div>

<script>
    let gallery = [];
    let current = 0;
    let autoPlay = null;

    function openLightboxList(list, index) {
        gallery = list;
        current = index;
        updateLightbox();
        document.getElementById('lightbox').style.display = 'flex';
    }

    function updateLightbox() {
        if (!gallery.length) return;
        document.getElementById('lightbox-img').src = gallery[current];
    }

    function nextImage() {
        current = (current + 1) % gallery.length;
        updateLightbox();
    }

    function prevImage() {
        current = (current - 1 + gallery.length) % gallery.length;
        updateLightbox();
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        stopAuto();
    }

    function startAuto() {
        stopAuto();
        autoPlay = setInterval(nextImage, 2000);
    }

    function stopAuto() {
        if (autoPlay) {
            clearInterval(autoPlay);
            autoPlay = null;
        }
    }

    document.addEventListener('keydown', function(e) {

        if (document.getElementById('lightbox').style.display !== 'flex') return;

        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === 'Escape') closeLightbox();
        if (e.key === ' ') startAuto();
    });
</script>

<?= $this->endSection() ?>