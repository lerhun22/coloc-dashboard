<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Analyse Clubs</title>
</head>

<style>
    /* =========================
   DASHBOARD PREMIUM
========================= */

    .club-right {
        text-align: right;
        font-weight: 600;
    }

    .club-chip {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 14px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
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

    .matrix td.active {
        background: #eef6ff;
        font-weight: 700;
    }

    .export-btn {
        float: right;
        background: #27ae60;
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        margin-left: 12px;
    }

    thead.sticky th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
</style>

<body>

    <h1>
        📊 Dashboard COLOC — Saison <?= $annee ?>

        <a href="<?= site_url('dashboard/export') ?>"
            class="export-btn">
            📥 Export Excel
        </a>

    </h1>

    <!-- ========================= -->
    <!-- 🟢 CLASSEMENT REGIONAL -->
    <!-- ========================= -->
    <div class="section">


        <h2>🟢 Régional par compétition</h2>

        <?php foreach ($regional_by_comp as $comp => $clubs): ?>

            <h3><?= esc($comp) ?></h3>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="club-right">Club</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($clubs as $c): ?>
                        <tr>
                            <td><?= $c['rank'] ?></td>
                            <td class="club-right"><?= esc($c['club_nom']) ?></td>
                            <td><?= number_format($c['points'], 0, '', ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>

        <?php endforeach; ?>

        <h2>🔵 Classement National</h2>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Club</th>
                    <th>UR</th>
                    <th>N2</th>
                    <th>N1</th>
                    <th>CDF</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($national as $c): ?>
                    <tr>
                        <td><?= $c['rank'] ?></td>
                        <td class="club-right"><?= esc($c['club_nom']) ?></td>
                        <td><?= $c['ur'] ?></td>
                        <td><?= number_format($c['N2'], 0, '', ' ') ?></td>
                        <td><?= number_format($c['N1'], 0, '', ' ') ?></td>
                        <td><?= number_format($c['CDF'], 0, '', ' ') ?></td>
                        <td><strong><?= number_format($c['total'], 0, '', ' ') ?></strong></td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>



        <!-- ========================= -->
        <!-- 🧪 DEBUG -->
        <!-- ========================= -->
        <?php if (!empty($debug)): ?>
            <div class="section">
                <h2>🧪 Debug</h2>
                <div class="debug">
                    <pre><?= print_r($debug, true) ?></pre>
                </div>
            </div>
        <?php endif; ?>

</body>

</html>