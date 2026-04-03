<?php

use App\Libraries\CompetitionService;

$activeCompetition = CompetitionService::getActive();

$competitionBadge = '';

if ($activeCompetition) {

    $ur = $activeCompetition['urs_id'] ?? null;

    $ur = $ur
        ? str_pad((string)$ur, 2, '0', STR_PAD_LEFT)
        : '00';

    $competitionBadge =
        "{$activeCompetition['nom']} - {$activeCompetition['saison']} - {$ur} - {$activeCompetition['numero']} - {$activeCompetition['id']}";
}

// log_message('debug', 'Active competition ID: ' . ($activeCompetition['id'] ?? 'none'));

?>

<div class="app-header">

    <div class="header-container">

        <div class="header-brand">
            <a href="<?= base_url() ?>">COLOC</a>
        </div>

        <nav class="header-nav">

            <a href="<?= base_url('dashboard') ?>">Accueil</a>
            <a href="<?= base_url('competitions') ?>">Compétitions</a>
            <a href="<?= $activeCompetition
                            ? base_url('competitions/' . $activeCompetition['id'] . '/photos')
                            : '#' ?>" <?= !$activeCompetition ? 'style="opacity:0.5; pointer-events:none;"' : '' ?>>
                Photo
            </a>
            <a href="<?= base_url('jugement') ?>">Jugement</a>
            <a href="<?= base_url('export') ?>">Export</a>
            <a href="<?= base_url('suivi') ?>">Suivi</a>

        </nav>

        <div class="header-competition">

            <span class="badge-competition">
                <?= $competitionBadge ?? '' ?>
            </span>

        </div>

    </div>

</div>
