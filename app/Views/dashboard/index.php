<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<style>
    .table-dashboard {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .table-dashboard th {
        background: #2c3e50;
        color: white;
        padding: 8px;
        font-size: 12px;
    }

    .table-dashboard td {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }

    .table-dashboard tr:nth-child(even) {
        background: #fafafa;
    }

    .table-dashboard tr:hover {
        background: #f0f6ff;
    }

    .badge-comp {
        display: inline-block;
        background: #eef3ff;
        padding: 3px 6px;
        margin: 2px;
        border-radius: 4px;
        font-size: 11px;
    }

    .badge-rank {
        background: #27ae60;
        color: white;
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 11px;
    }

    .dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .green {
        background: #2ecc71;
    }

    .red {
        background: #e74c3c;
    }

    .block {
        margin-top: 40px;
    }
</style>

<div class="main-content">

    <h2>📊 Matrice participation UR</h2>

    <?php if (!empty($matriceUR)): ?>
        <table class="table-dashboard">
            <thead>
                <tr>
                    <th></th>
                    <th>Mono</th>
                    <th>Couleur</th>
                    <th>Nature</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($matriceUR as $niveau => $cats): ?>
                    <tr>
                        <td><strong><?= esc($niveau) ?></strong></td>

                        <?php foreach ($cats as $val): ?>
                            <td>
                                <?= $val > 0 ? '🟢' : '🔴' ?> <?= $val ?>
                            </td>
                        <?php endforeach; ?>

                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    <?php endif; ?>

    <!-- ===================================================== -->

    <div class="block">

        <h2>📊 Présence des clubs UR en National</h2>

        <?php if (!empty($dashboardUR)): ?>
            <table class="table-dashboard">
                <thead>
                    <tr>
                        <th>Compétition</th>
                        <th>Clubs</th>
                        <th>Photos</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($dashboardUR as $c): ?>
                        <tr>
                            <td>
                                <strong><?= esc(\App\Helpers\CompetitionMapper::label($c)) ?></strong><br>
                                <small><?= esc($c['saison']) ?></small>
                            </td>

                            <td>
                                <?= $c['clubs_count'] > 0 ? '🟢' : '🔴' ?>
                                <?= $c['clubs_count'] ?>
                            </td>

                            <td><?= $c['photos'] ?></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        <?php endif; ?>

    </div>

    <!-- ===================================================== -->

    <div class="block">

        <h2>🏢 Synthèse clubs UR</h2>

        <?php if (!empty($syntheseClubs)): ?>
            <table class="table-dashboard">

                <thead>
                    <tr>
                        <th>Club</th>
                        <th>N1</th>
                        <th>N2</th>
                        <th>CdF</th>
                        <th>Total</th>
                        <th>Compétitions</th>

                        <th>N1 P</th>
                        <th>N1 IP</th>
                        <th>N2 P</th>
                        <th>N2 IP</th>
                        <th>CdF P</th>
                        <th>CdF IP</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($syntheseClubs as $club => $data): ?>
                        <tr>

                            <td><strong><?= esc($club) ?></strong></td>

                            <td><?= $data['N1'] ? '<span class="badge-rank">' . $data['N1'] . 'e</span>' : '' ?></td>
                            <td><?= $data['N2'] ? '<span class="badge-rank">' . $data['N2'] . 'e</span>' : '' ?></td>
                            <td><?= $data['CdF'] ? '<span class="badge-rank">' . $data['CdF'] . 'e</span>' : '' ?></td>

                            <td><?= $data['total'] ?></td>

                            <td>
                                <?php foreach ($data['competitions'] as $k => $v): ?>
                                    <span class="badge-comp"><?= esc($k) ?></span>
                                <?php endforeach; ?>
                            </td>

                            <td><?= $data['N1_P_COULEUR'] ? $data['N1_P_COULEUR'] . 'e' : '' ?></td>
                            <td><?= $data['N1_IP_COULEUR'] ? $data['N1_IP_COULEUR'] . 'e' : '' ?></td>
                            <td><?= $data['N2_P_COULEUR'] ? $data['N2_P_COULEUR'] . 'e' : '' ?></td>
                            <td><?= $data['N2_IP_COULEUR'] ? $data['N2_IP_COULEUR'] . 'e' : '' ?></td>
                            <td><?= $data['CDF_P_COULEUR'] ? $data['CDF_P_COULEUR'] . 'e' : '' ?></td>
                            <td><?= $data['CDF_IP_COULEUR'] ? $data['CDF_IP_COULEUR'] . 'e' : '' ?></td>

                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        <?php endif; ?>

    </div>

</div>

<?= $this->endSection() ?>