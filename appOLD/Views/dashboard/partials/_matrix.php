<?php if (empty($matrix)) return; ?>

<?php
$totaux = [];

foreach ($clubs as $club) {
    $totaux[$club] = 0;
}
?>

<div class="section">

    <h2><?= esc($title) ?></h2>

    <div class="matrix-wrap">

        <table class="matrix">

            <tr>
                <th>Compétition</th>
                <th>Lauréat / Club gagnant</th>

                <?php foreach ($clubs as $club): ?>
                    <th>
                        <span class="club-chip club-<?= $club ?>">
                            <?= $club ?>
                        </span>
                    </th>
                <?php endforeach; ?>
            </tr>

            <?php foreach ($matrix as $comp => $data): ?>

                <tr>

                    <td>
                        <?= esc($comp) ?>
                    </td>

                    <td>

                        <?php
                        $winner = $data['winner_club'] ?? null;

                        $winnerAuthor = trim(
                            (string)($data['winner_author'] ?? '')
                        );

                        $winnerClubName =
                            $clubMap[$winner]
                            ?? ('Club ' . $winner);

                        $winnerPoints =
                            $data['winner_points'] ?? null;
                        ?>

                        <?php if ($winner): ?>

                            <?php if (
                                $winnerAuthor !== ''
                                && $winnerAuthor !== $winnerClubName
                            ): ?>
                                <div>
                                    🏅
                                    <strong>
                                        <?= esc($winnerAuthor) ?>
                                    </strong>
                                </div>
                            <?php endif; ?>

                            <div class="winner-club">

                                <span class="club-chip club-<?= $winner ?>">
                                    <?= $winner ?>
                                </span>

                                <?= esc($winnerClubName) ?>

                            </div>

                            <?php if ($winnerPoints): ?>
                                <div class="winner-points">
                                    <?= number_format(
                                        $winnerPoints,
                                        0,
                                        '',
                                        ' '
                                    ) ?> pts
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            —
                        <?php endif; ?>

                    </td>


                    <?php foreach ($clubs as $club): ?>

                        <?php
                        $v =
                            $data['scores'][$club]
                            ?? null;

                        if ($v !== null) {
                            $totaux[$club] += $v;
                        }
                        ?>

                        <td class="<?= $v !== null ? 'active' : '' ?>">

                            <?= $v !== null
                                ? number_format(
                                    $v,
                                    0,
                                    '',
                                    ' '
                                )
                                : '' ?>

                        </td>

                    <?php endforeach; ?>

                </tr>

            <?php endforeach; ?>


            <tr>

                <th colspan="2">
                    Totaux
                </th>

                <?php foreach ($clubs as $club): ?>
                    <th>
                        <?= number_format(
                            $totaux[$club],
                            0,
                            '',
                            ' '
                        ) ?>
                    </th>
                <?php endforeach; ?>

            </tr>

        </table>

    </div>


    <div class="legend">

        <b>Légende clubs</b>

        <div class="legend-grid">

            <?php foreach ($clubs as $club): ?>

                <div class="legend-item">

                    <span class="club-chip club-<?= $club ?>">
                        <?= $club ?>
                    </span>

                    <span>
                        <?= $clubMap[$club]
                            ?? ('Club ' . $club) ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>