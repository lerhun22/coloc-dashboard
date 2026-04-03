<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?php

/* =========================
   NORMALISATION
========================= */
function getVal($arr, $keys, $default = '—')
{
    foreach ($keys as $k) {
        if (isset($arr[$k]) && $arr[$k] !== '') return $arr[$k];
    }
    return $default;
}

/* =========================
   PREP DATA
========================= */
$clubs = [];
$clubAuteurs = [];

foreach ($photos as $p) {

    $club = trim(getVal($p, ['club', 'club_nom', 'clubName'], ''));
    $auteur = trim(getVal($p, ['auteur', 'author', 'nom_auteur'], ''));

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
    str_pad($competition['urs_id'], 2, '0', STR_PAD_LEFT) . '_' .
    $competition['numero'] . '_' .
    $competition['id'];
?>

<div class="container">

    <h2><?= esc($competition['nom']) ?> (<?= count($photos) ?>)</h2>

    <!-- =========================
     FILTERS
========================= -->

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

    <!-- =========================
     GRID
========================= -->

    <div class="photo-grid">

        <?php foreach ($photos as $photo): ?>

            <?php
            $titre = getVal($photo, ['titre', 'nom', 'title']);
            $auteur = getVal($photo, ['auteur', 'author', 'nom_auteur']);
            $club = getVal($photo, ['club', 'club_nom']);
            $place = getVal($photo, ['place', 'classement'], null);
            $ean = getVal($photo, ['ean', 'code', 'id'], '');
            ?>

            <div class="photo-card" data-ean="<?= esc($ean) ?>" data-auteur="<?= esc($auteur) ?>"
                data-club="<?= esc($club) ?>" data-saisie="<?= (int)($photo['saisie'] ?? 0) ?>">

                <img 
                    src="<?= competition_photo_url($competition, $ean) ?>"
                    class="photo-item"
                    data-titre="<?= esc($titre) ?>"
                    data-auteur="<?= esc($auteur) ?>"
                    data-club="<?= esc($club) ?>"
                    data-place="<?= (int)($place ?? 0) ?>"
                >

                <div class="photo-meta">
                    <strong><?= esc($titre) ?></strong>
                    <?php if ($place): ?> (<?= $place ?>°) <?php endif; ?>
                    <br>
                    <?= esc($auteur) ?><br>
                    <small><?= esc($club) ?></small>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<!-- =========================
     STYLE
========================= -->

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

    #slideshow {
        display: none;
        position: fixed;
        inset: 0;
        background: black;
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    #slide-img {
        max-width: 95%;
        max-height: 85%;
    }

    #slide-info {
        color: white;
        margin-top: 10px;
    }

    #slide-counter {
        position: absolute;
        top: 10px;
        right: 20px;
        color: white;
    }

    #filter-club {
        width: 260px;
    }
</style>

<!-- =========================
     SLIDESHOW
========================= -->

<div id="slideshow">
    <div id="slide-counter"></div>
    <img id="slide-img">
    <div id="slide-info"></div>
</div>

<!-- =========================
     SCRIPT
========================= -->

<script>
document.addEventListener('DOMContentLoaded', function() {

    const grid = document.querySelector('.photo-grid');

    const filterEAN = document.getElementById('filter-ean');
    const filterAuteur = document.getElementById('filter-auteur');
    const filterClub = document.getElementById('filter-club');
    const filterSaisie = document.getElementById('filter-saisie');

    const btnReset = document.getElementById('btn-reset');
    const btnSlide = document.getElementById('btn-slideshow');

    const slideshow = document.getElementById('slideshow');
    const slideImg = document.getElementById('slide-img');
    const slideInfo = document.getElementById('slide-info');
    const slideCounter = document.getElementById('slide-counter');

    let index = 0;

    /* ============================================================
       NORMALIZE
    ============================================================ */

    function normalize(str) {
        return (str || "").toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
    }

    /* ============================================================
       FILTERS
    ============================================================ */

    function filterPhotos() {

        const ean = normalize(filterEAN.value);
        const auteur = normalize(filterAuteur.value);
        const club = normalize(filterClub.value);
        const saisie = filterSaisie.value;

        grid.querySelectorAll('.photo-card').forEach(card => {

            let show = true;

            if (ean && !normalize(card.dataset.ean).includes(ean)) show = false;
            if (auteur && !normalize(card.dataset.auteur).includes(auteur)) show = false;
            if (club && !normalize(card.dataset.club).includes(club)) show = false;
            if (saisie && card.dataset.saisie != saisie) show = false;

            card.style.display = show ? "block" : "none";
        });
    }

    [filterEAN, filterAuteur, filterClub, filterSaisie]
        .forEach(i => i.addEventListener('input', filterPhotos));

    btnReset.addEventListener('click', () => {
        filterEAN.value = "";
        filterAuteur.value = "";
        filterClub.value = "";
        filterSaisie.value = "";
        filterPhotos();
        updateAuteurList();
    });

    /* ============================================================
       CLUB → AUTEURS
    ============================================================ */

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

    /* ============================================================
       TRI
    ============================================================ */

    function sortPhotos(mode) {

        const cards = Array.from(grid.querySelectorAll('.photo-card'));

        cards.sort((a, b) => {

            let valA, valB;

            if (mode === "classement") {
                valA = parseInt(a.querySelector('.photo-item').dataset.place || 999);
                valB = parseInt(b.querySelector('.photo-item').dataset.place || 999);
                return valA - valB;
            }

            if (mode === "classement_inv") {
                valA = parseInt(a.querySelector('.photo-item').dataset.place || 0);
                valB = parseInt(b.querySelector('.photo-item').dataset.place || 0);
                return valB - valA;
            }

            if (mode === "passage") {
                valA = parseInt(a.dataset.ean || 0);
                valB = parseInt(b.dataset.ean || 0);
                return valA - valB;
            }

            return 0;
        });

        cards.forEach(card => grid.appendChild(card));
    }

    document.getElementById("sort-classement")
        .addEventListener("click", () => sortPhotos("classement"));

    document.getElementById("sort-classement-inv")
        .addEventListener("click", () => sortPhotos("classement_inv"));

    document.getElementById("sort-passage")
        .addEventListener("click", () => sortPhotos("passage"));

    /* ============================================================
       SLIDESHOW
    ============================================================ */

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

    /* ============================================================
       CLICK IMAGE = ZOOM
    ============================================================ */

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

});
</script>

<?= $this->endSection() ?>
