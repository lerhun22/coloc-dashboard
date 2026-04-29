<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>

<div class="container">

    <h1>Import ZIP local</h1>

    <table class="table">

        <tr>
            <th>Fichier</th>
            <th></th>
        </tr>

        <?php if (!empty($zipFiles)): ?>

        <?php foreach ($zipFiles as $f): ?>

        <tr>

            <td>
                <?= esc($f) ?>
            </td>

            <td>

                <a href="<?= site_url('import/run/' . $f) ?>" class="btn btn-primary">

                    Importer

                </a>

            </td>

        </tr>

        <?php endforeach; ?>

        <?php else: ?>

        <tr>
            <td colspan="2">
                Aucun ZIP trouvé
            </td>
        </tr>

        <?php endif; ?>

    </table>

</div>

<?= $this->endSection() ?>