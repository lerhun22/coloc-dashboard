<?php

use App\Helpers\CompetitionMapper;

$id = (int)($c['id'] ?? 0);

$ur = $c['urs_id'] !== null
    ? str_pad((string)$c['urs_id'], 2, '0', STR_PAD_LEFT)
    : '';

// 🔥 type basé sur flags controller
$typeClass = $c['isNational'] ? 'national' : 'regional';

// 🔥 label métier
$label = CompetitionMapper::label($c);


// 🔥 statut
?>

<div class="card-import <?= $typeClass ?>"
    id="card-<?= $id ?>"
    data-ur="<?= esc($ur) ?>"
    data-judged="<?= $c['isJudged'] ? 1 : 0 ?>"
    data-pending="<?= !$c['isJudged'] ? 1 : 0 ?>">

    <!-- 🏷 LIGNE 1 -->
    <div class="card-title">
        <?= esc($label) ?>
    </div>

    <!-- 📅 LIGNE 2 -->
    <div class="card-status">

        <?php if (empty($c['is_imported'])): ?>
            <span class="status-error">NON IMPORTÉE</span>

        <?php elseif ($c['isJudged']): ?>
            <span class="status-ok">
                JUGÉE <?= esc($c['dateJugement']) ?>
            </span>

        <?php else: ?>
            <span class="status-wait">
                À VENIR<?= !empty($c['dateJugement']) ? ' : ' . esc($c['dateJugement']) : '' ?>
            </span>
        <?php endif; ?>

    </div>

    <!-- 🆔 LIGNE 3 -->
    <div class="card-ref">
        ID <?= $id ?>
    </div>

    <!-- 📊 PROGRESS -->
    <div class="progress-box" id="progress-<?= $id ?>" style="display:none">
        <div class="progress-bar" id="bar-<?= $id ?>"></div>
    </div>

    <div class="progress-text" id="text-<?= $id ?>"></div>

    <!-- 🔘 ACTIONS -->
    <div class="card-actions">
        <button class="btn-import" onclick="startImportFull(<?= $id ?>, '<?= $c['type'] ?>')">
            Import
        </button>
        <button class="btn-db"
            onclick="startImportDB(<?= $id ?>, '<?= $c['type'] ?>')">
            DB
        </button>

    </div>

</div>

<style>
    .card-import {
        padding: 15px;
        border-radius: 12px;
        background: #fff;
        border-left: 6px solid #ccc;
    }

    .card-import.national {
        border-left-color: #1976d2;
        background: #eef5ff;
    }

    .card-import.regional {
        border-left-color: #388e3c;
        background: #eefaf0;
    }

    .card-title {
        font-size: 18px;
        font-weight: bold;
    }

    .card-status {
        margin-top: 5px;
        font-size: 13px;
        font-weight: 500;
    }

    .status-ok {
        color: #2e7d32;
    }

    .status-wait {
        color: #999;
    }

    .status-error {
        color: #c62828;
        font-weight: bold;
    }

    .card-ref {
        font-size: 12px;
        color: #999;
        margin-top: 2px;
    }

    .card-actions {
        margin-top: 10px;
        display: flex;
        gap: 10px;
    }

    .btn-db {
        background: #eee;
    }
</style>


<script>
    window.startImportFull = function(id, type) {

        const text = document.getElementById(`text-${id}`);
        const bar = document.getElementById(`bar-${id}`);
        const box = document.getElementById(`progress-${id}`);
        const card = document.getElementById(`card-${id}`);

        if (box) box.style.display = 'block';
        if (bar) bar.style.width = "20%";

        text.innerHTML = "🚀 Import en cours...";

        fetch(`${ROUTES.import.full}/${id}?type=${type}`)
            .then(res => res.text()) // 🔥 important
            .then(() => {

                if (bar) bar.style.width = "100%";

                const label = (type === 'N') ? 'National' : 'Régional';
                text.innerHTML = `✅ FULL terminé (${label})`;

                /*
                =========================
                🎯 LOGIQUE MÉTIER
                =========================
                */

                const isJudged = card.dataset.judged == "1";
                const isPending = card.dataset.pending == "1";

                console.log("STATUS:", {
                    isJudged,
                    isPending
                });

                /*
                =========================
                🔁 REDIRECTION
                =========================
                */

                setTimeout(() => {
                    window.location.href = `${BASE_URL}/competitions/${id}/photos`;
                }, 800);


            })
            .catch(err => {

                console.error(err);

                if (bar) bar.style.width = "0%";

                text.innerHTML = "❌ Exception";
            });
    };


    window.startImportDB = function(id, type) {

        const text = document.getElementById(`text-${id}`);

        text.innerHTML = "⏳ Import DB en cours...";

        fetch(`${ROUTES.import.db}/${id}?type=${type}`)
            .then(res => res.json())
            .then(data => {

                let label = '';

                if (data.type === 'N') label = 'National';
                else if (data.type === 'R') label = 'Régional';
                else label = 'Inconnu';

                if (data.status === 'ok') {
                    text.innerHTML = `✅ DB importée (${label})`;
                } else {
                    text.innerHTML = `❌ Erreur import`;
                }
            })
            .catch(err => {
                console.error(err);
                text.innerHTML = "❌ Exception";
            });
    };
</script>

<script>
    window.ROUTES = window.ROUTES || <?= json_encode($routes) ?>;
</script>