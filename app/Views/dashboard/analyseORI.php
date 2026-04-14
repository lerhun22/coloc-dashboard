<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?php
$targetClub = $targetClub ?? '';
$rivalClubs = $rivalClubs ?? [];
?>

<style>
    .block {
        margin-bottom: 40px
    }

    h2 {
        border-left: 5px solid #3498db;
        padding-left: 10px;
    }

    .card-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    .card {
        background: #fff;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background: #2c3e50;
        color: white;
        padding: 8px;
    }

    .table td {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }
</style>

<div class="main-content">

    <h1>📊 Analyse complète - <?= $annee ?></h1>

    <!-- ========================= -->
    <!-- GLOBAL -->
    <!-- ========================= -->
    <div class="block">
        <h2>🟦 Indicateurs globaux</h2>

        <div class="card-grid">
            <div class="card">Clubs<br><strong><?= $global['nb_clubs'] ?></strong></div>
            <div class="card">Auteurs<br><strong><?= $global['nb_auteurs'] ?></strong></div>
            <div class="card">Images<br><strong><?= $global['nb_images'] ?></strong></div>
            <div class="card">Moyenne<br><strong><?= round($global['moyenne'], 2) ?></strong></div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- CLUBS -->
    <!-- ========================= -->

    <div class="block">
        <h2>🟩 Clubs</h2>

        <div style="
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
">

            <?php foreach (array_slice($clubsExtended, 0, 20) as $c): ?>

                <div style="
    background:#fff;
    padding:14px;
    border-radius:12px;
    box-shadow:0 2px 5px rgba(0,0,0,0.05);
    display:flex;
    flex-direction:column;
    gap:10px;
">

                    <!-- HEADER -->
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <span style="background:#2c3e50;color:#fff;padding:5px 8px;border-radius:6px;">
                            #<?= $c['rang'] ?>
                        </span>
                        <span style="background:#34495e;color:#fff;padding:5px 8px;border-radius:6px;">
                            <?= esc($c['nom']) ?>
                        </span>
                        <span style="background:#7f8c8d;color:#fff;padding:5px 8px;border-radius:6px;">
                            #<?= esc($c['numero']) ?>
                        </span>
                    </div>

                    <?php
                    $levels = [
                        'cdf' => ['label' => 'CdF', 'color' => '#f1c40f'],
                        'n1'  => ['label' => 'N1', 'color' => '#2980b9'],
                        'n2'  => ['label' => 'N2', 'color' => '#3498db'],
                        'r'   => ['label' => 'R', 'color' => '#8e44ad'],
                    ];
                    ?>

                    <?php foreach ($levels as $key => $meta): ?>

                        <?php if ($c[$key]['count'] > 0): ?>

                            <?php
                            $comps = $c[$key]['competitions'];
                            $display = array_slice($comps, 0, 3);
                            $remaining = count($comps) - 3;
                            ?>

                            <div style="display:flex;flex-direction:column;gap:4px;">

                                <!-- BADGE -->
                                <span style="
        background:<?= $meta['color'] ?>;
        color:#fff;
        padding:5px 8px;
        border-radius:6px;
        font-size:13px;
        width:fit-content;
    ">
                                    <?= $meta['label'] ?> : <?= $c[$key]['count'] ?>
                                </span>

                                <!-- COMPETITIONS -->
                                <span style="font-size:12px;color:#555;">
                                    <?php foreach ($display as $index => $comp): ?>
                                        <?= esc($comp['nom']) ?> (⭐<?= $comp['points'] ?>)
                                        <?= $index < count($display) - 1 ? ', ' : '' ?>
                                    <?php endforeach; ?>

                                    <?php if ($remaining > 0): ?>
                                        <span style="color:#999;">
                                            +<?= $remaining ?> autres
                                        </span>
                                    <?php endif; ?>
                                </span>

                                <!-- STATS -->
                                <span style="font-size:12px;color:#777;">
                                    📸 <?= $c[$key]['images'] ?> • ⭐ <?= $c[$key]['points'] ?>
                                </span>

                            </div>

                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- TOTAL -->
                    <div style="margin-top:5px;">
                        <span style="background:#27ae60;color:#fff;padding:5px 8px;border-radius:6px;">
                            📸 <?= $c['total_images'] ?>
                        </span>
                        <span style="background:#f39c12;color:#fff;padding:5px 8px;border-radius:6px;">
                            ⭐ <?= $c['total_points'] ?>
                        </span>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>
    </div>

    <!-- ========================= -->
    <!-- AUTEURS -->
    <!-- ========================= -->

    <div class="block">
        <h2>🟨 Auteurs</h2>

        <table class="table">
            <tr>
                <th>#</th>
                <th>Auteur</th>
                <th>Points</th>
            </tr>

            <?php foreach (array_slice($auteurs, 0, 20) as $a): ?>
                <tr>
                    <td><?= $a['rang'] ?></td>
                    <td><?= esc($a['auteur_nom']) ?></td>
                    <td><?= $a['points'] ?></td>
                </tr>
            <?php endforeach; ?>

        </table>
    </div>

</div>

<?= $this->endSection() ?>