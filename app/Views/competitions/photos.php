<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?php

/* =========================
   PREP DATA
========================= */
$clubs = [];
$clubAuteurs = [];

foreach ($photos as $p) {

    $club = !empty(trim($p['club'] ?? '')) ? trim($p['club']) : '';
    $auteur = !empty(trim($p['auteur'] ?? '')) ? trim($p['auteur']) : '';

    if ($club) $clubs[$club] = true;

    if ($club && $auteur) {
        $clubAuteurs[$club][$auteur] = true;
    }
}

ksort($clubs);
foreach ($clubAuteurs as $c => $list) {
    ksort($clubAuteurs[$c]);
}

/* dossier */
$folder =
    'uploads/competitions/' .
    $competition['saison'] . '_' .
    str_pad((int)$competition['urs_id'], 2, '0', STR_PAD_LEFT) . '_' .
    $competition['numero'] . '_' .
    $competition['id'];


/* ============================================================
   🔎 FONCTION EAN
============================================================ */
function decodeEAN($ean)
{
    $ean = preg_replace('/\D/', '', $ean);

    if (strlen($ean) !== 12) {
        return null;
    }

    return [
        'ur'        => substr($ean, 0, 2),
        'club'      => substr($ean, 2, 4),
        'adherent'  => substr($ean, 6, 4),
    ];
}

/* ============================================================
   🔄 NORMALISATION PHOTOS
============================================================ */
foreach ($photos as &$p) {

    $ean = $p['ean'] ?? '';
    $decoded = decodeEAN($ean);

    // 👤 auteur
    if (empty(trim($p['auteur'] ?? '')) && $decoded) {
        $p['auteur'] = $decoded['adherent'];
    }

    // 🏢 club (UNIQUEMENT fallback UR22 - XX)
    $club = trim($p['club'] ?? '');

    $isFallback = (
        empty($club) ||
        preg_match('/^UR\s*22\s*-\s*XX$/i', $club)
    );

    if ($isFallback && $decoded) {
        $p['club'] = 'UR ' . $decoded['ur'] . ' - ' . $decoded['club'];
    }

    // sécurité affichage
    $p['auteur'] = $p['auteur'] ?: '—';
    $p['club']   = $p['club']   ?: '—';
    $p['titre']  = $p['titre']  ?: 'Sans titre';
}
unset($p);

?>

<div class="container">

    <h2><?= esc($competition['nom']) ?> (<?= count($photos) ?>)</h2>

    <!-- ========================= FILTERS ========================= -->

    <div class="toolbar">

        <input type="text" id="filter-ean" placeholder="EAN">
        <input type="text" id="filter-club" list="list-clubs" placeholder="Club">
        <input type="text" id="filter-auteur" list="list-auteurs" placeholder="Auteur">
        <input type="text" id="filter-saisie" placeholder="Photologic">

        <button id="btn-reset">Reset</button>
        <button id="btn-slideshow">▶️ Diaporama</button>

        <button id="sort-classement">Classement</button>
        <button id="sort-classement-inv">Classement ↓</button>
        <button id="sort-passage">Passage</button>
    </div>

    <!-- LIST CLUBS -->
    <datalist id="list-clubs">
        <?php foreach ($clubs as $club => $_): ?>
            <option value="<?= esc($club) ?>">
            <?php endforeach; ?>
    </datalist>

    <!-- LIST AUTEURS -->
    <datalist id="list-auteurs"></datalist>

    <script>
        const clubAuteurs = <?= json_encode($clubAuteurs) ?>;
    </script>

    <!-- ========================= GRID ========================= -->

    <div class="photo-grid">

        <?php foreach ($photos as $photo): ?>

            <?php
            $titre = !empty(trim($photo['titre'] ?? ''))
                ? $photo['titre']
                : 'Sans titre';

            $auteur = esc($photo['auteur']);

            $club = !empty(trim($photo['club'] ?? ''))
                ? $photo['club']
                : 'Club inconnu';

            $place = isset($photo['place']) ? (int)$photo['place'] : 0;
            $ean = $photo['ean'] ?? '';
            ?>

            <div class="photo-card"
                data-ean="<?= esc($ean) ?>"
                data-auteur="<?= esc($auteur) ?>"
                data-club="<?= esc($club) ?>"
                data-saisie="<?= (int)($photo['saisie'] ?? 0) ?>">

                <img
                    src="<?= competition_photo_url($competition, $ean) ?>"
                    class="photo-item"
                    data-titre="<?= esc($titre) ?>"
                    data-auteur="<?= esc($auteur) ?>"
                    data-club="<?= esc($club) ?>"
                    data-place="<?= $place ?>">

                <div class="photo-meta">
                    <strong><?= esc($titre) ?></strong>
                    <?php if ($place > 0): ?> (<?= $place ?>°) <?php endif; ?>
                    <br>
                    <?= esc($auteur) ?>
                    <?php if ($auteur === 'Auteur inconnu'): ?>
                        <span style="color:red;">⚠</span>
                    <?php endif; ?>
                    <br>
                    <small><?= esc($club) ?></small>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<!-- ========================= STYLE ========================= -->

<style>
    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }

    .photo-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        cursor: pointer;
    }
</style>

<!-- ========================= SLIDESHOW ========================= -->

<div id="slideshow" style="display:none;position:fixed;inset:0;background:black;z-index:9999;justify-content:center;align-items:center;flex-direction:column;">
    <div id="slide-counter" style="color:white;position:absolute;top:10px;right:20px;"></div>
    <img id="slide-img" style="max-width:95%;max-height:85%;">
    <div id="slide-info" style="color:white;margin-top:10px;"></div>
</div>

<!-- ========================= SCRIPT ========================= -->

<script>
    console.log("SCRIPT CHARGÉ");

    document.addEventListener('DOMContentLoaded', function() {

        /* =========================
           ELEMENTS
        ========================= */

        const grid = document.querySelector('.photo-grid');

        const filterEAN = document.getElementById('filter-ean');
        const filterAuteur = document.getElementById('filter-auteur');
        const filterClub = document.getElementById('filter-club');
        const filterSaisie = document.getElementById('filter-saisie');

        const btnReset = document.getElementById('btn-reset');
        const btnSlide = document.getElementById('btn-slideshow');

        const btnSortClassement = document.getElementById('sort-classement');
        const btnSortClassementInv = document.getElementById('sort-classement-inv');
        const btnSortPassage = document.getElementById('sort-passage');

        const slideshow = document.getElementById('slideshow');
        const slideImg = document.getElementById('slide-img');
        const slideInfo = document.getElementById('slide-info');
        const slideCounter = document.getElementById('slide-counter');

        let index = 0;

        if (!grid) {
            console.error("GRID INTROUVABLE");
            return;
        }

        /* =========================
           TOOLS
        ========================= */

        function normalize(str) {
            return (str || "").toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");
        }

        function getPlace(card) {
            const val = card.querySelector('.photo-item')?.dataset.place;
            const num = parseInt(val);
            return isNaN(num) || num === 0 ? 9999 : num;
        }

        function sortGrid(compareFn) {
            const cards = Array.from(grid.querySelectorAll('.photo-card'));
            cards.sort(compareFn);
            cards.forEach(card => grid.appendChild(card));
        }

        /* =========================
           FILTER
        ========================= */

        function filterPhotos() {

            const ean = normalize(filterEAN.value);
            const auteur = normalize(filterAuteur.value);
            const club = normalize(filterClub.value);
            const saisie = filterSaisie.value;

            grid.querySelectorAll('.photo-card').forEach(card => {

                let show = true;

                if (ean && !normalize(card.dataset.ean).includes(ean)) show = false;

                if (auteur && auteur.length > 0 &&
                    !normalize(card.dataset.auteur).includes(auteur)) show = false;

                if (club && !normalize(card.dataset.club).includes(club)) show = false;

                if (saisie && card.dataset.saisie != saisie) show = false;

                card.style.display = show ? "block" : "none";
            });
        }

        /* =========================
           EVENTS FILTER
        ========================= */

        [filterEAN, filterAuteur, filterClub, filterSaisie]
        .forEach(i => i.addEventListener('input', filterPhotos));

        /* =========================
           RESET (FIX BUG AUTEUR)
        ========================= */

        btnReset.addEventListener('click', () => {

            filterEAN.value = "";
            filterAuteur.value = "";
            filterClub.value = "";
            filterSaisie.value = "";

            // 🔥 force re-trigger (important)
            filterEAN.dispatchEvent(new Event('input'));
            filterAuteur.dispatchEvent(new Event('input'));
            filterClub.dispatchEvent(new Event('input'));
            filterSaisie.dispatchEvent(new Event('input'));

            updateAuteurList();

            // UX
            filterAuteur.blur();
            filterClub.blur();
        });

        /* =========================
           AUTEURS DYNAMIQUES
        ========================= */

        function updateAuteurList() {

            const club = filterClub.value;
            const datalist = document.getElementById('list-auteurs');

            datalist.innerHTML = "";

            if (!club || !clubAuteurs[club]) {

                let all = {};

                Object.values(clubAuteurs).forEach(list => {
                    Object.keys(list).forEach(a => all[a] = true);
                });

                Object.keys(all).sort().forEach(a => {
                    let opt = document.createElement('option');
                    opt.value = a;
                    datalist.appendChild(opt);
                });

                return;
            }

            Object.keys(clubAuteurs[club]).forEach(a => {
                let opt = document.createElement('option');
                opt.value = a;
                datalist.appendChild(opt);
            });
        }

        filterClub.addEventListener('input', () => {
            filterAuteur.value = "";
            updateAuteurList();
            filterPhotos();
        });

        updateAuteurList();

        /* =========================
           SLIDESHOW
        ========================= */

        function getSlides() {
            return Array.from(grid.querySelectorAll('.photo-card'))
                .filter(c => c.style.display !== 'none')
                .map(c => c.querySelector('.photo-item'));
        }

        function openSlide(i) {
            index = i;
            slideshow.style.display = "flex";
            showSlide();
        }

        function showSlide() {
            const slides = getSlides();
            const img = slides[index];
            if (!img) return;

            slideImg.src = img.src;
            slideCounter.innerText = (index + 1) + " / " + slides.length;

            slideInfo.innerHTML =
                "<strong>" + img.dataset.titre + "</strong><br>" +
                img.dataset.auteur + " — " + img.dataset.club +
                (img.dataset.place > 0 ? " (" + img.dataset.place + "°)" : "");
        }

        function next() {
            index = (index + 1) % getSlides().length;
            showSlide();
        }

        function prev() {
            index = (index - 1 + getSlides().length) % getSlides().length;
            showSlide();
        }

        grid.querySelectorAll('.photo-item').forEach(img => {
            img.addEventListener('click', () => {
                const slides = getSlides();
                const i = slides.indexOf(img);
                if (i >= 0) openSlide(i);
            });
        });

        btnSlide.addEventListener('click', () => {
            if (!getSlides().length) return;
            openSlide(0);
        });

        document.addEventListener('keydown', e => {
            if (slideshow.style.display !== 'flex') return;

            if (e.key === "Escape") slideshow.style.display = "none";
            if (e.key === "ArrowRight") next();
            if (e.key === "ArrowLeft") prev();
        });

        slideshow.addEventListener('click', () => {
            slideshow.style.display = "none";
        });

        /* =========================
           SORTING
        ========================= */

        btnSortClassement.addEventListener('click', () => {
            sortGrid((a, b) => getPlace(a) - getPlace(b));
        });

        btnSortClassementInv.addEventListener('click', () => {
            sortGrid((a, b) => getPlace(b) - getPlace(a));
        });

        btnSortPassage.addEventListener('click', () => {
            sortGrid((a, b) => {
                return (parseInt(a.dataset.saisie) || 0) - (parseInt(b.dataset.saisie) || 0);
            });
        });

    });
</script>



<?= $this->endSection() ?>