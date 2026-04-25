<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?php
$top = array_slice($national, 0, 10);

$urClubs = $dashboard['urClubs'] ?? [];

$matrixNational =
    $dashboard['competitionMatrixNational'] ?? [];

$matrixRegional =
    $dashboard['competitionMatrixRegional'] ?? [];

$clubs =
    $dashboard['clubColumns'] ?? [];

sort($clubs);

$globalFPF =
    $dashboard['globalFPF'];

$comparison =
    $dashboard['comparison'];

/*
------------------------------------------------
Club labels
------------------------------------------------
*/

$clubMap =
    $dashboard['clubLabels'] ?? [];
?>

<style>
    .main-content {
        max-width: 1550px;
        margin: auto;
        padding: 30px;
    }

    .section {
        background: #fff;
        padding: 34px 38px;
        border-radius: 18px;
        margin-bottom: 45px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .08);
    }

    /* =========================
KPIs
========================= */

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin: 30px 0;
    }

    .card {
        background: #f7f9fc;
        padding: 28px;
        border-radius: 14px;
    }

    .card b {
        display: block;
        font-size: 32px;
        margin-top: 8px;
    }

    .highlight {
        background: #fff6e6;
    }

    .export-btn {
        float: right;
        background: #27ae60;
        color: #fff;
        padding: 11px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
    }


    /* =========================
BASE TABLE RESET
========================= */

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th {
        background: #243447;
        color: #fff;
        padding: 15px 18px;
    }

    td {
        padding: 14px 18px;
        border-bottom: 1px solid #e8edf3;
        color: #1f2937;
        background: #fff;
    }


    /* =========================
RANKINGS (FIX gris parasite)
========================= */

    .ranking {
        font-size: 18px;
        width: 100%;
    }

    .ranking tbody tr,
    .ranking tbody td {
        background: #ffffff !important;
    }

    .ranking tbody tr:hover td {
        background: #eef6ff !important;
        transition: .15s;
    }

    .ranking td {
        box-shadow: none !important;
        border-bottom: 1px solid #e8edf3;
    }

    .rank-col {
        width: 75px;
        text-align: center;
    }

    .rank-badge {
        display: inline-block;
        min-width: 38px;
        padding: 7px 11px;
        border-radius: 20px;
        background: #243447;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
    }

    .club-col {
        width: 42%;
        text-align: left;
        font-weight: 600;
        background: #fff !important;
        color: #172033;
    }

    .ur-col {
        width: 90px;
        text-align: center;
    }

    .num-col {
        width: 120px;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .total-col {
        font-weight: 700;
        font-size: 20px;
    }


    /* =========================
CLUB BADGES
========================= */

    .club-chip {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 18px;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        line-height: 1;
    }

    [class^="club-"] {
        background: #7f8c8d;
    }

    .club-603 {
        background: #8e44ad;
    }

    .club-1233 {
        background: #3498db;
    }

    .club-1677 {
        background: #16a085;
    }

    .club-1771 {
        background: #d35400;
    }

    .club-1816 {
        background: #e74c3c;
    }

    .club-1829 {
        background: #2ecc71;
    }

    .club-2159 {
        background: #34495e;
    }

    .club-2268 {
        background: #f39c12;
    }

    .club-2274 {
        background: #c0392b;
    }

    .club-58 {
        background: #9b59b6;
    }

    .club-131 {
        background: #16a085;
    }

    .club-747 {
        background: #2980b9;
    }


    /* =========================
MATRICES
========================= */

    .matrix-wrap {
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .matrix {
        min-width: 1500px;
    }

    .matrix th,
    .matrix td {
        white-space: nowrap;
        text-align: center;
    }

    .matrix td:first-child {
        text-align: left;
        min-width: 360px;
        font-weight: 600;
    }

    .matrix td:nth-child(2) {
        min-width: 260px;
        background: #fff;
    }

    .matrix td.active {
        background: #eef6ff;
        font-weight: 700;
    }


    /* =========================
LEGEND
========================= */

    .legend {
        margin-top: 30px;
    }

    .legend-grid {
        display: grid;
        grid-template-columns:
            repeat(auto-fit, minmax(420px, 1fr));
        gap: 20px 40px;
        margin-top: 20px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 14px;
        white-space: nowrap;
    }
</style>


<div class="main-content">

    <h1>
        📊 Dashboard COLOC — Saison <?= $annee ?>

        <a href="<?= site_url('dashboard/export') ?>"
            class="export-btn">
            📥 Export Excel
        </a>
    </h1>


    <div class="section">

        <h2>🔵 Classement National</h2>

        <div class="grid-3">

            <div class="card">
                Clubs actifs
                <b><?= $globalFPF['nb_clubs'] ?></b>
            </div>

            <div class="card">
                Points FPF
                <b>
                    <?= number_format(
                        $globalFPF['nb_points'],
                        0,
                        '',
                        ' '
                    ) ?>
                </b>
            </div>

            <div class="card highlight">
                UR22
                <b><?= $comparison['ratio_points'] ?>%</b>
            </div>

        </div>


        <table class="ranking">

            <tr>
                <th>#</th>
                <th>Club</th>
                <th>UR</th>
                <th>N2</th>
                <th>N1</th>
                <th>CDF</th>
                <th>Total</th>
            </tr>


            <?php foreach ($top as $c): ?>

                <tr>

                    <td class="rank-col">
                        <span class="rank-badge">
                            <?= $c['rank'] ?>
                        </span>
                    </td>

                    <td class="club-col">
                        <?= mb_strimwidth(
                            esc((string)$c['club_nom']),
                            0,
                            40,
                            '…'
                        ) ?>
                    </td>

                    <td class="ur-col">
                        <?= $c['ur'] ?>
                    </td>

                    <td class="num-col">
                        <?= number_format(
                            $c['N2'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                    <td class="num-col">
                        <?= number_format(
                            $c['N1'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                    <td class="num-col">
                        <?= number_format(
                            $c['CDF'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                    <td class="num-col total-col">
                        <?= number_format(
                            $c['total'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>



    <div class="section">

        <h2>🟢 UR22 — Classement</h2>

        <table class="ranking">

            <tr>
                <th>#</th>
                <th>Club</th>
                <th>N2</th>
                <th>N1+CDF</th>
                <th>Images</th>
                <th>Total</th>
            </tr>


            <?php foreach ($urClubs as $c): ?>

                <tr>

                    <td class="rank-col">
                        <span class="rank-badge">
                            <?= $c['rang'] ?>
                        </span>
                    </td>

                    <td class="club-col">
                        <?= mb_strimwidth(
                            esc((string)$c['nom']),
                            0,
                            42,
                            '…'
                        ) ?>
                    </td>

                    <td class="num-col">
                        <?= number_format(
                            $c['N2'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                    <td class="num-col">
                        <?= number_format(
                            $c['N1_CDF'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                    <td class="num-col">
                        <?= number_format(
                            $c['total_images'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                    <td class="num-col total-col">
                        <?= number_format(
                            $c['points'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>



    <?php
    function renderMatrix(
        $title,
        $matrix,
        $clubs,
        $clubMap
    ) {

        if (empty($matrix)) return;

        $totaux = [];

        foreach ($clubs as $club) {
            $totaux[$club] = 0;
        }
    ?>

        <div class="section">

            <h2><?= $title ?></h2>

            <div class="matrix-wrap">

                <table class="matrix">

                    <tr>

                        <th>Compétition</th>
                        <th>Lauréat</th>

                        <?php foreach ($clubs as $club): ?>

                            <th>
                                <span class="club-chip club-<?= $club ?>">
                                    <?= $club ?>
                                </span>
                            </th>

                        <?php endforeach; ?>

                    </tr>


                    <?php foreach ($matrix as $comp => $data): ?>

                        <tr>

                            <td>
                                <?= esc($comp) ?>
                            </td>

                            <td>

                                <?php
                                $winner =
                                    $data['winner_club'] ?? '';
                                ?>

                                <?= esc(
                                    $data['winner_author']
                                        ?? ($clubMap[$winner] ?? '—')
                                ) ?>

                                <?php if ($winner): ?>
                                    <br>
                                    <span class="club-chip club-<?= $winner ?>">
                                        <?= $winner ?>
                                    </span>
                                <?php endif; ?>

                            </td>


                            <?php foreach ($clubs as $club): ?>

                                <?php
                                $v =
                                    $data['scores'][$club]
                                    ?? '';

                                if ($v !== '') {
                                    $totaux[$club] += $v;
                                }
                                ?>

                                <td class="<?= $v !== '' ? 'active' : '' ?>">

                                    <?= $v !== ''

                                        ? number_format(
                                            $v,
                                            0,
                                            '',
                                            ' '
                                        )

                                        : '' ?>

                                </td>

                            <?php endforeach; ?>

                        </tr>

                    <?php endforeach; ?>


                    <tr>

                        <th colspan="2">
                            Totaux
                        </th>

                        <?php foreach ($clubs as $club): ?>
                            <th>
                                <?= number_format(
                                    $totaux[$club],
                                    0,
                                    '',
                                    ' '
                                ) ?>
                            </th>
                        <?php endforeach; ?>

                    </tr>

                </table>

            </div>


            <div class="legend">

                <b>Légende clubs</b>

                <div class="legend-grid">

                    <?php foreach ($clubs as $club): ?>

                        <div class="legend-item">

                            <span class="club-chip club-<?= $club ?>">
                                <?= $club ?>
                            </span>

                            <span>
                                <?= $clubMap[$club] ?? ('Club ' . $club) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    <?php } ?>


    <?php
    renderMatrix(
        '🟣 Répartition compétitions nationales',
        $matrixNational,
        $clubs,
        $clubMap
    );

    renderMatrix(
        '🟢 Répartition compétitions régionales',
        $matrixRegional,
        $clubs,
        $clubMap
    );
    ?>

</div>

<?= $this->endSection() ?>