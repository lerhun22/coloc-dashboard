<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<h2>Import en cours</h2>

<div style="width:400px">

    <div style="
        height:20px;
        background:#ddd;
        margin-bottom:10px;
    ">
        <div id="bar" style="
                height:20px;
                width:0%;
                background:#28a745;
             ">
        </div>
    </div>

    <div id="text">
        starting...
    </div>

</div>


<script>
let id = <?= (int)$id ?>;

console.log("PROGRESS ID =", id);


function tick() {

    fetch("<?= base_url('import/step') ?>/" + id)

        .then(r => r.json())

        .then(s => {

            let bar =
                document.getElementById("bar");

            let text =
                document.getElementById("text");

            bar.style.width =
                (s.progress ?? 0) + "%";

            text.innerHTML =
                s.step +
                " — " +
                s.progress +
                "%";


            if (s.status !== "done") {
                setTimeout(tick, 500);
            } else {
                window.location =
                    "<?= base_url('competitions') ?>/" +
                    id +
                    "/photos";
            }

        });

}

tick();
</script>

<?= $this->endSection() ?>