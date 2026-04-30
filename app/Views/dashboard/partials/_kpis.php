<?php

$globalFPF  = $dashboard['globalFPF'] ?? [];
$globalUR   = $dashboard['globalUR'] ?? [];
$comparison = $dashboard['comparison'] ?? [];

?>

<div class="section">

    <h2>🔵 Classement National</h2>

    <div class="grid-3">

        <!-- =====================================================
             CARD 1 : NATIONAL
        ====================================================== -->
        <div class="card">
            <div class="kpi-title">
                Participation Nationale
            </div>

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



        <!-- =====================================================
             CARD 2 : TOP UR
        ====================================================== -->
        <div class="card">
            <div class="kpi-title">
                Top UR
            </div>

            <b>
                <?= $comparison['rank_ur_national'] ?? '-' ?>e / 22
            </b><br>

            <?= number_format(
                $globalUR['nb_images'] ?? 0,
                0,
                '',
                ' '
            ) ?>
            images <br>

            <?= $comparison['nb_authors_ranked'] ?? 0 ?>
            auteurs classés
        </div>

        <!-- =====================================================
             CARD 2 : FOCUS UR USER
        ====================================================== -->

        <div class="card highlight">
            <div class="kpi-title">
                Focus <?= env('copain.uruser') ?: 22; ?>
            </div>

            <b>
                <?= $comparison['clubs_engaged'] ?? 0 ?>
                /
                <?= $globalUR['nb_clubs'] ?? 0 ?>
            </b>
            clubs engagés<br>

            <?= $comparison['engagement_rate'] ?? 0 ?>%
            mobilisation<br>

            <?= $globalUR['nb_authors'] ?? 0 ?>
            auteurs
        </div>

    </div>

</div>