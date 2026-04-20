<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<style>
    .main-content {
        max-width: 1400px;
        margin: auto;
    }

    .section {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    h2 {
        margin-bottom: 10px;
    }

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .card {
        padding: 15px;
        background: #f7f9fc;
        border-radius: 8px;
    }

    .highlight {
        color: #e67e22;
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th,
    td {
        padding: 8px;
        border-bottom: 1px solid #eee;
        text-align: center;
    }

    th {
        background: #2c3e50;
        color: #fff;
    }

    .small {
        font-size: 12px;
        color: #666;
    }

    .legend {
        margin-top: 10px;
        font-size: 12px;
    }

    /* ========================= */
    /* 🔴 GALERIE JUGEMENT */
    /* ========================= */

    .jugement-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
    }

    .jugement-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: .2s;
    }

    .jugement-card:hover {
        transform: translateY(-4px);
    }

    .jugement-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        cursor: pointer;
    }

    .jugement-content {
        padding: 10px;
        text-align: left;
    }

    .jugement-title {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .jugement-meta {
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
    }

    .badge-notes span {
        display: inline-block;
        padding: 4px 6px;
        margin: 2px;
        border-radius: 4px;
        font-size: 11px;
        color: #fff;
    }

    /* ========================= */
    /* ✨ WOW */
    /* ========================= */

    .wow-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 10px;
    }

    .wow-grid img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
    }

    /* ========================= */
    /* LIGHTBOX */
    /* ========================= */

    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, .95);
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
</style>

<div class="main-content">

    <h1>📊 Dashboard COLOC — Saison <?= $annee ?></h1>

    <?php
    $top = array_slice($national, 0, 10);
    $urClubs = $dashboard['urClubs'];
    $matrix = $dashboard['competitionMatrix'] ?? [];
    $globalFPF = $dashboard['globalFPF'];
    $comparison = $dashboard['comparison'];

    /* MAP CLUBS */
    $clubMap = [];
    foreach ($urClubs as $c) {
        $clubMap[$c['numero']] = $c['nom'];
    }
    ?>

    <!-- ========================= -->
    <!-- 🔵 NATIONAL -->
    <!-- ========================= -->
    <div class="section">

        <h2>🔵 Classement National</h2>

        <div class="grid-3">
            <div class="card">
                <b>Clubs actifs</b><br>
                <?= $globalFPF['nb_clubs'] ?><br>
                <span class="small">sur <?= $totalClubs ?></span>
            </div>

            <div class="card">
                <b>Points FPF</b><br>
                <?= number_format($globalFPF['nb_points'], 0, ',', ' ') ?>
            </div>

            <div class="card highlight">
                <b>UR22</b><br><?= $comparison['ratio_points'] ?> %
            </div>
        </div>

        <table>
            <tr>
                <th>#</th>
                <th>Club</th>
                <th>UR</th>
                <th>N2</th>
                <th>N1</th>
                <th>CDF</th>
                <th>Total</th>
            </tr>

            <?php foreach ($top as $c): ?>
                <tr>
                    <td><?= $c['rank'] ?></td>
                    <td><?= esc($c['club_nom']) ?></td>
                    <td><?= $c['ur'] ?></td>
                    <td><?= number_format($c['N2']) ?></td>
                    <td><?= number_format($c['N1']) ?></td>
                    <td><?= number_format($c['CDF']) ?></td>
                    <td><strong><?= number_format($c['total']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </table>

    </div>

    <!-- ========================= -->
    <!-- 🟢 UR22 -->
    <!-- ========================= -->
    <div class="section">

        <h2>🟢 UR22 — Classement</h2>

        <table>
            <tr>
                <th>#</th>
                <th>Club</th>
                <th>N2</th>
                <th>N1-CDF</th>
                <th>#Img</th>
                <th>Total</th>
            </tr>

            <?php foreach ($urClubs as $c): ?>
                <tr>
                    <td><?= $c['rang'] ?></td>
                    <td><?= esc($c['nom']) ?></td>
                    <td><?= number_format($c['N2']) ?></td>
                    <td><?= number_format($c['CDF']) ?></td>
                    <td><?= $c['total_images'] ?></td>
                    <td><strong><?= number_format($c['points']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </table>

    </div>

    <!-- ========================= -->
    <!-- 🟣 MATRICE -->
    <!-- ========================= -->
    <div class="section">

        <h2>🟣 Répartition compétitions</h2>

        <?php
        $clubs = [];
        foreach ($matrix as $comp => $data) {
            foreach ($data as $club => $p) {
                $clubs[$club] = true;
            }
        }
        $clubs = array_keys($clubs);
        sort($clubs);
        ?>

        <table>
            <tr>
                <th>Compétition</th>
                <?php foreach ($clubs as $club): ?><th><?= $club ?></th><?php endforeach; ?>
            </tr>

            <?php foreach ($matrix as $comp => $data): ?>
                <tr>
                    <td class="small"><?= esc($comp) ?></td>
                    <?php foreach ($clubs as $club): ?>
                        <td><?= isset($data[$club]) ? number_format($data[$club]) : '' ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="legend">
            <b>Légende :</b><br>
            <?php foreach ($clubs as $club): ?>
                <?= $club ?> → <?= $clubMap[$club] ?? '—' ?><br>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- ========================= -->
    <!-- 🔴 JUGEMENT -->
    <!-- ========================= -->
    <div class="section">

        <h2>🔥 Images clivantes</h2>
        <p class="small">Fortes divergences entre juges</p>

        <?php renderImages($jugement['top_clivantes'] ?? [], 'Clivante'); ?>

        <h2>🤝 Images consensuelles</h2>
        <p class="small">Accord global</p>

        <?php renderImages($jugement['top_consensuelles'] ?? [], 'Consensuelle'); ?>

        <h2>⚖️ Juge décisif</h2>

        <?php renderImages($jugement['top_juge_decisif'] ?? [], 'Juge'); ?>

    </div>

    <!-- ========================= -->
    <!-- ✨ WOW -->
    <!-- ========================= -->
    <div class="section">

        <h2>✨ WOW (16+)</h2>

        <?php
        $galleryWow = array_map(fn($i) => $i['photo_url'] ?? $i['thumb_url'], $wow);
        $json = htmlspecialchars(json_encode($galleryWow), ENT_QUOTES);
        ?>

        <div class="wow-grid">
            <?php foreach ($wow as $i => $img): ?>
                <img src="<?= $img['photo_url'] ?? $img['thumb_url'] ?>"
                    onclick='openLightboxList(<?= $json ?>,<?= $i ?>)'>
            <?php endforeach; ?>
        </div>

    </div>

</div>

<!-- ========================= -->
<!-- LIGHTBOX -->
<!-- ========================= -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">✕</span>
    <div class="lightbox-nav left" onclick="prevImage()">‹</div>
    <img id="lightbox-img">
    <div class="lightbox-nav right" onclick="nextImage()">›</div>
</div>

<script>
    let gallery = [],
        current = 0,
        autoPlay = null;

    function openLightboxList(list, index) {
        gallery = list;
        current = index;
        updateLightbox();
        document.getElementById('lightbox').style.display = 'flex';
    }

    function updateLightbox() {
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
        clearInterval(autoPlay);
    }
</script>

<?php
/* ========================= */
/* 🔴 FUNCTION */
/* ========================= */
function renderImages($images, $label)
{
    if (empty($images)) return;

    $gallery = array_map(fn($i) => $i['photo_url'] ?? $i['thumb_url'], $images);
    $json = htmlspecialchars(json_encode($gallery), ENT_QUOTES);

    echo "<div class='jugement-grid'>";

    foreach ($images as $i => $img) {

        $src = $img['photo_url'] ?? $img['thumb_url'];
        $title = esc($img['titre'] ?? '');

        echo "<div class='jugement-card'>";

        echo "<img src='$src' class='jugement-img'
onclick='openLightboxList($json,$i)'>";

        echo "<div class='jugement-content'>";
        echo "<div class='jugement-title'>$title</div>";
        echo "<div class='jugement-meta'>$label • {$img['competition_nom']}</div>";

        echo "<div class='badge-notes'>";
        foreach ($img['notes_array'] ?? [] as $n) {
            $color = '#f39c12';
            if ($n >= 16) $color = '#27ae60';
            elseif ($n <= 8) $color = '#e74c3c';
            echo "<span style='background:$color'>$n</span>";
        }
        echo "</div></div></div>";
    }

    echo "</div>";
}
?>

<?= $this->endSection() ?>