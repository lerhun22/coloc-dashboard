<?php helper('dashboard'); ?>

<?php renderImages($jugement['top_clivantes'] ?? [], 'Clivante'); ?>
<?php renderImages($jugement['top_consensuelles'] ?? [], 'Consensuelle'); ?>
<?php renderImages($jugement['top_juge_decisif'] ?? [], 'Juge décisif'); ?>

<div class="section">

    <h2>🔥 Images clivantes</h2>
    <p class="small">
        Fortes divergences entre juges
    </p>

    <?php renderImages(
        $jugement['top_clivantes'] ?? [],
        'Clivante'
    ); ?>


    <h2 style="margin-top:40px;">
        🤝 Images consensuelles
    </h2>

    <p class="small">
        Accord global des jurés
    </p>

    <?php renderImages(
        $jugement['top_consensuelles'] ?? [],
        'Consensuelle'
    ); ?>


    <h2 style="margin-top:40px;">
        ⚖️ Juge décisif
    </h2>

    <?php renderImages(
        $jugement['top_juge_decisif'] ?? [],
        'Juge'
    ); ?>

</div>