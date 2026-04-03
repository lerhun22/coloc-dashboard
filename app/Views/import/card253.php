<div class="card-import card-<?= esc($label) ?>" id="card-<?= (int)$c['id'] ?>"
    data-ur="<?= esc($c['urs_id'] ?? '') ?>">

    <div class="card-title">
        <?= esc($c['nom']) ?>
    </div>

    <div class="card-info">

        <?= esc($c['saison'] ?? '') ?>
        —

        <?= esc($label ?? '') ?>
        —

        #<?= (int)$c['id'] ?>

    </div>


    <!-- =========================
         PROGRESS BAR
    ========================== -->

    <div class="progress-box" id="progress-<?= (int)$c['id'] ?>" style="display:none">

        <div class="progress-bar" id="bar-<?= (int)$c['id'] ?>">
        </div>

    </div>


    <!-- =========================
         TEXT
    ========================== -->

    <div class="progress-text" id="text-<?= (int)$c['id'] ?>">
    </div>


    <!-- =========================
         SIZE
    ========================== -->

    <div class="progress-size" id="size-<?= (int)$c['id'] ?>">
    </div>


    <!-- =========================
         BUTTON IMPORT
    ========================== -->

    <button id="btn-<?= (int)$c['id'] ?>" class="btn-import" onclick="startImport(<?= (int)$c['id'] ?>)">

        Importer

    </button>

</div>
