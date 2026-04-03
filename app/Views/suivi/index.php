<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<style>
    .badge {
        padding: 3px 8px;
        border-radius: 6px;
        color: white;
        font-size: 12px;
        margin-right: 3px;
    }

    .badge-haute {
        background: #dc2626;
    }

    .badge-normal {
        background: #2563eb;
    }

    .badge-ouvert {
        background: #16a34a;
    }

    .badge-clos {
        background: #374151;
    }

    .badge-faible {
        background: #16a34a;
    }

    .badge-moyen {
        background: #ea580c;
    }

    .badge-eleve {
        background: #dc2626;
    }

    .badge-retenu {
        background: #16a34a;
    }

    .badge-refuse {
        background: #dc2626;
    }

    .badge-a_etudier {
        background: #2563eb;
    }

    .badge-plus_tard {
        background: #6b7280;
    }

    .badge-CI4 {
        background: #0891b2;
    }

    .badge-COLOC {
        background: #2563eb;
    }

    .badge-COPAIN {
        background: #9333ea;
    }

    .small {
        font-size: 12px
    }
</style>


<div class="main-content">
    <div class="container">

        <h1>Suivi</h1>

        <a href="<?= site_url('suivi/create') ?>">
            Nouvelle
        </a>


        <table class="table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Badges</th>
                    <th>Quoi</th>
                    <th>Details</th>
                    <th>Analyse</th>
                    <th></th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($suivi as $s): ?>

                    <tr>

                        <td><?= $s['id'] ?></td>

                        <td>

                            <span class="badge badge-<?= $s['priorite'] ?>">
                                <?= $s['priorite'] ?>
                            </span>

                            <span class="badge badge-<?= $s['statut'] ?>">
                                <?= $s['statut'] ?>
                            </span>

                            <span class="badge badge-<?= $s['cout'] ?>">
                                <?= $s['cout'] ?>
                            </span>

                            <span class="badge badge-<?= $s['decision'] ?>">
                                <?= $s['decision'] ?>
                            </span>

                            <span class="badge badge-<?= $s['version'] ?>">
                                <?= $s['version'] ?>
                            </span>

                            <span class="badge badge-<?= $s['saison'] ?>">
                                <?= $s['saison'] ?>
                            </span>

                        </td>

                        <td><?= $s['quoi'] ?></td>

                        <td class="small"><?= $s['details'] ?></td>

                        <td class="small"><?= $s['analyse'] ?></td>

                        <td>

                            <a href="<?= site_url('suivi/edit/' . $s['id']) ?>">
                                Edit
                            </a>

                        </td>

                    </tr>

                <?php endforeach ?>

            </tbody>

        </table>

    </div>
</div>

<?= $this->endSection() ?>
