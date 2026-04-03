<?php
$saison = $c['saison'] ?? '----';
$ur = str_pad($c['urs_id'] ?? 0, 2, '0', STR_PAD_LEFT);
$numero = $c['numero'] ?? '-';
$id = (int)($c['id'] ?? 0);

$isNational = ($ur === '00');
$type = $isNational ? 'national' : 'regional';
$badge = $isNational ? 'National' : 'Régional';
?>

<div class="card-import card-<?= esc($label) ?>" id="card-<?= $id ?>" data-ur="<?= esc($ur) ?>"
    data-type="<?= $type ?>">

    <!-- TITLE -->
    <div class="card-title">
        <?= esc($c['nom']) ?>
    </div>

    <!-- INFO LINE -->
    <div class="card-info">
        <strong>
            <?= esc($saison) ?> <?= esc($ur) ?> <?= esc($numero) ?>
        </strong>

        — ID <?= $id ?>

        <span class="badge badge-<?= $type ?>">
            <?= $badge ?>
        </span>
    </div>

    <!-- PROGRESS -->
    <div class="progress-box" id="progress-<?= $id ?>" style="display:none">
        <div class="progress-bar" id="bar-<?= $id ?>"></div>
    </div>

    <div class="progress-text" id="text-<?= $id ?>"></div>
    <div class="progress-size" id="size-<?= $id ?>"></div>

    <!-- BUTTON -->
    <button id="btn-<?= $id ?>" class="btn-import" onclick="startImport(<?= $id ?>)">
        Importer
    </button>

</div>
