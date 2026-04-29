<?php

$globalFPF  = $dashboard['globalFPF'] ?? [];
$globalUR   = $dashboard['globalUR'] ?? [];
$comparison = $dashboard['comparison'] ?? [];
$urName = $dashboard['userURName'] ?? '';

?>

<div class="section">

    <h2>🔵 Classement National</h2>

    <div class="grid-3">


        <!-- =========================================
NATIONAL
========================================= -->
        <div class="card">
            Participation Nationale

            <b>
                <?= $globalFPF['nb_clubs'] ?? 0 ?>
            </b>
            clubs<br>

            <?= $globalFPF['nb_authors'] ?? 0 ?>
            auteurs<br>

            <?= number_format(
                $globalFPF['nb_images'] ?? 0,
                0,
                '',
                ' '
            ) ?>
            images
        </div>



        <!-- =========================================
TOP UR
========================================= -->
        <div class="card">
            Top UR

            <b>
                <?= $comparison['rank_ur'] ?? '-' ?>e / 22
            </b><br>

            <?= number_format(
                $globalUR['nb_points'] ?? 0,
                0,
                '',
                ' '
            ) ?>
            pts FPF<br>

            <?= number_format(
                $globalUR['nb_images'] ?? 0,
                0,
                '',
                ' '
            ) ?>
            images
        </div>



        <!-- =========================================
FOCUS UR22
========================================= -->
        <div class="card highlight">
            Focus UR<?= $urName ?>

            <b>
                <?= $comparison['clubs_engaged'] ?? 0 ?>
                /
                <?= $globalUR['nb_clubs'] ?? 0 ?>
            </b>
            clubs N1-CdF<br>

            <?= $comparison['engagement_rate'] ?? 0 ?>%
            haut niveau<br>

            <?= number_format(
                $globalUR['nb_images'] ?? 0,
                0,
                '',
                ' '
            ) ?>
            images
        </div>


    </div>
</div>