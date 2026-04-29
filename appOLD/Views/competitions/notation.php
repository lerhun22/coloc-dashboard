<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>

<div class="main-content">
    <div class="judging-container">

        <div class="scan-zone">
            <input id="scan_barcode" placeholder="Scanner la fiche" autofocus>
        </div>

        <div class="photo-zone">

            <img id="photo_preview" src="" class="photo-thumb">

            <div class="photo-meta">
                <div id="photo_title"></div>
                <div id="photo_author"></div>
            </div>

        </div>

        <div class="notes">

            <input id="judge1" type="number" min="6" max="20">
            <input id="judge2" type="number" min="6" max="20">
            <input id="judge3" type="number" min="6" max="20">

            <input id="total" readonly>

        </div>

    </div>

</div>




<?= $this->endSection() ?>