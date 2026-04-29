<?= $this->extend('layout/default') ?>

<?php $full = true; ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('css/jugement.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .zoom-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .zoom-overlay.active {
        display: flex;
    }

    #zoomImage {
        max-width: 90%;
        max-height: 90%;
    }
</style>

<!-- 🔥 CONFIG JS CENTRALISÉE -->
<script>
    window.APP = {
        baseUrl: "<?= base_url() ?>",
        photosUrl: "<?= $photosUrl ?>",
        competitionId: <?= $competition['id'] ?>,
        nbJuges: <?= $nb_juges ?>
    };

    console.log("APP CONFIG 👉", window.APP);
</script>

<div class="jugement-container">

    <!-- =========================
         FILTRES
    ========================== -->

    <div class="jugement-filters">

        <div class="filters-top">
            <div class="filters-left">

                <div>
                    Code barre
                    <input type="text" id="filter-ean">
                </div>

                <div>
                    Passage
                    <input type="number" id="filter-passage">
                </div>

            </div>
        </div>

        <div class="filters-info">

            <div class="filters-state">

                <label>
                    <input type="checkbox" id="filter-pending" class="filter-checkbox" checked>
                    Reste
                </label>

                <label>
                    <input type="checkbox" id="filter-partial" class="filter-checkbox" checked>
                    Partiel
                </label>

                <label>
                    <input type="checkbox" id="filter-done" class="filter-checkbox" checked>
                    Jugés
                </label>

            </div>

        </div>

        <div class="filters-right">
            <span id="photo-position"><?= $position ?></span>
            /
            <span id="photo-total"><?= $total ?></span>
        </div>

    </div>

    <!-- =========================
         GRILLE PHOTOS
    ========================== -->

    <div class="jugement-grid">

        <?php foreach ($photos as $p): ?>

            <?php
            if ($p['nb_notes'] == 0) {
                $class = 'pending';
            } elseif ($p['nb_notes'] < $nb_juges) {
                $class = 'partial';
            } else {
                $class = 'done';
            }
            ?>

            <div class="photo-tile <?= $class ?>"
                data-id="<?= $p['photo_id'] ?>"
                data-ean="<?= $p['ean'] ?>"
                data-passage="<?= $p['passage'] ?>">

                <?= $p['passage'] ?>

            </div>

        <?php endforeach; ?>

    </div>

    <!-- =========================
         MAIN
    ========================== -->

    <div class="jugement-main">

        <!-- PHOTO -->
        <div class="jugement-photo">
            <?php if (!empty($photo)): ?>



                <img
                    src="<?= $photosUrl . '/' . $photo['ean'] . '.jpg' ?>"
                    class="photo-juge"
                    id="photo-active">
            <?php endif; ?>

        </div>

        <!-- NOTES -->
        <div class="jugement-notes">

            <h3>Notes</h3>

            <?php foreach ($juges as $i => $j): ?>

                <div>
                    Juge <?= $i + 1 ?> :
                    <?= esc($j['nom']) ?>
                </div>

                <input type="number"
                    class="note-input"
                    data-juge="<?= $j['id'] ?>"
                    min="6"
                    max="20">

            <?php endforeach; ?>

            <hr>

            <div>
                Total
                <input type="number" id="total" readonly>
            </div>

        </div>

        <!-- INFOS -->
        <div class="jugement-infos">

            <h3><?= esc($competition['nom']) ?></h3>

            <p>
                <strong>Titre :</strong>
                <span id="photo-titre"><?= $photo['titre'] ?></span>
            </p>

            <p>
                <strong>EAN :</strong>
                <span id="photo-ean"><?= $photo['ean'] ?></span>
            </p>

            <p>
                <strong>Dossier :</strong><br>
                <span class="photo-path"><?= esc($photosPath) ?></span>
            </p>

            <div class="jugement-stats">

                <div class="stat">
                    <div class="stat-value" id="count-done">0</div>
                    <div class="stat-label">Jugées</div>
                </div>

                <div class="stat">
                    <div class="stat-value" id="count-partial">0</div>
                    <div class="stat-label">Partielles</div>
                </div>

                <div class="stat">
                    <div class="stat-value" id="count-pending">0</div>
                    <div class="stat-label">Restantes</div>
                </div>

                <div class="stat-total">
                    /
                    <span id="count-total">0</span>
                </div>

            </div>

            <!-- DISQUALIFY -->
            <div class="disqualify-box">
                <button id="btn-disqualify"
                    class="btn-disqualify"
                    onclick="toggleDisqualify()">
                    Disqualifier
                </button>
            </div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>

<!-- ZOOM -->
<div id="zoomOverlay" class="zoom-overlay">
    <img id="zoomImage">
</div>

<script src="<?= base_url('js/jugement.js?v=' . time()) ?>"></script>

<?= $this->endSection() ?>