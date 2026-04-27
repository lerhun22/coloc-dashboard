<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?php

$top = array_slice($national, 0, 10);

$urClubs = $dashboard['urClubs'] ?? [];

$matrixNational =
    $dashboard['competitionMatrixNational'] ?? [];

$matrixRegional =
    $dashboard['competitionMatrixRegional'] ?? [];

$clubs =
    $dashboard['clubColumns'] ?? [];

sort($clubs);

$globalFPF =
    $dashboard['globalFPF'];

$comparison =
    $dashboard['comparison'];

/*
------------------------------------------------
Club labels
------------------------------------------------
*/

$clubMap =
    $dashboard['clubLabels'] ?? [];
?>

<style>
    .main-content {
        max-width: 1550px;
        margin: auto;
        padding: 30px;
    }

    .section {
        background: #fff;
        padding: 34px 38px;
        border-radius: 18px;
        margin-bottom: 45px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .08);
    }

    /* =========================
KPIs
========================= */

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin: 30px 0;
    }

    .card {
        background: #f7f9fc;
        padding: 28px;
        border-radius: 14px;
    }

    .card b {
        display: block;
        font-size: 32px;
        margin-top: 8px;
    }

    .highlight {
        background: #fff6e6;
    }

    .export-btn {
        float: right;
        background: #27ae60;
        color: #fff;
        padding: 11px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
    }


    /* =========================
BASE TABLE RESET
========================= */

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th {
        background: #243447;
        color: #fff;
        padding: 15px 18px;
    }

    td {
        padding: 14px 18px;
        border-bottom: 1px solid #e8edf3;
        color: #1f2937;
        background: #fff;
    }


    /* =========================
RANKINGS (FIX gris parasite)
========================= */

    .ranking {
        font-size: 18px;
        width: 100%;
    }

    .ranking tbody tr,
    .ranking tbody td {
        background: #ffffff !important;
    }

    .ranking tbody tr:hover td {
        background: #eef6ff !important;
        transition: .15s;
    }

    .ranking td {
        box-shadow: none !important;
        border-bottom: 1px solid #e8edf3;
    }

    .rank-col {
        width: 75px;
        text-align: center;
    }

    .rank-badge {
        display: inline-block;
        min-width: 38px;
        padding: 7px 11px;
        border-radius: 20px;
        background: #243447;
        color: #fff;
        font-weight: 700;
        font-size: 15px;
    }

    .club-col {
        width: 42%;
        text-align: left;
        font-weight: 600;
        background: #fff !important;
        color: #172033;
    }

    .ur-col {
        width: 90px;
        text-align: center;
    }

    .num-col {
        width: 120px;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .total-col {
        font-weight: 700;
        font-size: 20px;
    }


    /* =========================
CLUB BADGES
========================= */

    .club-chip {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 18px;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        line-height: 1;
    }

    [class^="club-"] {
        background: #7f8c8d;
    }

    .club-603 {
        background: #8e44ad;
    }

    .club-1233 {
        background: #3498db;
    }

    .club-1677 {
        background: #16a085;
    }

    .club-1771 {
        background: #d35400;
    }

    .club-1816 {
        background: #e74c3c;
    }

    .club-1829 {
        background: #2ecc71;
    }

    .club-2159 {
        background: #34495e;
    }

    .club-2268 {
        background: #f39c12;
    }

    .club-2274 {
        background: #c0392b;
    }

    .club-58 {
        background: #9b59b6;
    }

    .club-131 {
        background: #16a085;
    }

    .club-747 {
        background: #2980b9;
    }


    /* =========================
MATRICES
========================= */

    .matrix-wrap {
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .matrix {
        min-width: 1500px;
    }

    .matrix th,
    .matrix td {
        white-space: nowrap;
        text-align: center;
    }

    .matrix td:first-child {
        text-align: left;
        min-width: 360px;
        font-weight: 600;
    }

    .matrix td:nth-child(2) {
        min-width: 260px;
        background: #fff;
    }

    .matrix td.active {
        background: #eef6ff;
        font-weight: 700;
    }


    /* =========================
LEGEND
========================= */

    .legend {
        margin-top: 30px;
    }

    .legend-grid {
        display: grid;
        grid-template-columns:
            repeat(auto-fit, minmax(420px, 1fr));
        gap: 20px 40px;
        margin-top: 20px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 14px;
        white-space: nowrap;
    }

    /* ========================= */
    /* 🔴 GALERIE JUGEMENT */
    /* ========================= */

    .jugement-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
    }

    .jugement-card {
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: .2s;
    }

    .jugement-card:hover {
        transform: translateY(-4px);
    }

    .jugement-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        cursor: pointer;
    }

    .jugement-content {
        padding: 10px;
        text-align: left;
    }

    .jugement-title {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .jugement-meta {
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
    }

    .badge-notes span {
        display: inline-block;
        padding: 4px 6px;
        margin: 2px;
        border-radius: 4px;
        font-size: 11px;
        color: #fff;
    }

    /* ========================= */
    /* ✨ WOW */
    /* ========================= */

    .wow-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 10px;
    }

    .wow-grid img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
    }

    /* ========================= */
    /* LIGHTBOX */
    /* ========================= */

    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, .95);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .lightbox img {
        max-width: 90%;
        max-height: 90%;
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 30px;
        cursor: pointer;
    }

    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 60px;
        color: white;
        cursor: pointer;
    }

    .lightbox-nav.left {
        left: 20px;
    }

    .lightbox-nav.right {
        right: 20px;
    }


    .conv-pill {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 18px;
        font-weight: 700;
        min-width: 70px;
        text-align: center;
    }

    .conv-good {
        background: #eaf8ef;
        color: #1e7d32;
    }

    .conv-mid {
        background: #fff8e5;
        color: #9a6b00;
    }

    .conv-low {
        background: #fdecec;
        color: #b42318;
    }

    .obs-pill {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 18px;
        font-weight: 700;
        min-width: 78px;
        text-align: center;
    }

    .perf-elite {
        background: #eaf8ef;
        color: #1e7d32;
    }

    .perf-balanced {
        background: #fff8e5;
        color: #9a6b00;
    }

    .perf-low {
        background: #fdecec;
        color: #b42318;
    }

    .profile-chip {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 700;
        background: #eef3fb;
        color: #243447;
    }

    .legend-box {
        margin-top: 30px;
        padding: 24px 28px;
        background: #f7f9fc;
        border-radius: 14px;
        line-height: 1.7;
    }

    .legend-box h3 {
        margin-top: 0;
        margin-bottom: 14px;
    }

    .legend-box ul {
        margin: 0;
        padding-left: 18px;
    }

    .legend-box li {
        margin-bottom: 10px;
    }


    .obs-pill {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 18px;
        font-weight: 700;
        min-width: 76px;
        text-align: center;
    }

    .perf-good {
        background: #eaf8ef;
        color: #1e7d32;
    }

    .perf-mid {
        background: #fff8e5;
        color: #9a6b00;
    }

    .perf-low {
        background: #fdecec;
        color: #b42318;
    }

    .obs-bonus {
        font-weight: 700;
        color: #243447;
    }

    .legend-box {
        margin-top: 28px;
        padding: 24px 28px;
        background: #f7f9fc;
        border-radius: 14px;
        line-height: 1.7;
    }
</style>


<div class="main-content">

    <h1>
        📊 Dashboard COLOC — Saison <?= $annee ?>

        <a href="<?= site_url('dashboard/export') ?>"
            class="export-btn">
            📥 Export Excel
        </a>
    </h1>


    <div class="section">

        <h2>🔵 Classement National</h2>

        <div class="grid-3">

            <div class="card">
                Clubs actifs
                <b><?= $globalFPF['nb_clubs'] ?></b>
            </div>

            <div class="card">
                Points FPF
                <b>
                    <?= number_format(
                        $globalFPF['nb_points'],
                        0,
                        '',
                        ' '
                    ) ?>
                </b>
            </div>

            <div class="card highlight">
                UR
                <b><?= $comparison['ratio_points'] ?>%</b>
            </div>

        </div>


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
                            <?= $c['rank'] ?>
                        </span>
                    </td>

                    <td class="club-col">
                        <?= mb_strimwidth(
                            esc((string)$c['club_nom']),
                            0,
                            40,
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
                            $c['total'],
                            0,
                            '',
                            ' '
                        ) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>



    <div class="section">

        <h2>🟢 UR — Classement</h2>

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

    <?php if (!empty($dashboard['clubObservatory'])):

        $obs = $dashboard['clubObservatory'];

        $conversions = array_filter(
            array_column($obs, 'conversion')
        );

        $authorsActifs = array_sum(
            array_column($obs, 'authors')
        ); // on raffinera plus tard si besoin

    ?>

        <div class="section">

            <h2>🟠 Observatoire Clubs UR</h2>

            <div class="grid-3">

                <div class="card">
                    Auteurs actifs UR
                    <b>
                        <?= $authorsActifs ?>
                    </b>
                </div>

                <div class="card">
                    Conversion moyenne
                    <b>
                        <?= $conversions
                            ? round(
                                array_sum($conversions)
                                    / count($conversions),
                                1
                            )
                            : 0 ?>
                    </b>
                </div>

                <div class="card highlight">
                    Meilleure conversion
                    <b>
                        <?= $conversions
                            ? max($conversions)
                            : 0 ?>
                    </b>
                </div>

            </div>



            <table class="ranking">

                <tr>
                    <th>#</th>
                    <th>Club</th>
                    <th>Auteurs engagés</th>
                    <th>Moteurs</th>
                    <th>Depth %</th>
                    <th>Images</th>
                    <th>Images/Auteur</th>
                    <th>Poids %</th>
                    <th>Conversion</th>
                    <th>Bonus élite</th>
                    <th>Indice global</th>
                    <th>Profil</th>
                </tr>


                <?php foreach ($obs  as $c):

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

                        <td><?= $c['motor_authors'] ?></td>
                        <td><?= $c['depth_pct'] ?>%</td>

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
                            <span class="perf-badge <?= $perf ?>">
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
                            <?= $c['profile'] ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

            <div class="obs-legend" style="
margin-top:22px;
padding:22px;
background:#f8fafc;
border-radius:12px;
font-size:14px;
line-height:1.8;
">

                <b>📖 Lecture de l’observatoire clubs</b>
                <br><br>

                <b>Auteurs engagés</b><br>
                Nombre de photographes distincts ayant représenté le club sur la saison.

                <br><br>

                <b>Moteurs</b><br>
                Nombre d’auteurs « impactants » :
                photographes ayant contribué aux niveaux élevés
                (N1 / Coupe de France) et pesant réellement dans le classement.

                <br><br>

                <b>Depth % (profondeur compétitive)</b><br>
                Part des auteurs engagés qui sont moteurs :

                Depth = Auteurs moteurs / Auteurs engagés

                <br>

                • &gt; 50% → excellence diffusée dans le club<br>
                • 30–50% → structure compétitive solide<br>
                • 15–30% → quelques piliers portent le club<br>
                • &lt; 15% → forte dépendance à quelques leaders

                <br><br>

                <b>Images / Auteur</b><br>
                Intensité moyenne de contribution.

                • &lt; 6 activité légère<br>
                • 6–10 activité soutenue<br>
                • &gt; 10 club intensif

                <br><br>

                <b>Poids %</b><br>
                Part du volume d’images engagées porté par le club
                dans l’ensemble de l’UR.

                <br><br>

                <b>Conversion</b><br>
                Capacité à transformer ce poids en performance.

                • 100 = rendement attendu<br>
                • &gt;100 = surperformance<br>
                • &lt;100 = sous-performance relative

                <br><br>

                <b>Bonus élite</b><br>
                Prime liée à la présence dans les niveaux supérieurs :

                +3 présence National 1<br>
                +5 présence Coupe de France

                <br><br>

                <b>Indice global</b><br>
                Conversion pondérée par bonus élite.<br>
                Mesure synthétique d’efficience + excellence.

                <br><br>

                <b>Lecture des profils</b>

                <br><br>

                • <b>Locomotive élite</b><br>
                Club moteur, fort poids, profondeur et conversion.

                <br>

                • <b>Elite diffuse</b><br>
                Beaucoup d’auteurs performants répartissent l’excellence.

                <br>

                • <b>Elite concentrée</b><br>
                Très haut niveau porté par peu d’auteurs moteurs.

                <br>

                • <b>Moteur collectif</b><br>
                Club structurant pour la dynamique régionale.

                <br>

                • <b>Club intensif</b><br>
                Forte densité de production par auteur.

                <br>

                • <b>Convertisseur</b><br>
                Club efficient au-delà de son poids.

                <br>

                • <b>Sous potentiel</b><br>
                Volume présent mais rendement perfectible.

                <br><br>

                <b>Principe clé</b><br>
                Volume ≠ performance<br>
                Performance ≠ profondeur<br>
                La colonne <b>Depth %</b> mesure la robustesse compétitive d’un club.

            </div>

        <?php endif; ?>

        <?php
        function renderMatrix(
            $title,
            $matrix,
            $clubs,
            $clubMap
        ) {

            if (empty($matrix)) return;

            $totaux = [];

            foreach ($clubs as $club) {
                $totaux[$club] = 0;
            }
        ?>

            <div class="section">

                <h2><?= $title ?></h2>

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
                                    $winner =
                                        $data['winner_club'] ?? null;

                                    $winnerAuthor =
                                        trim(
                                            (string)(
                                                $data['winner_author'] ?? ''
                                            )
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
                                            && $winnerAuthor !== '—'
                                            && $winnerAuthor !== $winnerClubName
                                        ): ?>

                                            <div>
                                                🏅
                                                <strong>
                                                    <?= esc($winnerAuthor) ?>
                                                </strong>
                                            </div>

                                        <?php endif; ?>

                                        <div style="margin-top:6px;">

                                            <span class="club-chip club-<?= $winner ?>">
                                                <?= $winner ?>
                                            </span>

                                            <?= esc($winnerClubName) ?>

                                        </div>

                                        <?php if ($winnerPoints): ?>
                                            <div style="
            margin-top:6px;
            font-size:14px;
            color:#666;
        ">
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
                                        ?? '';

                                    if ($v !== '') {
                                        $totaux[$club] += $v;
                                    }
                                    ?>

                                    <td class="<?= $v !== '' ? 'active' : '' ?>">

                                        <?= $v !== ''
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

        <?php } ?>


        <?php
        renderMatrix(
            '🟣 Répartition compétitions nationales',
            $matrixNational,
            $clubs,
            $clubMap
        );

        renderMatrix(
            '🟢 Répartition compétitions régionales',
            $matrixRegional,
            $clubs,
            $clubMap
        );
        ?>

        </div>

        <!-- ========================= -->
        <!-- 🔴 JUGEMENT -->
        <!-- ========================= -->
        <div class="section">

            <h2>🔥 Images clivantes</h2>
            <p class="small">Fortes divergences entre juges</p>

            <?php renderImages($jugement['top_clivantes'] ?? [], 'Clivante'); ?>

            <h2>🤝 Images consensuelles</h2>
            <p class="small">Accord global</p>

            <?php renderImages($jugement['top_consensuelles'] ?? [], 'Consensuelle'); ?>

            <h2>⚖️ Juge décisif</h2>

            <?php renderImages($jugement['top_juge_decisif'] ?? [], 'Juge'); ?>

        </div>

        <!-- ========================= -->
        <!-- ✨ WOW -->
        <!-- ========================= -->
        <div class="section">

            <h2>✨ WOW (16+)</h2>

            <?php
            $galleryWow = array_map(fn($i) => $i['photo_url'] ?? $i['thumb_url'], $wow);
            $json = htmlspecialchars(json_encode($galleryWow), ENT_QUOTES);
            ?>

            <div class="wow-grid">
                <?php foreach ($wow as $i => $img): ?>
                    <img src="<?= $img['photo_url'] ?? $img['thumb_url'] ?>"
                        onclick='openLightboxList(<?= $json ?>,<?= $i ?>)'>
                <?php endforeach; ?>
            </div>

        </div>

</div>

<!-- ========================= -->
<!-- LIGHTBOX -->
<!-- ========================= -->
<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">✕</span>
    <div class="lightbox-nav left" onclick="prevImage()">‹</div>
    <img id="lightbox-img">
    <div class="lightbox-nav right" onclick="nextImage()">›</div>
</div>

<script>
    let gallery = [],
        current = 0,
        autoPlay = null;

    function openLightboxList(list, index) {
        gallery = list;
        current = index;
        updateLightbox();
        document.getElementById('lightbox').style.display = 'flex';
    }

    function updateLightbox() {
        document.getElementById('lightbox-img').src = gallery[current];
    }

    function nextImage() {
        current = (current + 1) % gallery.length;
        updateLightbox();
    }

    function prevImage() {
        current = (current - 1 + gallery.length) % gallery.length;
        updateLightbox();
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        clearInterval(autoPlay);
    }
</script>

<?php
/* ========================= */
/* 🔴 FUNCTION */
/* ========================= */
function renderImages($images, $label)
{
    if (empty($images)) return;

    $gallery = array_map(fn($i) => $i['photo_url'] ?? $i['thumb_url'], $images);
    $json = htmlspecialchars(json_encode($gallery), ENT_QUOTES);

    echo "<div class='jugement-grid'>";

    foreach ($images as $i => $img) {

        $src = $img['photo_url'] ?? $img['thumb_url'];
        $title = esc($img['titre'] ?? '');

        echo "<div class='jugement-card'>";

        echo "<img src='$src' class='jugement-img'
onclick='openLightboxList($json,$i)'>";

        echo "<div class='jugement-content'>";
        echo "<div class='jugement-title'>$title</div>";
        echo "<div class='jugement-meta'>$label • {$img['competition_nom']}</div>";

        echo "<div class='badge-notes'>";
        foreach ($img['notes_array'] ?? [] as $n) {
            $color = '#f39c12';
            if ($n >= 16) $color = '#27ae60';
            elseif ($n <= 8) $color = '#e74c3c';
            echo "<span style='background:$color'>$n</span>";
        }
        echo "</div></div></div>";
    }

    echo "</div>";
}
?>


<?= $this->endSection() ?>