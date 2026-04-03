<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>

<h2>Import compétition COPAINS</h2>

<?php if (empty($competitions)): ?>

<p>Aucune compétition disponible.</p>

<?php else: ?>

<!-- =========================
     PROFIL (optionnel)
========================== -->
<?php
    $profile = env('copain.profile', 'regional');
    $userUr = env('copain.ur', '');
    ?>

<div style="margin-bottom:10px;">
    Profil : <b><?= esc($profile) ?></b>
    <?php if ($profile === 'regional'): ?>
    — UR<?= esc($userUr) ?>
    <?php endif; ?>
</div>

<!-- =========================
     FILTRE TYPE
========================== -->
<div class="filters" style="margin-bottom:10px;">

    <strong>Type :</strong>

    <button onclick="filterByType('')">Toutes</button>
    <button onclick="filterByType('N')">National</button>
    <button onclick="filterByType('R')">Régional</button>

</div>

<!-- =========================
     FILTRE UR
========================== -->
<div class="filters" style="margin-bottom:20px;">

    <strong>Filtre UR :</strong>

    <button onclick="filterByUR('')">Toutes</button>

    <?php
        $urs = array_unique(array_column($competitions, 'urs_id'));
        sort($urs);
        ?>

    <?php foreach ($urs as $ur): ?>
    <?php if ($ur !== ''): ?>
    <button onclick="filterByUR('<?= esc($ur) ?>')">
        UR<?= esc($ur) ?>
    </button>
    <?php endif; ?>
    <?php endforeach; ?>

</div>

<!-- =========================
     NATIONAL
========================== -->
<h3>National</h3>

<div class="cards-container">

    <?php foreach ($competitions as $c): ?>
    <?php if ($c['type_code'] === 'N'): ?>

    <?= view('import/card', [
                    'c' => $c,
                    'label' => $c['label']
                ]) ?>

    <?php endif; ?>
    <?php endforeach; ?>

</div>

<!-- =========================
     REGIONAL
========================== -->
<h3 style="margin-top:30px;">Régional</h3>

<div class="cards-container">

    <?php foreach ($competitions as $c): ?>
    <?php if ($c['type_code'] === 'R'): ?>

    <?= view('import/card', [
                    'c' => $c,
                    'label' => $c['label']
                ]) ?>

    <?php endif; ?>
    <?php endforeach; ?>

</div>

<?php endif; ?>

<!-- =========================
     JS FILTRES
========================== -->
<script>
function filterByType(type) {

    document.querySelectorAll('.card-import').forEach(card => {

        const t = card.dataset.type;

        if (!type || t === type) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }

    });
}

function filterByUR(ur) {

    document.querySelectorAll('.card-import').forEach(card => {

        const cardUR = card.dataset.ur;

        if (!ur || cardUR === String(ur)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }

    });
}
</script>

<!-- =========================
     JS IMPORT (PROGRESS CARD)
========================== -->
<script>
const BASE_URL = "<?= base_url() ?>";

function startImport(id, type, folder) {

    const box = document.getElementById('progress-' + id);
    const bar = document.getElementById('bar-' + id);
    const text = document.getElementById('text-' + id);
    const btn = document.getElementById('btn-' + id);

    box.style.display = 'block';
    bar.style.width = "0%";
    text.innerHTML = "Initialisation...";
    btn.disabled = true;

    fetch(BASE_URL + "/import/start/" + id +
            "?type=" + encodeURIComponent(type) +
            "&folder=" + encodeURIComponent(folder || ""), {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }
        )
        .then(() => runStep(id))
        .catch(() => {
            text.innerHTML = "❌ Erreur start";
            btn.disabled = false;
        });
}

function runStep(id) {

    const bar = document.getElementById('bar-' + id);
    const text = document.getElementById('text-' + id);

    fetch(BASE_URL + "/import/step/" + id, {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(r => r.json())
        .then(s => {

            bar.style.width = (s.progress || 0) + "%";

            text.innerHTML =
                "<b>" + (s.step || "...") + "</b><br>" +
                (s.progress || 0) + "%";

            if (s.status !== "done") {
                setTimeout(() => runStep(id), 500);
            } else {
                text.innerHTML = "✅ Terminé";

                setTimeout(() => {
                    window.location.href = BASE_URL + "/competitions/" + id + "/photos";
                }, 800);
            }
        })
        .catch(() => {
            text.innerHTML = "❌ Erreur step";
        });
}
</script>

<?= $this->endSection() ?>