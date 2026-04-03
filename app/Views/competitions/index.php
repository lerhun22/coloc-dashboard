<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">

    <div class="container">

        <h1 class="page-title">Compétitions</h1>

        <div class="competition-list">


            <!-- ========================================= -->
            <!-- IMPORT COPAINS EN PREMIER -->
            <!-- ========================================= -->

            <div class="competition-card new-competition"
                onclick="window.location='<?= site_url('competitions/import') ?>'">

                <div class="competition-left">

                    <h2 class="competition-title">
                        + Importer une compétition COPAINS
                    </h2>

                    <div class="competition-date">
                        Fédération Photographique de France
                    </div>

                </div>

                <div class="competition-right">

                    <div class="competition-stats">
                        Import depuis serveur fédéral
                    </div>

                </div>

                <div class="competition-arrow">
                    +
                </div>

            </div>



            <!-- ========================================= -->
            <!-- LISTE DES COMPETITIONS -->
            <!-- ========================================= -->

            <?php foreach ($competitions_list as $competition): ?>

                <div class="competition-card"
                    onclick="window.location='<?= base_url('competitions/' . $competition['id']) ?>'">

                    <!-- ACTIONS -->

                    <div class="competition-actions" onclick="event.stopPropagation();">

                        <a href="<?= base_url('competitions/delete/' . $competition['id']) ?>" class="btn-action btn-danger"
                            onclick="return confirm('Supprimer la compétition ?\nFichiers + base supprimés')">

                            SUPP

                        </a>

                    </div>


                    <!-- NOM -->

                    <div class="competition-left">

                        <div class="competition-title">
                            <?= esc($competition['nom']) ?>
                        </div>

                        <div class="competition-date">

                            <?php if (!empty($competition['urs_id'])): ?>

                                UR<?= esc($competition['urs_id']) ?> — Régional

                            <?php else: ?>

                                National

                            <?php endif; ?>

                            •

                            <?= esc($competition['date_competition']) ?>

                        </div>

                    </div>


                    <!-- STATS -->

                    <div class="competition-right">

                        <?= $competition['photo_count'] ?? 0 ?> photos •

                        <?= $competition['author_count'] ?? 0 ?> auteurs •

                        <?= $competition['club_count'] ?? 0 ?> clubs •

                        Ø <?= $competition['avg_photos_per_author'] ?? 0 ?> / auteur •

                        Ø <?= $competition['avg_photos_per_club'] ?? 0 ?> / club

                    </div>

                </div>

            <?php endforeach; ?>


        </div>

    </div>

</div>

<?= $this->endSection() ?>
