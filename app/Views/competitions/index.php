<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">
    <div class="container">

        <h1 class="page-title">Compétitions</h1>

        <div class="competition-list">

            <!-- ========================================= -->
            <!-- IMPORT -->
            <!-- ========================================= -->
            <div class="competition-card new-competition"
                onclick="window.location='<?= site_url('competitions/import') ?>'">

                <div class="bloc infos">
                    <h2 class="competition-title">
                        + Importer une compétition COPAINS
                    </h2>
                    <div class="competition-date-inline">
                        Fédération Photographique de France
                    </div>
                </div>

                <div class="bloc stat-ur">
                    Import serveur fédéral
                </div>

            </div>

            <!-- ========================================= -->
            <!-- LISTE -->
            <!-- ========================================= -->

            <?php foreach ($competitions_list as $competition): ?>

            <div class="competition-card
                    <?= empty($competition['urs_id']) ? 'comp-national' : 'comp-regional' ?>
                    <?= ($competition['id'] == ($activeCompetitionId ?? null)) ? 'comp-active' : '' ?>"
                onclick="window.location='<?= base_url('competitions/' . $competition['id']) ?>'">

                <!-- 1 ACTION -->
                <div class="bloc action" onclick="event.stopPropagation();">
                    <a href="<?= base_url('competitions/delete/' . $competition['id']) ?>" class="btn-action btn-danger"
                        onclick="return confirm('Supprimer la compétition ?')">
                        SUPP
                    </a>
                </div>

                <!-- 2 INFOS -->
                <div class="bloc infos">
                    <span class="competition-badge">
                        <?= empty($competition['urs_id']) ? 'National' : 'UR' . esc($competition['urs_id']) ?>
                    </span>

                    <span class="competition-title">
                        <?= esc($competition['nom']) ?>
                    </span>

                    <span class="competition-date-inline">
                        <?= esc($competition['date_competition']) ?>
                    </span>

                    <?php if ($competition['id'] == ($activeCompetitionId ?? null)): ?>
                    <span class="active-label">ACTIVE</span>
                    <?php endif; ?>
                </div>

                <!-- 3 STATS NAT -->
                <div class="bloc stat-nat">
                    <span><strong><?= $competition['photo_count'] ?? 0 ?></strong> photos</span>
                    <span><strong><?= $competition['author_count'] ?? 0 ?></strong> auteurs</span>
                    <span><strong><?= $competition['clubs_nat'] ?? 0 ?></strong> clubs</span>
                </div>

                <!-- 4 STATS UR -->
                <div class="bloc stat-ur">
                    <span><?= $competition['clubs_ur'] ?? 0 ?> UR<?= esc($userUr ?? '') ?></span>
                    <span><?= $competition['clubs_nat'] ?? 0 ?> total</span>
                </div>

            </div>

            <?php endforeach; ?>

        </div>
    </div>
</div>




<style>
/* =========================
LIST
========================= */

.competition-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* =========================
CARD = 1 LIGNE
========================= */

.competition-card {
    display: flex;
    /* 🔥 clé */
    align-items: center;
    gap: 15px;
    padding: 12px 15px;
    border-radius: 10px;
    background: #fff;
    border-left: 6px solid #ccc;
    transition: 0.2s;
    cursor: pointer;
}

.competition-card:hover {
    transform: translateY(-2px);
}

/* =========================
BLOCS
========================= */

.bloc {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* 1 ACTION */
.bloc.action {
    width: 80px;
}

/* 2 INFOS */
.bloc.infos {
    flex: 1;
    font-weight: 600;
}

/* 3 STATS NAT */
.bloc.stat-nat {
    width: 260px;
    justify-content: center;
    font-size: 13px;
    color: #555;
}

/* 4 STATS UR */
.bloc.stat-ur {
    width: 160px;
    justify-content: flex-end;
    font-size: 13px;
    font-weight: 600;
}

/* séparateurs */
.bloc:not(:first-child) {
    border-left: 1px solid rgba(0, 0, 0, 0.08);
    padding-left: 15px;
}

/* =========================
TEXT
========================= */

.competition-title {
    font-size: 15px;
}

.competition-date-inline {
    font-size: 13px;
    color: #777;
}

.competition-date-inline::before {
    content: "— ";
}

/* =========================
BADGES
========================= */

.competition-badge {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 500;
}

/* =========================
COLORS
========================= */

/* NATIONAL */
.comp-national {
    border-left-color: #1976d2;
    background: #eef5ff;
}

.comp-national .competition-badge {
    background: #1976d2;
    color: #fff;
}

/* REGIONAL */
.comp-regional {
    border-left-color: #388e3c;
    background: #eefaf0;
}

.comp-regional .competition-badge {
    background: #388e3c;
    color: #fff;
}

/* ACTIVE */
.comp-active {
    border-left-color: #ff9800 !important;
    background: #fff8e1;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.comp-active .competition-badge {
    background: #ff9800 !important;
}

.active-label {
    font-size: 10px;
    background: #ff9800;
    color: #fff;
    padding: 2px 6px;
    border-radius: 4px;
}

/* =========================
IMPORT
========================= */

.new-competition {
    background: #f5f5f5;
    border-left: 6px solid #999;
}
</style>




<?= $this->endSection() ?>
