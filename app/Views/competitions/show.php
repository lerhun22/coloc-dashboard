<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">
    <div class="container">

        <div class="competition-header">

            <h1 class="competition-title">
                <?= esc($competition['nom']) ?>
            </h1>

            <div class="competition-meta">

                <?= $competition['photo_count'] ?> photos •

                <?= $competition['author_count'] ?> auteurs •

                <?= $competition['club_count'] ?> clubs

            </div>

        </div>


        <div class="competition-nav">

            <a href="<?= site_url('competitions/' . $competition['id'] . '/photos') ?>">
                Photos
            </a>

            <a href="<?= site_url('competitions/' . $competition['id'] . '/jugement') ?>">
                Jugement
            </a>

            <a href="<?= site_url('competitions/' . $competition['id'] . '/classement') ?>">
                Classement
            </a>

            <a href="<?= site_url('competitions/' . $competition['id'] . '/export') ?>">
                Exports
            </a>

        </div>

    </div>
</div>

<?= $this->endSection() ?>
