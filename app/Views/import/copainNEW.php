<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?= $this->section('styles') ?>
<style>
    .container {
        max-width: 1400px;
    }

    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
    }

    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .filter-bar button.active {
        background: #1976d2;
        color: #fff;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }

    .kpi {
        background: #fff;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
    }

    .kpi-value {
        font-size: 22px;
        font-weight: bold;
    }

    .kpi-label {
        font-size: 12px;
        color: #777;
    }

    .progress-global {
        margin-bottom: 20px;
    }

    .progress-label {
        font-size: 13px;
        margin-bottom: 5px;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: #eee;
        border-radius: 4px;
    }

    .progress-fill {
        height: 100%;
        background: #1976d2;
    }
</style>
<?= $this->endSection() ?>

<h2>Import compétition COPAINS</h2>

<div class="kpi-grid">

    <div class="kpi">
        <div class="kpi-value"><?= $stats['competitions_total'] ?? 0 ?></div>
        <div class="kpi-label">Compétitions</div>
    </div>

    <div class="kpi">
        <div class="kpi-value"><?= $stats['competitions_imported'] ?? 0 ?></div>
        <div class="kpi-label">Importées</div>
    </div>

    <div class="kpi">
        <div class="kpi-value"><?= $stats['photos_total'] ?? 0 ?></div>
        <div class="kpi-label">Photos</div>
    </div>

    <div class="kpi">
        <div class="kpi-value"><?= $stats['clubs_total'] ?? 0 ?></div>
        <div class="kpi-label">Clubs actifs</div>
    </div>

</div>

<?php
$total = $stats['competitions_total'] ?? 1;
$imported = $stats['competitions_imported'] ?? 0;
$percent = round(($imported / $total) * 100);
?>

<div class="progress-global">
    <div class="progress-label">
        Progression UR22 : <?= $percent ?>%
    </div>

    <div class="progress-bar">
        <div class="progress-fill" style="width: <?= $percent ?>%"></div>
    </div>
</div>

<!-- 🎯 FILTRES -->
<div class="filter-bar">

    <strong>Type :</strong>
    <button onclick="showAll()">Tous</button>
    <button onclick="showNational()">National</button>
    <button onclick="showRegionalAll()">Régional</button>

    <strong>UR :</strong>
    <button onclick="showRegionalAll()">Toutes</button>

    <?php foreach ($urs as $u): ?>
        <button onclick="showRegionalUR('<?= $u ?>')">
            UR <?= $u ?>
        </button>
    <?php endforeach; ?>

</div>



<!-- 🟢 REGIONAL -->
<h3>Régional</h3>
<div class="card-grid">
    <?php foreach ($rcompetitions as $c): ?>
        <?= view('import/card', ['c' => $c]) ?>
    <?php endforeach ?>
</div>

<!-- 🔵 NATIONAL -->
<h3>National</h3>
<div class="card-grid">
    <?php foreach ($competitions as $c): ?>
        <?= view('import/card', ['c' => $c]) ?>
    <?php endforeach ?>
</div>



<!-- 🔥 ROUTES GLOBAL -->

<script>
    window.BASE_URL = "<?= base_url() ?>";
</script>


<script>
    window.ROUTES = <?= json_encode($routes) ?>;
</script>

<script>
    // =========================
    // 🎯 STATE
    // =========================
    let currentMode = null;
    let currentUR = null;

    // =========================
    // 💾 STORAGE
    // =========================
    function saveFilters() {
        localStorage.setItem('copainFilters', JSON.stringify({
            mode: currentMode,
            ur: currentUR
        }));
    }

    function loadFilters() {
        const saved = localStorage.getItem('copainFilters');

        if (saved) {
            const f = JSON.parse(saved);
            currentMode = f.mode;
            currentUR = f.ur;
        }
    }

    // =========================
    // 🎯 FILTER
    // =========================
    function applyFilters() {

        document.querySelectorAll('.card-import').forEach(card => {

            const isNational = card.classList.contains('national');
            const isRegional = card.classList.contains('regional');
            const ur = card.dataset.ur;

            let show = false;

            if (currentMode === 'all') {
                show = true;
            } else if (currentMode === 'national') {
                show = isNational;
            } else if (currentMode === 'regional') {

                if (isRegional) {
                    show = (!currentUR || ur === currentUR);
                }
            }

            card.style.display = show ? 'block' : 'none';
        });
    }

    // =========================
    // 🎨 ACTIVE BUTTON
    // =========================
    function setActiveButton(type, ur = null) {

        document.querySelectorAll('.filter-bar button')
            .forEach(b => b.classList.remove('active'));

        if (type === 'all') {
            document.querySelector('[onclick="showAll()"]').classList.add('active');
        } else if (type === 'national') {
            document.querySelector('[onclick="showNational()"]').classList.add('active');
        } else if (type === 'regional' && !ur) {
            document.querySelector('[onclick="showRegionalAll()"]').classList.add('active');
        } else if (type === 'regional' && ur) {
            document.querySelector(`[onclick="showRegionalUR('${ur}')"]`)?.classList.add('active');
        }
    }

    // =========================
    // 🎯 ACTIONS
    // =========================
    function showAll() {
        currentMode = 'all';
        currentUR = null;
        saveFilters();
        setActiveButton('all');
        applyFilters();
    }

    function showNational() {
        currentMode = 'national';
        currentUR = null;
        saveFilters();
        setActiveButton('national');
        applyFilters();
    }

    function showRegionalAll() {
        currentMode = 'regional';
        currentUR = null;
        saveFilters();
        setActiveButton('regional');
        applyFilters();
    }

    function showRegionalUR(ur) {
        currentMode = 'regional';
        currentUR = ur;
        saveFilters();
        setActiveButton('regional', ur);
        applyFilters();
    }

    // =========================
    // 🚀 INIT
    // =========================
    document.addEventListener('DOMContentLoaded', () => {

        loadFilters();

        // 🔥 fallback si rien en storage
        if (!currentMode) {
            currentMode = 'regional';
            currentUR = "<?= $defaultUR ?>";
        }

        console.log("INIT:", currentMode, currentUR);

        setActiveButton(currentMode, currentUR);
        applyFilters();
    });

    // =========================
    // 📦 IMPORT DB
    // =========================
    window.startImportDB = function(id, type) {

        const text = document.getElementById(`text-${id}`);

        text.innerHTML = "⏳ Import DB en cours...";

        fetch(`${ROUTES.import.db}/${id}?type=${type}`)
            .then(res => res.json())
            .then(data => {

                console.log("DATA =", data);

                // 🔥 on se base sur le BACKEND uniquement pour l'affichage
                let label = (data.urs_id === null) ?
                    'National' :
                    'Régional';

                text.innerHTML =
                    data.status === 'ok' ?
                    `✅ DB importée (${label})` :
                    `❌ Erreur import`;

            })
            .catch(err => {
                console.error(err);
                text.innerHTML = "❌ Exception";
            });
    };
</script>

<?= $this->endSection() ?>