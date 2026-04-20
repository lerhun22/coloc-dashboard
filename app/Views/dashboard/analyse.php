<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Analyse Clubs</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f7fa;
        }

        h2 {
            margin-top: 40px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            background: #fff;
        }

        th,
        td {
            padding: 8px 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        .left {
            text-align: left;
        }

        .highlight {
            font-weight: bold;
            color: #2c3e50;
        }

        .section {
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .debug {
            background: #111;
            color: #0f0;
            padding: 10px;
            font-size: 12px;
            overflow-x: auto;
        }
    </style>
</head>

<body>

    <h1>📊 Analyse Clubs</h1>

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
                        <th>Club</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($clubs as $c): ?>
                        <tr>
                            <td><?= $c['rank'] ?></td>
                            <td><?= esc($c['club_nom']) ?></td>
                            <td><?= number_format($c['points'], 0, ',', ' ') ?></td>
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
                        <td><?= esc($c['club_nom']) ?></td>
                        <td><?= $c['ur'] ?></td>
                        <td><?= number_format($c['N2'], 0, ',', ' ') ?></td>
                        <td><?= number_format($c['N1'], 0, ',', ' ') ?></td>
                        <td><?= number_format($c['CDF'], 0, ',', ' ') ?></td>
                        <td><strong><?= number_format($c['total'], 0, ',', ' ') ?></strong></td>
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