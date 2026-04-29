<?= $this->extend('layout/default') ?>


<?= $this->section('content') ?>

<h2>Import ZIP</h2>


<table class="table">

    <tr>
        <th>Fichier</th>
        <th></th>
    </tr>


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


</table>


<?= $this->endSection() ?>