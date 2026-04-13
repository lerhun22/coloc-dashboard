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

                <div class="competition-left">
                    <h2 class="competition-title">
                        + Importer une compétition COPAINS
                    </h2>
                    <div class="competition-date">
                        Fédération Photographique de France
                    </div>
                </div>

                <div class="competition-right">
                    Import depuis serveur fédéral
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

                    <div class="competition-main">

                        <!-- HEADER FULL WIDTH -->
                        <div class="competition-header-line">

                            <!-- SUPP -->
                            <div class="competition-actions" onclick="event.stopPropagation();">
                                <a href="<?= base_url('competitions/delete/' . $competition['id']) ?>"
                                    class="btn-action btn-danger"
                                    onclick="return confirm('Supprimer la compétition ?')">
                                    SUPP
                                </a>
                            </div>

                            <!-- BADGE -->
                            <span class="competition-badge">
                                <?= empty($competition['urs_id']) ? 'National' : 'UR' . esc($competition['urs_id']) ?>
                            </span>

                            <!-- NOM -->
                            <div class="competition-title">
                                <?= esc($competition['nom']) ?>
                            </div>

                            <!-- DATE -->
                            <div class="competition-date-inline">
                                <?= esc($competition['date_competition']) ?>
                            </div>

                            <!-- ACTIVE -->
                            <?php if ($competition['id'] == ($activeCompetitionId ?? null)): ?>
                                <span class="active-label">ACTIVE</span>
                            <?php endif; ?>

                        </div>



                    </div>

                    <!-- RIGHT -->
                    <div class="competition-right">

                        <div class="competition-stats-main">
                            --------------------
                            <span><strong><?= $competition['photo_count'] ?? 0 ?></strong> photos</span>
                            <span><strong><?= $competition['author_count'] ?? 0 ?></strong> auteurs</span>
                            <span><strong><?= $competition['clubs_nat'] ?? 0 ?></strong> clubs</span>
                            --------------------
                            <?= $competition['clubs_ur'] ?? 0 ?> UR<?= esc($userUr ?? '') ?> •
                            <?= $competition['clubs_nat'] ?? 0 ?> total
                        </div>



                    </div>

                </div>


            <?php endforeach; ?>

        </div>
    </div>
</div>

<style>
    /* =========================
GLOBAL
========================= */

    .competition-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    /* =========================
CARD
========================= */

    .competition-card {
        padding: 15px;
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
STRUCTURE
========================= */

    .competition-main {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* =========================
HEADER INLINE
========================= */

    .competition-header-line {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* SUPP */
    .competition-actions {
        margin-right: 5px;
    }

    /* BADGE */
    .competition-badge {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 500;
    }

    /* TITLE */
    .competition-title {
        font-size: 16px;
        font-weight: 600;
    }

    /* DATE INLINE */
    .competition-date-inline {
        font-size: 13px;
        color: #777;
    }

    .competition-date-inline::before {
        content: "— ";
    }

    /* =========================
TYPE COLORS
========================= */

    /* 🔵 NATIONAL */
    .comp-national {
        border-left-color: #1976d2;
        background: #eef5ff;
    }

    .comp-national .competition-badge {
        background: #1976d2;
        color: #fff;
    }

    /* 🟢 REGIONAL */
    .comp-regional {
        border-left-color: #388e3c;
        background: #eefaf0;
    }

    .comp-regional .competition-badge {
        background: #388e3c;
        color: #fff;
    }

    /* =========================
ACTIVE
========================= */

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
RIGHT / STATS
========================= */

    .competition-right {
        text-align: right;
    }

    .competition-stats-main {
        font-size: 14px;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .competition-stats-sub {
        font-size: 12px;
        color: #666;
        margin-top: 3px;
    }

    .competition-stats-main strong {
        font-weight: 600;
    }

    /* =========================
SCORE
========================= */

    .competition-score {
        font-size: 13px;
        font-weight: bold;
        margin-top: 5px;
    }

    .competition-score.high {
        color: #2e7d32;
    }

    .competition-score.medium {
        color: #f9a825;
    }

    .competition-score.low {
        color: #c62828;
    }

    /* =========================
IMPORT CARD
========================= */

    .new-competition {
        background: #f5f5f5;
        border-left: 6px solid #999;
    }
</style>

<?= $this->endSection() ?>