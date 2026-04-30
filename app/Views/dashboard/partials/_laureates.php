<?php if (empty($laureates)) return;

//dd($laureates);

?>

<div class="section">

    <h2>🏅 Capital d’excellence — auteurs lauréats</h2>

    <p class="section-intro">
        Podiums auteurs contextualisés par densité compétitive
    </p>

    <table class="ranking">

        <tr>
            <th>Compétition</th>
            <th>Densité</th>
            <th>🥇</th>
            <th>🥈</th>
            <th>🥉</th>
            <th>⚡</th>
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
                        <?= $row['field_size'] ?> images
                    </div>

                </td>


                <?php foreach (['gold', 'silver', 'bronze'] as $medal): ?>

                    <td>

                        <?php if (!empty($row[$medal])):

                            $m = $row[$medal];
                        ?>

                            <strong>
                                <?= esc($m['author']) ?>
                            </strong>

                            <div class="laureate-meta">
                                <?= $m['total'] ?> pts ·
                                <?= $m['nb_photos'] ?> img
                            </div>

                            <div class="laureate-club">
                                <span class="club-chip club-<?= $m['club'] ?>">
                                    <?= $m['club'] ?>
                                </span>
                            </div>

                        <?php else: ?>

                            —

                        <?php endif; ?>

                    </td>


                <?php endforeach; ?>

                <td>
                    <?php if (!empty($row['strikes'])): ?>
                        <?php foreach ($row['strikes'] as $s): ?>
                            <div class="strike">
                                ⚡ <?= esc($s) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>