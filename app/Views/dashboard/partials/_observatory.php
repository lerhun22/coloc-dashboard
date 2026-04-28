<?php if (empty($obs)) return; ?>

<?php

$conversions =
    array_filter(
        array_column(
            $obs,
            'conversion'
        )
    );

$authorsActifs =
    array_sum(
        array_column(
            $obs,
            'authors'
        )
    );

$avgConversion =
    $conversions
    ? round(
        array_sum($conversions)
            / count($conversions),
        1
    )
    : 0;

$bestConversion =
    $conversions
    ? max($conversions)
    : 0;

?>

<div class="section">

    <h2>🟠 Observatoire Clubs UR</h2>

    <div class="grid-3">

        <div class="card">
            Auteurs actifs
            <b><?= $authorsActifs ?></b>
        </div>

        <div class="card">
            Conversion moyenne
            <b><?= $avgConversion ?></b>
        </div>

        <div class="card highlight">
            Meilleure conversion
            <b><?= $bestConversion ?></b>
        </div>

    </div>


    <table class="ranking">

        <tr>
            <th>#</th>
            <th>Club</th>
            <th>Auteurs</th>
            <th>Moteurs</th>
            <th>Depth %</th>
            <th>Img</th>
            <th>Img/Auteur</th>
            <th>Poids%</th>
            <th>Conversion</th>
            <th>Bonus</th>
            <th>Indice</th>
            <th>Profil</th>
        </tr>


        <?php foreach ($obs as $c):

            $perf = 'perf-mid';

            if ($c['conversion'] >= 104) {
                $perf = 'perf-good';
            } elseif ($c['conversion'] < 99) {
                $perf = 'perf-low';
            }

        ?>

            <tr>

                <td class="rank-col">
                    <span class="rank-badge">
                        <?= $c['rang_obs'] ?>
                    </span>
                </td>

                <td class="club-col">
                    <?= esc($c['nom']) ?>
                </td>

                <td>
                    <?= $c['authors'] ?: '—' ?>
                </td>

                <td>
                    <?= $c['motor_authors'] ?>
                </td>

                <td>
                    <?= $c['depth_pct'] ?>%
                </td>

                <td>
                    <?= $c['total_images'] ?>
                </td>

                <td>
                    <?= $c['intensity'] ?>
                </td>

                <td>
                    <?= $c['weight_pct'] ?>%
                </td>

                <td>
                    <span class="obs-pill <?= $perf ?>">
                        <?= $c['conversion'] ?>
                    </span>
                </td>

                <td>
                    +<?= $c['elite_bonus'] ?>
                </td>

                <td>
                    <strong>
                        <?= $c['global_index'] ?>
                    </strong>
                </td>

                <td>
                    <span class="profile-chip">
                        <?= $c['profile'] ?>
                    </span>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>


    <div class="legend-box">

        <h3>
            📖 Lecture rapide
        </h3>

        <ul>

            <li>
                Depth % :
                profondeur compétitive du club
            </li>

            <li>
                Conversion >100 :
                surperformance
            </li>

            <li>
                Bonus élite :
                présence N1 / Coupe
            </li>

            <li>
                Indice global :
                efficience + excellence
            </li>

            <li>
                Le profil qualifie
                la structure du club.
            </li>

        </ul>

    </div>

</div>