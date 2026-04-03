<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'COLOC' ?></title>

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/import.css') ?>">

    <?= $this->renderSection('styles') ?>
</head>

<body>

    <div class="app-container">

        <?= view('layout/header') ?>

        <main class="main-content">
            <div class="container">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

    </div>

    <?= $this->renderSection('scripts') ?>

</body>

</html>