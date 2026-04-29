<?php

use App\Helpers\CompetitionMapper;

$id = (int)($c['id'] ?? 0);

$ur = $c['urs_id'] !== null
    ? str_pad((string)$c['urs_id'], 2, '0', STR_PAD_LEFT)
    : '';

$typeClass = $c['isNational'] ? 'national' : 'regional';
$label = CompetitionMapper::label($c);

// =========================
// 🎯 STATUT MÉTIER
// =========================
$isImported = !empty($c['is_imported']);
$isJudged   = !empty($c['isJudged']);

$statusClass = 'badge-wait';
$statusText  = 'À venir';

if (!$isImported) {
    $statusClass = 'badge-error';
    $statusText  = 'Non importée';
} elseif ($isJudged) {
    $statusClass = 'badge-ok';
    $statusText  = 'Jugée' . (!empty($c['dateJugement']) ? ' : ' . $c['dateJugement'] : '');
}
?>

<div class="card-import <?= $typeClass ?>"
    id="card-<?= $id ?>"
    data-ur="<?= esc($ur) ?>"
    data-judged="<?= $isJudged ? 1 : 0 ?>"
    data-pending="<?= !$isJudged ? 1 : 0 ?>">

    <!-- =========================
         HEADER
    ========================= -->
    <div class="card-header">

        <span class="badge badge-type <?= $typeClass === 'national' ? 'badge-national' : 'badge-regional' ?>">
            <?= $typeClass === 'national' ? 'National' : 'UR ' . $ur ?>
        </span>

        <span class="badge badge-status <?= $statusClass ?>">
            <?= esc($statusText) ?>
        </span>

    </div>

    <!-- =========================
         TITLE
    ========================= -->
    <div class="card-title">
        <?= esc($label) ?>
    </div>

    <!-- =========================
         META
    ========================= -->
    <div class="card-ref">
        Saison <?= esc($c['saison']) ?> • ID <?= $id ?>
    </div>

    <!-- =========================
         PROGRESS
    ========================= -->
    <div class="progress-box" id="progress-<?= $id ?>" style="display:none">
        <div class="progress-bar">
            <div class="progress-fill" id="bar-<?= $id ?>"></div>
        </div>
    </div>

    <div class="card-feedback" id="text-<?= $id ?>"></div>

    <!-- =========================
         ACTIONS
    ========================= -->
    <div class="card-actions">

        <button class="btn-import"
            onclick="startImportFull(<?= $id ?>, '<?= $c['type'] ?>')">
            FULL
        </button>

        <button class="btn-db"
            onclick="startImportDB(<?= $id ?>, '<?= $c['type'] ?>')">
            DB
        </button>

    </div>

</div>

<!-- =========================
     🎨 STYLE
========================= -->
<style>
    .card-import {
        padding: 15px;
        border-radius: 12px;
        background: #fff;
        border-left: 6px solid #ccc;
        transition: 0.2s;
    }

    .card-import:hover {
        transform: translateY(-2px);
    }

    .card-import.national {
        border-left-color: #1976d2;
        background: #eef5ff;
    }

    .card-import.regional {
        border-left-color: #388e3c;
        background: #eefaf0;
    }

    /* HEADER */
    .card-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    /* BADGES */
    .badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-national {
        background: #1976d2;
        color: #fff;
    }

    .badge-regional {
        background: #388e3c;
        color: #fff;
    }

    .badge-ok {
        background: #2e7d32;
        color: #fff;
    }

    .badge-wait {
        background: #eee;
    }

    .badge-error {
        background: #c62828;
        color: #fff;
    }

    /* TEXT */
    .card-title {
        font-size: 16px;
        font-weight: bold;
    }

    .card-ref {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }

    /* ACTIONS */
    .card-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
    }

    .btn-import {
        background: #6d4c41;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 6px;
        cursor: pointer;
    }

    .btn-db {
        background: #1976d2;
        color: white;
        border: none;
        padding: 6px 10px;
        border-radius: 6px;
        cursor: pointer;
    }

    /* PROGRESS */
    .progress-box {
        margin-top: 8px;
    }

    .progress-bar {
        width: 100%;
        height: 6px;
        background: #eee;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        width: 0%;
        background: #1976d2;
        transition: width 0.3s;
    }

    .card-feedback {
        font-size: 12px;
        margin-top: 5px;
    }
</style>

<!-- =========================
     ⚙️ SCRIPT
========================= -->
<script>
    window.ROUTES = window.ROUTES || <?= json_encode($routes) ?>;

    /* =========================
       🔥 UPDATE BADGE
    ========================= */
    function updateBadge(id, status) {

        const card = document.getElementById(`card-${id}`);
        const badge = card.querySelector('.badge-status');

        badge.classList.remove('badge-ok', 'badge-wait', 'badge-error');

        if (status === 'ok') {
            badge.classList.add('badge-ok');
            badge.innerText = "Importée";
        }

        if (status === 'error') {
            badge.classList.add('badge-error');
            badge.innerText = "Erreur";
        }
    }

    /* =========================
       📦 IMPORT DB
    ========================= */
    window.startImportDB = function(id, type) {

        const text = document.getElementById(`text-${id}`);

        text.innerHTML = "⏳ DB...";

        fetch(`${ROUTES.import.db}/${id}?type=${type}`)
            .then(res => res.json())
            .then(data => {

                const label = (data.urs_id === null) ? 'National' : 'Régional';

                text.innerHTML = `✅ DB OK (${label})`;

                updateBadge(id, 'ok');

            })
            .catch(() => {
                text.innerHTML = "❌ DB erreur";
                updateBadge(id, 'error');
            });
    };

    /* =========================
       🚀 IMPORT FULL
    ========================= */
    window.startImportFull = function(id, type) {

        const text = document.getElementById(`text-${id}`);
        const bar = document.getElementById(`bar-${id}`);
        const box = document.getElementById(`progress-${id}`);

        if (box) box.style.display = 'block';
        if (bar) bar.style.width = "20%";

        text.innerHTML = "🚀 Import...";

        fetch(`${ROUTES.import.full}/${id}?type=${type}`)
            .then(res => res.text())
            .then(() => {

                if (bar) bar.style.width = "100%";

                text.innerHTML = "✅ FULL terminé";

                updateBadge(id, 'ok');

                setTimeout(() => {
                    window.location.href = `${BASE_URL}/competitions/${id}/photos`;
                }, 1500);

            })
            .catch(() => {

                if (bar) bar.style.width = "0%";

                text.innerHTML = "❌ FULL erreur";
                updateBadge(id, 'error');
            });
    };
</script>