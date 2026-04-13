<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

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
    <!-- NATIONAL -->
    <!-- ========================= -->
    <div class="block">
        <h2>🟥 National</h2>

        <p><?= count($national) ?> compétitions</p>

    </div>

    <!-- ========================= -->
    <!-- REGIONAL -->
    <!-- ========================= -->
    <div class="block">
        <h2>🟧 Régional</h2>

        <p><?= count($regional) ?> compétitions</p>

    </div>

    <!-- ========================= -->
    <!-- CLUBS -->
    <!-- ========================= -->
    <div class="block">
        <h2>🟩 Clubs</h2>

        <table class="table">
            <tr>
                <th>#</th>
                <th>Club</th>
                <th>Points</th>
                <th>Images</th>
            </tr>

            <?php foreach (array_slice($clubs, 0, 20) as $c): ?>
                <tr>
                    <td><?= $c['rang'] ?></td>
                    <td><?= esc($c['club_nom']) ?></td>
                    <td><?= $c['points'] ?></td>
                    <td><?= $c['images'] ?></td>
                </tr>
            <?php endforeach; ?>

        </table>
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

    <!-- ========================= -->
    <!-- JUGEMENT -->
    <!-- ========================= -->
    <div class="block">
        <h2>🟪 Analyse jugement</h2>

        <p>Images clivantes : <?= count($jugement['top_clivantes']) ?></p>
        <p>Images consensuelles : <?= count($jugement['top_consensuelles']) ?></p>
        <p>Juge décisif : <?= count($jugement['top_juge_decisif']) ?></p>

    </div>

</div>

<?= $this->endSection() ?>