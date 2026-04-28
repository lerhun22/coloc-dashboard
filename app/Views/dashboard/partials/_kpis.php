<?php

$globalFPF =
    $dashboard['globalFPF'] ?? [];

$comparison =
    $dashboard['comparison'] ?? [];

?>

<div class="section">

    <h2>🔵 Classement National</h2>

    <div class="grid-3">

        <div class="card">
            Clubs actifs
            <b>
                <?= $globalFPF['nb_clubs'] ?? 0 ?>
            </b>
        </div>


        <div class="card">
            Points FPF

            <b>
                <?= number_format(
                    $globalFPF['nb_points'] ?? 0,
                    0,
                    '',
                    ' '
                ) ?>
            </b>
        </div>


        <div class="card highlight">
            Poids UR

            <b>
                <?= $comparison['ratio_points'] ?? 0 ?>%
            </b>
        </div>

    </div>

</div>