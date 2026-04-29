<?php if (empty($urClubs)) return; ?>

<div class="section">

    <h2>🟢 Classement Clubs UR</h2>

    <table class="ranking">

        <tr>
            <th>#</th>
            <th>Club</th>
            <th>N2</th>
            <th>N1+CDF</th>
            <th>Images</th>
            <th>Total</th>
        </tr>

        <?php foreach ($urClubs as $c): ?>

            <tr>

                <td class="rank-col">
                    <span class="rank-badge">
                        <?= $c['rang'] ?>
                    </span>
                </td>


                <td class="club-col">
                    <?= mb_strimwidth(
                        esc((string)$c['nom']),
                        0,
                        42,
                        '…'
                    ) ?>
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
                        $c['N1_CDF'],
                        0,
                        '',
                        ' '
                    ) ?>
                </td>


                <td class="num-col">
                    <?= number_format(
                        $c['total_images'],
                        0,
                        '',
                        ' '
                    ) ?>
                </td>


                <td class="num-col total-col">
                    <?= number_format(
                        $c['points'],
                        0,
                        '',
                        ' '
                    ) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>