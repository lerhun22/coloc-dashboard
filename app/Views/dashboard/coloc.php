<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>


<link rel="stylesheet" href="<?= base_url('css/dashboard-coloc.css') ?>">


<?php

$top =
    $dashboard['topNational']
    ?? [];

$urClubs =
    $dashboard['urClubs']
    ?? [];

$matrixNational =

    $dashboard['competitionMatrixNational']
    ?? [];

$matrixRegional =
    $dashboard['competitionMatrixRegional']
    ?? [];

$clubs =
    $dashboard['clubColumns']
    ?? [];

$clubMap =
    $dashboard['clubLabels']
    ?? [];

sort($clubs);
?>

<div class="main-content">

    <h1>
        📊 Dashboard COLOC — Saison <?= $annee ?>


        <a href="<?= site_url('dashboard/export') ?>"
            class="export-btn">
            📥 Export Excel
        </a>
    </h1>
    <!-- Performance -->
    <?= view('dashboard/partials/_kpis', [
        'dashboard' => $dashboard
    ]) ?>

    <?= view('dashboard/partials/_ranking_national', [
        'top' => array_slice($national, 0, 10)
    ]) ?>

    <?= view('dashboard/partials/_ranking_ur', [
        'urClubs' => $urClubs
    ]) ?>

    <?= view('dashboard/partials/_observatory', [
        'obs' => $dashboard['clubObservatory'] ?? []
    ]) ?>
    <!-- Excellence -->
    <?= view('dashboard/partials/_laureates', [
        'laureates' => $dashboard['dashboardLaureates'] ?? []
    ]) ?>

    <?= view('dashboard/partials/_matrix', [
        'title' => '🟣 Répartition nationales',
        'matrix' => $matrixNational,
        'clubs' => $clubs,
        'clubMap' => $clubMap
    ]) ?>

    <?= view('dashboard/partials/_matrix', [
        'title' => '🟢 Répartition régionales',
        'matrix' => $matrixRegional,
        'clubs' => $clubs,
        'clubMap' => $clubMap
    ]) ?>
    <!-- Lecture qualitative -->
    <?= view('dashboard/partials/_judgement', [
        'jugement' => $jugement
    ]) ?>

    <?= view('dashboard/partials/_wow', [
        'wow' => $wow
    ]) ?>

</div>

<?= $this->endSection() ?>