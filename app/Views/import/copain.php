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

    .badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
    }

    .badge-regional {
        background: #e7f1ff;
        color: #1d4ed8;
        border: 1px solid #1d4ed8;
    }

    .badge-national {
        background: #f3e8ff;
        color: #7e22ce;
        border: 1px solid #7e22ce;
    }
</style>
<?= $this->endSection() ?>

<?php
$isNational = ($userProfil === 'CommissaireNational');
$isRegional = ($userProfil === 'CommissaireRegional');
$defaultUR = str_pad($userUR ?? 0, 2, '0', STR_PAD_LEFT);
?>

<h2>Import compétition COPAINS</h2>

<div class="filter-bar">

    <strong>Type :</strong>

    <?php if ($isNational): ?>
        <button onclick="setType(null)">Tous</button>
        <button onclick="setType('national')">National</button>
        <button onclick="setType('regional')">Régional</button>
    <?php else: ?>
        <span>Régional uniquement</span>
    <?php endif; ?>

    <strong>UR :</strong>

    <select id="filter-ur" onchange="applyFilters()">
        <?php if ($isNational): ?>
            <option value="">Toutes</option>
        <?php endif; ?>

        <?php foreach ($urs as $ur): ?>
            <?php $u = str_pad($ur, 2, '0', STR_PAD_LEFT); ?>
            <option value="<?= $u ?>" <?= $u == $defaultUR ? 'selected' : '' ?>>
                UR <?= $u ?>
            </option>
        <?php endforeach; ?>
    </select>

</div>

<h3>National</h3>
<div class="card-grid">
    <?php foreach ($competitions as $c): ?>
        <?= view('import/card', ['c' => $c, 'label' => 'National']) ?>
    <?php endforeach ?>
</div>

<h3>Régional</h3>
<div class="card-grid">
    <?php foreach ($rcompetitions as $c): ?>
        <?= view('import/card', ['c' => $c, 'label' => 'Régional']) ?>
    <?php endforeach ?>
</div>

<script>
    let running = null;
    let currentType = null;

    const labels = {
        download_json: "Chargement données",
        download_zip: "Téléchargement images",
        extract_zip: "Extraction",
        move_files: "Organisation fichiers",
        thumbs: "Miniatures",
        done: "Terminé"
    };

    function tickCard(id) {
        fetch("<?= base_url('import/step') ?>/" + id)
            .then(r => r.json())
            .then(s => {

                const bar = document.getElementById("bar-" + id);
                const text = document.getElementById("text-" + id);

                if (bar) bar.style.width = s.progress + "%";

                if (text && s.status !== "done") {
                    const label = labels[s.step] || s.step;
                    text.innerHTML = label + " — " + s.progress + "%";
                }

                if (s.status === "done") {

                    running = null;

                    if (text) {
                        text.innerHTML = "✅ Import terminé";
                    }

                    setTimeout(() => {
                        window.location =
                            "<?= base_url('competitions/') ?>" + id + '/photos';
                    }, 1000);

                } else {
                    setTimeout(() => tickCard(id), 500);
                }

            })
            .catch(err => {
                console.error(err);
                running = null;
            });
    }

    function startImport(id) {

        if (running) {
            alert("Import déjà en cours");
            return;
        }

        running = id;

        document.getElementById('progress-' + id).style.display = 'block';

        fetch("<?= base_url('import/start') ?>/" + id + "?mode=card")
            .then(r => r.json())
            .then(() => tickCard(id));
    }

    function normalizeUR(v) {
        if (!v) return '00';
        return String(parseInt(v)).padStart(2, '0');
    }

    function setType(type) {
        currentType = type;

        const urFilter = document.getElementById('filter-ur');

        // 🔴 NATIONAL → reset + disable
        if (type === 'national') {
            urFilter.value = '';
            urFilter.disabled = true;
            urFilter.style.opacity = 0.5;
        } else {
            // 🟢 REGIONAL + TOUS → actif
            urFilter.disabled = false;
            urFilter.style.opacity = 1;
        }

        applyFilters();
    }

    function applyFilters() {

        let ur = document.getElementById('filter-ur')?.value;
        let targetUR = ur ? normalizeUR(ur) : null;

        document.querySelectorAll('.card-import').forEach(card => {

            const cardUR = normalizeUR(card.dataset.ur);
            const cardType = card.dataset.type;

            let show = true;

            // 🔴 TYPE = NATIONAL
            if (currentType === 'national') {
                show = (cardType === 'national');
                targetUR = null; // ignore UR
            }

            // 🟢 TYPE = REGIONAL
            else if (currentType === 'regional') {
                if (cardType !== 'regional') show = false;
            }

            // 🟡 TYPE = TOUS → pas de filtre type

            // 🔵 FILTRE UR (si actif)
            if (show && targetUR) {
                if (cardUR !== targetUR) show = false;
            }

            <?php if ($isRegional): ?>
                if (cardType !== 'regional') show = false;
            <?php endif; ?>

            card.style.display = show ? 'block' : 'none';
        });
    }

    window.onload = function() {

        <?php if ($isRegional): ?>
            currentType = 'regional';
        <?php endif; ?>

        applyFilters();
    };
</script>

<?= $this->endSection() ?>
