<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">  <!-- AJOUT -->

    <div class="home-hero">
        <h1>COLOC</h1>
        <h2>Bienvenue sur l'outil local de gestion des concours de la FPF</h2>
    </div>

    <div class="home-version">

        <p>
            Vous utilisez la version 
            <strong><?= esc($current_version) ?></strong>
            (<?= $official_build == "1" ? "officielle" : "personnalisée" ?>)
            mise à jour le 
            <strong><?= esc($current_version_date) ?></strong>

            <?php if ($build_number): ?>
                — build <?= esc($build_number) ?>
            <?php endif; ?>

            <?php if ($local_build_date): ?>
                — maj locale <?= esc($local_build_date) ?>
            <?php endif; ?>

            — <strong><?= esc($environment) ?></strong>
        </p>

        <p>
            En cas de problème, contactez <?= esc($origin) ?> :
            <a href="mailto:<?= esc($author_email) ?>">
                <?= esc($author_email) ?>
            </a>
        </p>

        <p>
            Consultez l’
            <a href="<?= site_url('utilisateurs/aide') ?>" target="_blank">Aide</a>
            pour vous guider dans l’utilisation de l’outil.
        </p>

    </div>

    <div class="home-actions">

        <a href="<?= site_url('preparation') ?>" class="home-card">
            🔧
            <span>Préparer un concours</span>
        </a>

        <a href="<?= site_url('competitions/gestion') ?>" class="home-card">
            🖼
            <span>Gérer un concours</span>
        </a>

        <a href="<?= site_url('competitions/exportation') ?>" class="home-card">
            📢
            <span>Exporter les résultats</span>
        </a>

    </div>

</div> <!-- FIN main-content -->

<?= $this->endSection() ?>