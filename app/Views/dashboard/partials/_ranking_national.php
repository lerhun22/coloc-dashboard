<?php if (empty($top)) return; ?>

<div class="section">

    <h2>🏆 Top 10 National Clubs</h2>

    <table class="ranking">

        <tr>
            <th>#</th>
            <th>Club</th>
            <th>UR</th>
            <th>N2</th>
            <th>N1</th>
            <th>CDF</th>
            <th>Total</th>
        </tr>

        <?php foreach ($top as $c): ?>

        <tr>

            <td class="rank-col">
                <span class="rank-badge">
                    <?= $c['rang'] ?? $c['rank'] ?>
                </span>
            </td>

            <td class="club-col">
                <?= mb_strimwidth(
                        esc((string)($c['nom'] ?? $c['club_nom'])),
                        0,
                        42,
                        '…'
                    ) ?>
            </td>

            <td class="ur-col">
                <?= $c['ur'] ?>
            </td>

            <td class="num-col">
                <?= number_format(
                        $c['N2'],
                        0,
                        '',
                        ' '
                    ) ?>
            </td>

            <td class="num-col">
                <?= number_format(
                        $c['N1'],
                        0,
                        '',
                        ' '
                    ) ?>
            </td>

            <td class="num-col">
                <?= number_format(
                        $c['CDF'],
                        0,
                        '',
                        ' '
                    ) ?>
            </td>

            <td class="num-col total-col">
                <?= number_format(
                        $c['points'] ?? $c['total'],
                        0,
                        '',
                        ' '
                    ) ?>
            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>