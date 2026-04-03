<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container">

    <h1>Import compétition COPAINS</h1>

    <form id="form">

        <div style="margin-bottom:10px">

            <label>Ref compétition</label>

            <input type="text" id="ref" required>

        </div>

        <button type="button" onclick="startImport()">
            Importer
        </button>

    </form>

</div>


<script>
function startImport() {
    let ref =
        document.getElementById("ref").value;

    if (!ref) return;

    window.location =
        "<?= site_url('import/start/') ?>" + ref;
}
</script>

<?= $this->endSection() ?>