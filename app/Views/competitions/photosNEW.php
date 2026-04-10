<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?php

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


/* ============================================================
   📁 DOSSIER IMAGES
============================================================ */
$folder =
    'uploads/competitions/' .
    $competition['saison'] . '_' .
    str_pad((string)($competition['urs_id']), 2, '0', STR_PAD_LEFT) . '_' .
    $competition['numero'] . '_' .
    $competition['id'];

?>

<!-- ============================================================
     ✅ CONTENU (SANS CASSE HEADER)
============================================================ -->

<div class="container mt-3">

    <div class="competition-toolbar">

        <div class="toolbar-top">
            <h1 class="competition-title">
                <?= esc($competition['nom']) ?>
                <span class="competition-count">
                    (<?= count($photos) ?>)
                </span>
            </h1>
        </div>

        <div class="toolbar-row">

            <div class="toolbar-sort">
                <button data-sort="saisie">Photologic</button>
                <button data-sort="passage">Passage</button>
                <button data-sort="place">Classement</button>
                <button data-sort="place" data-desc="1">
                    Classement inversé
                </button>
            </div>

            <div class="toolbar-filters">
                <input type="text" id="filter-ean" placeholder="EAN">
                <input type="text" id="filter-auteur" placeholder="Auteur">
                <input type="text" id="filter-club" placeholder="Club">
                <input type="text" id="filter-saisie" placeholder="Photologic">
            </div>

        </div>

    </div>

    <div class="photo-grid">

        <?php foreach ($photos as $photo): ?>

        <div class="photo-card" data-ean="<?= esc(strtolower($photo['ean'] ?? '')) ?>"
            data-auteur="<?= esc(strtolower($photo['auteur'] ?? '')) ?>"
            data-club="<?= esc(strtolower($photo['club'] ?? '')) ?>" data-saisie="<?= (int)($photo['saisie'] ?? 0) ?>"
            data-passage="<?= (int)($photo['passage'] ?? 0) ?>" data-place="<?= (int)($photo['place'] ?? 9999) ?>">

            <a href="<?= base_url($folder . '/photos/' . $photo['ean'] . '.jpg') ?>" target="_blank">

                <img src="<?= base_url($folder . '/photos/' . $photo['ean'] . '.jpg') ?>" loading="lazy"
                    alt="<?= esc($photo['titre']) ?>">

            </a>

            <div class="photo-meta">

                <div class="photo-header">

                    <div class="photo-title">
                        <?= esc($photo['titre']) ?>
                    </div>

                    <?php if (isset($photo['place']) && $photo['place'] > 0): ?>
                    <span class="photo-place">
                        <?= $photo['place'] ?>°
                    </span>
                    <?php endif; ?>

                </div>

                <div class="photo-author">
                    <?= esc($photo['auteur']) ?>
                </div>

                <div class="photo-club">
                    <?= esc($photo['club']) ?>
                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

</div>


<style>
.competition-toolbar {
    margin-bottom: 12px;
}

.toolbar-top {
    margin-bottom: 6px;
}

.toolbar-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-sort {
    display: flex;
    gap: 6px;
}

.toolbar-sort button {
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
}

.toolbar-filters {
    display: flex;
    gap: 8px;
}

.competition-title {
    font-size: 22px;
}

.competition-count {
    color: #666;
    font-size: 16px;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.photo-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 6px;
}

.photo-meta {
    margin-top: 6px;
    font-size: 13px;
}

.photo-title {
    font-weight: 600;
}

.photo-author {
    color: #444;
}

.photo-club {
    color: #777;
    font-size: 12px;
}

.photo-header {
    display: flex;
    justify-content: space-between;
}

.photo-place {
    font-weight: bold;
}
</style>


<script>
const filterEAN = document.getElementById('filter-ean')
const filterAuteur = document.getElementById('filter-auteur')
const filterClub = document.getElementById('filter-club')
const filterSaisie = document.getElementById('filter-saisie')

const grid = document.querySelector('.photo-grid')

function filterPhotos() {

    const ean = filterEAN.value.toLowerCase()
    const auteur = filterAuteur.value.toLowerCase()
    const club = filterClub.value.toLowerCase()
    const saisie = filterSaisie.value

    const cards = document.querySelectorAll('.photo-card')

    cards.forEach(card => {

        const cardEAN = card.dataset.ean
        const cardAuteur = card.dataset.auteur
        const cardClub = card.dataset.club
        const cardSaisie = card.dataset.saisie

        let show = true

        if (ean && !cardEAN.includes(ean)) show = false
        if (auteur && !cardAuteur.includes(auteur)) show = false
        if (club && !cardClub.includes(club)) show = false
        if (saisie && cardSaisie != saisie) show = false

        card.style.display = show ? "block" : "none"

    })
}

filterEAN.addEventListener('keyup', filterPhotos)
filterAuteur.addEventListener('keyup', filterPhotos)
filterClub.addEventListener('keyup', filterPhotos)
filterSaisie.addEventListener('keyup', filterPhotos)


const sortButtons = document.querySelectorAll('[data-sort]')

sortButtons.forEach(button => {

    button.addEventListener('click', function() {

        const type = this.dataset.sort
        const desc = this.dataset.desc == "1"

        let cards = Array.from(grid.querySelectorAll('.photo-card'))

        cards.sort((a, b) => {

            let aVal = parseInt(a.dataset[type]) || 0
            let bVal = parseInt(b.dataset[type]) || 0

            return desc ? bVal - aVal : aVal - bVal
        })

        cards.forEach(card => grid.appendChild(card))
    })
})
</script>

<?= $this->endSection() ?>