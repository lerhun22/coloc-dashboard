<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content"> 

<div class="page-header">
    <h1>Préparer un concours</h1>

    <a href="<?= site_url('preparation/create') ?>" class="btn-primary">
        Ajouter une compétition
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<table class="table-competitions">
    <thead>
        <tr>
            <th>Concours</th>
            <th>Date</th>
            <th>Œuvres</th>
            <th style="text-align:right;">Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php foreach ($competitions as $competition): ?>

        <?php $isActive = ($competition['date_competition'] >= date('Y-m-d')); ?>

        <tr>
            <td>
                <span class="<?= $isActive ? 'badge-active' : 'badge-closed' ?>">
                    <?= $isActive ? 'ACTIF' : 'ARCHIVÉ' ?>
                </span>
                <?= esc($competition['nom']) ?>
            </td>

            <td><?= date('d M Y', strtotime($competition['date_competition'])) ?></td>

            <td><?= $competition['nb_oeuvres'] ?> œuvres</td>

            <td class="actions">

                <?php if ($isActive): ?>

                    <a href="<?= site_url('preparation/upload/'.$competition['id']) ?>"
                       class="action-primary"
                       title="Charger le fichier zip"
                       data-tooltip="Charger les œuvres">
                        🖼
                    </a>

                    <a href="<?= site_url('preparation/edit-judge/'.$competition['id']) ?>"
                       class="action-secondary"
                       title="Modifier le juge"
                       data-tooltip="Modifier le juge">
                        👤
                    </a>

                    <a href="<?= site_url('preparation/pte/'.$competition['id']) ?>"
                       class="action-secondary"
                       title="Informations PTE"
                       data-tooltip="Informations PTE">
                        🏠
                    </a>

                    <a href="<?= site_url('preparation/reload/'.$competition['id']) ?>"
                       class="action-secondary"
                       title="Recharger"
                       data-tooltip="Recharger">
                        🔄
                    </a>

                    <form method="post"
                          action="<?= site_url('preparation/'.$competition['id'].'/delete') ?>"
                          style="display:inline;"
                          onsubmit="return confirm('Confirmer la suppression ?')">

                        <button type="submit"
                                class="action-danger"
                                title="Supprimer"
                                data-tooltip="Supprimer">
                            🗑
                        </button>
                    </form>

                <?php else: ?>

                    <span class="locked">🔒 Clôturée</span>

                <?php endif; ?>

            </td>
        </tr>

    <?php endforeach; ?>

    </tbody>
</table>

</div>

<?= $this->endSection() ?>