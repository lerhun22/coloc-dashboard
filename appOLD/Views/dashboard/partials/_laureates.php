<?php if (empty($laureates)) return; ?>

<div class="section">

    <h2>
        🏅 Capital d’excellence — Images & auteurs
    </h2>

    <p class="section-intro">
        Lauréats image, densité compétitive
        et domination Top5.
    </p>


    <table class="ranking">

        <tr>
            <th>Compétition</th>
            <th>Densité</th>
            <th>🏆 Image 1</th>
            <th>🥈 Image 2</th>
            <th>🥉 Image 3</th>
            <th>🎯 Strike</th>
        </tr>



        <?php foreach ($laureates as $row): ?>

            <tr>

                <td class="club-col">
                    <?= esc($row['competition']) ?>
                </td>


                <td class="num-col">

                    <span class="obs-pill <?= $row['density_class'] ?>">
                        <?= $row['density_label'] ?>
                    </span>

                    <div class="density-meta">
                        <?= $row['field_size'] ?>
                        images
                    </div>

                </td>



                <?php for ($i = 1; $i <= 3; $i++):

                    $img =
                        $row['image_laureates'][$i - 1]
                        ?? null;
                ?>

                    <td>

                        <?php if ($img): ?>

                            <strong>
                                <?= esc($img['author']) ?>
                            </strong>

                            <div class="laureate-meta">
                                Image <?= $img['place'] ?>
                            </div>

                            <div class="laureate-club">
                                <span class="
club-chip
club-<?= $img['club'] ?>
">
                                    <?= $img['club'] ?>
                                </span>
                            </div>

                        <?php else: ?>

                            —

                        <?php endif; ?>

                    </td>

                <?php endfor; ?>



                <td>

                    <?php if (!empty($row['strike'])):

                        $s = $row['strike']; ?>

                        <strong>
                            <?= esc($s['author']) ?>
                        </strong>

                        <div class="laureate-meta">
                            🔥 <?= $s['count'] ?>/5 images
                        </div>

                        <div class="density-meta">
                            <?= implode(
                                ', ',
                                $s['positions']
                            ) ?>
                        </div>

                        <div class="laureate-club">
                            <span class="
club-chip
club-<?= $s['club'] ?>
">
                                <?= $s['club'] ?>
                            </span>
                        </div>

                    <?php else: ?>

                        <span style="
color:#98a2b3;
">
                            Aucun strike
                        </span>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>