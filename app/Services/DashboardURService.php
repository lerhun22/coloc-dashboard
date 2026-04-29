<?php

namespace App\Services;

class DashboardURService
{
    public function build(array $rows, ?int $ur = null): array

    {
        $ur ??= currentUR();
        /*
        ============================================================
        1. Agrégation clubs
        ============================================================
        */
        $classement = $this->aggregateByClub($rows);

        /*
        ============================================================
        2. Tri + rang
        ============================================================
        */
        usort(
            $classement,
            fn($a, $b) => $b['points'] <=> $a['points']
        );


        foreach ($classement as $i => &$c) {
            $c['rang'] = $i + 1;

            /*
            colonne pratique pour la vue
            */
            $c['N1_CDF'] =
                $c['N1'] + $c['CDF'];
        }
        unset($c);

        /*
        ============================================================
        3. Clubs UR
        ============================================================
        */
        $urClubs = array_values(
            array_filter(
                $classement,
                fn($c) =>
                (int)($c['ur'] ?? 0) === $ur
            )
        );

        /*
        ============================================================
        4. Top national
        ============================================================
        */
        $topNational =
            array_slice(
                $classement,
                0,
                10
            );

        /*
        ============================================================
        5. Classement UR
        ============================================================
        */
        $urRanking =
            $this->buildURRanking(
                $classement
            );

        /*
        ============================================================
        6. KPIs
        ============================================================
        */
        /*
============================================================
6. KPIs
============================================================
*/

        /*
------------------------------------------------------------
NATIONAL
------------------------------------------------------------
*/
        $globalFPF = [

            'nb_clubs' =>
            count($classement),

            'nb_points' =>
            array_sum(
                array_column(
                    $classement,
                    'points'
                )
            ),

            'nb_images' =>
            array_sum(
                array_column(
                    $classement,
                    'total_images'
                )
            ),

            // si disponible dans dataset clubs
            'nb_authors' =>
            array_sum(
                array_map(
                    fn($c) =>
                    $c['nb_auteurs']
                        ?? $c['auteurs']
                        ?? $c['nb_authors']
                        ?? 0,
                    $classement
                )
            ),
        ];


        /*
------------------------------------------------------------
UR22
------------------------------------------------------------
*/
        $globalUR = [

            'nb_clubs' =>
            count($urClubs),

            'nb_points' =>
            array_sum(
                array_column(
                    $urClubs,
                    'points'
                )
            ),

            'nb_images' =>
            array_sum(
                array_column(
                    $urClubs,
                    'total_images'
                )
            ),

            'nb_authors' =>
            array_sum(
                array_map(
                    fn($c) =>
                    $c['nb_auteurs']
                        ?? $c['auteurs']
                        ?? $c['nb_authors']
                        ?? 0,
                    $classement
                )
            ),
        ];


        /*
------------------------------------------------------------
Clubs engagés en compétitions nationales
(au moins 1 point ou 1 image en national)
------------------------------------------------------------
*/
        $clubsEngaged = count(
            array_filter(
                $urClubs,
                fn($club) => ($club['points'] ?? 0) > 0
                    || ($club['total_images'] ?? 0) > 0
            )
        );

        $engagementRate =
            $globalUR['nb_clubs']
            ? round(
                $clubsEngaged
                    /
                    $globalUR['nb_clubs']
                    * 100,
                0
            )
            : 0;


        /*
------------------------------------------------------------
COMPARAISON / KPI CARDS
------------------------------------------------------------
*/
        $comparison = [

            // ancien poids UR conservé
            'ratio_points' =>
            $globalFPF['nb_points']
                ? round(
                    $globalUR['nb_points']
                        /
                        $globalFPF['nb_points']
                        * 100,
                    1
                )
                : 0,

            'ratio_images' =>
            $globalFPF['nb_images']
                ? round(
                    $globalUR['nb_images']
                        /
                        $globalFPF['nb_images']
                        * 100,
                    1
                )
                : 0,

            /*
    TOP UR
    */
            'rank_ur' =>
            $urRank ?? null,

            'nb_authors_ranked' =>
            $globalUR['nb_authors'],


            /*
    FOCUS UR22
    */
            'clubs_engaged' =>
            $clubsEngaged,

            'engagement_rate' =>
            $engagementRate,
        ];


        $comparison['delta'] =
            $comparison['ratio_points']
            -
            $comparison['ratio_images'];

        /*
        ============================================================
        7. Matrices compétitions
        ============================================================
        */
        $matrices =
            $this->buildCompetitionMatrices(
                $rows,
                $ur
            );

        $clubColumns =
            array_column(
                $urClubs,
                'numero'
            );

        sort($clubColumns);

        /*
        ============================================================
        8. Insights
        ============================================================
        */
        $insights =
            $this->buildInsights(
                $classement,
                $urClubs,
                $ur
            );


        $clubLabels = [];

        /*
labels depuis clubs UR
*/
        foreach ($urClubs as $club) {
            $clubLabels[$club['numero']] = $club['nom'];
        }

        /*
compléter depuis matrices
*/
        foreach (
            [$matrices['national'], $matrices['regional']]
            as $matrix
        ) {
            foreach ($matrix as $comp => $data) {

                $winner =
                    $data['winner_club']
                    ?? null;

                if (
                    $winner &&
                    !isset($clubLabels[$winner])
                ) {
                    $clubLabels[$winner] = 'Club ' . $winner;
                }

                foreach (
                    $data['scores'] ?? []
                    as $club => $pts
                ) {
                    if (
                        !isset($clubLabels[$club])
                    ) {
                        $clubLabels[$club] = 'Club ' . $club;
                    }
                }
            }
        }

        /*
        ============================================================
        9. observatoire clubs
        ============================================================
        */

        $clubObservatory =
            $this->buildClubObservatory(
                $urClubs
            );

        $obsSummary =
            $this->buildObservatorySummary(
                $clubObservatory
            );
        /*
============================================================
10. Capital d'excellence (FIL2B)
============================================================
*/
        $laureatePodiums =
            $this->buildLaureatePodiums();

        return [

            'classementClubs'
            => $classement,

            'topNational'
            => $topNational,

            'urClubs'
            => $urClubs,

            'urRanking'
            => $urRanking,

            'globalFPF'
            => $globalFPF,

            'globalUR'
            => $globalUR,

            'comparison'
            => $comparison,

            'insights'
            => $insights,

            'competitionMatrixNational'
            => $matrices['national'],

            'competitionMatrixRegional'
            => $matrices['regional'],

            'clubColumns'
            => $clubColumns,

            'clubLabels' => $clubLabels,

            'clubObservatory' => $clubObservatory,
            'obsSummary'      => $obsSummary,
            'dashboardLaureates' => $laureatePodiums,
        ];
    }


    /*
    ============================================================
    Agrégation clubs
    ============================================================
    */

    private function buildClubObservatory(
        array $urClubs
    ): array {
        $totalImages =
            array_sum(
                array_column(
                    $urClubs,
                    'total_images'
                )
            );

        $totalPoints =
            array_sum(
                array_column(
                    $urClubs,
                    'points'
                )
            );

        foreach ($urClubs as &$c) {

            $partImages =
                $totalImages
                ? ($c['total_images'] / $totalImages) * 100
                : 0;

            $partPoints =
                $totalPoints
                ? ($c['points'] / $totalPoints) * 100
                : 0;

            $conversion =
                $partImages > 0
                ? ($partPoints / $partImages) * 100
                : 100;

            $c['part_images'] =
                round($partImages, 2);

            $c['part_points'] =
                round($partPoints, 2);

            $c['conversion'] =
                round($conversion, 1);

            $c['motor'] =
                round(
                    ($partImages + $partPoints) / 2,
                    2
                );

            /*
        🔥 manquait ici
        */
            $c['observatory_score'] =
                round(
                    $c['conversion']
                        +
                        ($c['elite_bonus'] ?? 0),
                    1
                );
        }

        unset($c);

        usort(
            $urClubs,
            fn($a, $b) =>
            $b['observatory_score']
                <=>
                $a['observatory_score']
        );

        foreach ($urClubs as $i => &$c) {
            $c['rang_obs'] = $i + 1;
        }

        unset($c);

        return $urClubs;
    }

    private function buildObservatorySummary(
        array $clubs
    ): array {

        return [

            'weight' => $clubs[0]['part_images'] ?? 0,

            'conversion' => round(
                array_sum(
                    array_column($clubs, 'conversion')
                ) / max(1, count($clubs)),
                1
            ),

            'motor' => $clubs[0]['motor'] ?? 0

        ];
    }

    /*
============================================================
Agrégation clubs
============================================================
*/

    private function aggregateByClub(
        array $rows
    ): array {
        $clubs = [];

        /*
    ==========================
    agrégation brute
    ==========================
    */

        foreach ($rows as $r) {

            $id = (int)$r['club_id'];

            if (!isset($clubs[$id])) {

                $clubs[$id] = [
                    'club_id' => $id,
                    'nom' => $r['nom'],
                    'numero' => $r['numero'],
                    'ur' => (int)$r['ur'],

                    'points' => 0,

                    'R' => 0,
                    'N2' => 0,
                    'N1' => 0,
                    'CDF' => 0,

                    'total_images' => 0,

                    'authors' =>
                    (int)($r['authors'] ?? 0),

                    'motor_authors' =>
                    (int)($r['motor_authors'] ?? 0),
                ];
            }

            $points = (float)$r['points'];

            $clubs[$id]['points'] += $points;

            $clubs[$id]['total_images']
                += (int)($r['total_images'] ?? 0);

            $level = strtoupper(
                trim(
                    (string)($r['level'] ?? '')
                )
            );

            switch ($level) {

                case 'REGIONAL':
                    $clubs[$id]['R'] += $points;
                    break;

                case 'N2':
                    $clubs[$id]['N2'] += $points;
                    break;

                case 'N1':
                    $clubs[$id]['N1'] += $points;
                    break;

                case 'CDF':
                case 'COUPE':
                    $clubs[$id]['CDF'] += $points;
                    break;
            }
        }


        /*
    ==========================
    métriques observatoire
    ==========================
    */

        $totalImages =
            array_sum(
                array_column(
                    $clubs,
                    'total_images'
                )
            );

        $totalPoints =
            array_sum(
                array_column(
                    $clubs,
                    'points'
                )
            );


        foreach ($clubs as &$c) {

            $c['weight_pct'] =
                $totalImages
                ? round(
                    100 *
                        $c['total_images'] /
                        $totalImages,
                    2
                )
                : 0;


            $pointsPct =
                $totalPoints
                ? (
                    100 *
                    $c['points'] /
                    $totalPoints
                )
                : 0;


            $c['conversion'] =
                $c['weight_pct'] > 0
                ? round(
                    (
                        $pointsPct
                        /
                        $c['weight_pct']
                    ) * 100,
                    1
                )
                : 100;


            $authors = max(
                1,
                (int)$c['authors']
            );

            $c['intensity'] = round(
                $c['total_images'] /
                    $authors,
                1
            );


            /*
        nouveau depth index
        */
            $c['depth_pct'] =
                $c['authors'] > 0
                ? round(
                    100 *
                        $c['motor_authors']
                        /
                        $c['authors'],
                    1
                )
                : 0;


            /*
        bonus élite
        */
            $bonus = 0;

            if ($c['N1'] > 0) {
                $bonus += 3;
            }

            if ($c['CDF'] > 0) {
                $bonus += 5;
            }

            $c['elite_bonus'] = $bonus;


            $depthBonus = 0;

            if ($c['depth_pct'] >= 70) {
                $depthBonus = 5;
            } elseif ($c['depth_pct'] >= 50) {
                $depthBonus = 3;
            } elseif ($c['depth_pct'] >= 30) {
                $depthBonus = 1;
            }

            $c['global_index'] = round(
                $c['conversion']
                    +
                    $bonus
                    +
                    $depthBonus,
                1
            );


            /*
        profils enrichis
        */

            /*
============================================================
Typologie finale V1 (stable)
============================================================
*/

            /* faibles conversions -> toujours avant tout */
            if (
                $c['conversion'] < 99
            ) {

                $c['profile'] = 'Sous potentiel';
            }

            /* gros clubs + élite distribuée */ elseif (
                $c['weight_pct'] >= 10 &&
                $c['conversion'] >= 102 &&
                $c['depth_pct'] >= 35
            ) {

                $c['profile'] = 'Locomotive élite';
            }

            /* excellence largement répartie */ elseif (
                $c['depth_pct'] >= 60 &&
                $c['conversion'] >= 100
            ) {

                $c['profile'] = 'Elite diffuse';
            }

            /* un ou peu de moteurs tirent le club */ elseif (
                $c['motor_authors'] >= 1 &&
                $c['depth_pct'] < 20 &&
                $c['conversion'] >= 100
            ) {

                $c['profile'] = 'Elite concentrée';
            }

            /* gros collectif structurant */ elseif (
                $c['weight_pct'] >= 10 &&
                $c['conversion'] >= 100
            ) {

                $c['profile'] = 'Moteur collectif';
            }

            /* pas de relais élite : pas convertisseur premium */ elseif (
                $c['motor_authors'] == 0
            ) {

                $c['profile'] = 'Equilibré';
            }

            /* petits clubs très efficients */ elseif (
                $c['conversion'] >= 103 &&
                $c['weight_pct'] < 5
            ) {

                $c['profile'] = 'Convertisseur';
            } else {

                $c['profile'] = 'Equilibré';
            }

            $c['N1_CDF'] =
                $c['N1']
                +
                $c['CDF'];
        }

        unset($c);


        usort(
            $clubs,
            fn($a, $b) =>
            $b['global_index']
                <=>
                $a['global_index']
        );

        foreach ($clubs as $i => &$c) {
            $c['rang_obs'] = $i + 1;
        }

        unset($c);

        return array_values($clubs);
    }
    /*
    ============================================================
    Matrices compétitions
    ============================================================
    */

    private function buildCompetitionMatrices(array $rows): array
    {
        $national = [];
        $regional = [];

        foreach ($rows as $r) {

            $comp   = trim((string)$r['competition_nom']);
            $club   = (string)$r['numero'];
            $points = (float)$r['points'];

            $level = strtoupper(
                trim((string)($r['level'] ?? ''))
            );

            /*
        référence correcte
        */
            if ($level === 'REGIONAL') {
                $target = &$regional;
            } else {
                $target = &$national;
            }

            if (!isset($target[$comp])) {

                $target[$comp] = [
                    'winner_club'   => null,
                    'winner_author' => null,
                    'winner_points' => 0,
                    'scores'        => []
                ];
            }

            /*
        cumul points club
        */
            if (
                !isset(
                    $target[$comp]['scores'][$club]
                )
            ) {
                $target[$comp]['scores'][$club] = 0;
            }

            $target[$comp]['scores'][$club]
                += $points;


            /*
        club gagnant
        */
            if (
                $target[$comp]['scores'][$club]
                >
                $target[$comp]['winner_points']
            ) {

                $target[$comp]['winner_points'] =
                    $target[$comp]['scores'][$club];

                $target[$comp]['winner_club'] =
                    $club;

                /*
            essayer plusieurs champs auteur
            */
                $target[$comp]['winner_author'] =
                    $r['participant_nom']
                    ?? $r['participant_name']
                    ?? $r['author_name']
                    ?? $r['auteur_nom']
                    ?? null;
            }


            /*
        fallback nom club
        */
            if (
                empty($target[$comp]['winner_author'])
            ) {
                $target[$comp]['winner_author'] =
                    $r['nom'] ?? null;
            }
        }

        ksort($national);
        ksort($regional);

        return [
            'national' => $national,
            'regional' => $regional
        ];
    }

    /*
    ============================================================
    Classement UR
    ============================================================
    */
    private function buildURRanking(
        array $classement
    ): array {
        $urs = [];

        foreach ($classement as $c) {

            $ur =
                (int)(
                    $c['ur'] ?? 0
                );

            if (!$ur) {
                continue;
            }

            if (!isset($urs[$ur])) {

                $urs[$ur] = [
                    'ur' => $ur,
                    'points' => 0,
                    'clubs' => 0
                ];
            }

            $urs[$ur]['points']
                += $c['points'];

            $urs[$ur]['clubs']++;
        }

        $urs =
            array_values($urs);

        usort(
            $urs,
            fn($a, $b) =>
            $b['points']
                <=>
                $a['points']
        );

        return $urs;
    }


    /*
    ============================================================
    Insights
    ============================================================
    */
    private function buildInsights(
        array $classement,
        array $urClubs,
        int $ur
    ): array {
        $topUR =
            array_filter(
                $classement,
                fn($c) =>
                $c['ur'] == $ur
                    &&
                    $c['rang'] <= 50
            );

        $underperforming =
            array_filter(
                $urClubs,
                function ($c) {

                    if (
                        $c['total_images'] < 20
                    ) {
                        return false;
                    }

                    return (
                        $c['points']
                        /
                        max(
                            1,
                            $c['total_images']
                        )
                    ) < 8;
                }
            );

        foreach ($urClubs as &$c) {

            $c['efficiency'] =
                $c['total_images']
                ?
                round(
                    $c['points']
                        /
                        $c['total_images'],
                    2
                )
                :
                0;
        }
        unset($c);

        usort(
            $urClubs,
            fn($a, $b) =>
            $b['efficiency']
                <=>
                $a['efficiency']
        );

        return [

            'top_ur_national'
            =>
            array_values(
                $topUR
            ),

            'underperforming'
            =>
            array_values(
                $underperforming
            ),

            'top_efficiency'
            =>
            array_slice(
                $urClubs,
                0,
                10
            )
        ];
    }

    /*
============================================================
FIL2B — Auteurs lauréats contextualisés
============================================================
*/
    private function buildLaureatePodiums(): array
    {
        $db = \Config\Database::connect();

        $rows = $db->query("
        SELECT
            c.nom competition,
            ca.place,
            pa.nom,
            pa.prenom,
            cl.numero club,
            ca.total,
            ca.nb_photos,
            x.field_size

        FROM classementauteurs ca

        JOIN competitions c
            ON c.id = ca.competitions_id

        JOIN participants pa
            ON pa.id = ca.participants_id

        JOIN clubs cl
            ON cl.id = pa.clubs_id

        JOIN (
            SELECT
                competitions_id,
                COUNT(*) field_size
            FROM photos
            GROUP BY competitions_id
        ) x
            ON x.competitions_id = ca.competitions_id

        WHERE ca.place IN (1,2,3)
          AND ca.total > 0

        ORDER BY c.nom, ca.place
    ")->getResultArray();

        $out = [];

        foreach ($rows as $r) {

            $comp = $r['competition'];

            if (!isset($out[$comp])) {

                $field = (int)$r['field_size'];

                if ($field >= 900) {
                    $label = '🔥 Elite';
                    $class = 'perf-good';
                } elseif ($field >= 700) {
                    $label = '▲ Haute';
                    $class = 'perf-good';
                } elseif ($field >= 300) {
                    $label = '● Dense';
                    $class = 'perf-mid';
                } else {
                    $label = '○ Spécial';
                    $class = 'perf-low';
                }

                $out[$comp] = [
                    'competition'    => $comp,
                    'field_size'     => $field,
                    'density_label'  => $label,
                    'density_class'  => $class,
                    'gold'           => null,
                    'silver'         => null,
                    'bronze'         => null,
                ];
            }

            $slot = match ((int)$r['place']) {
                1 => 'gold',
                2 => 'silver',
                3 => 'bronze',
                default => null
            };

            /*
        Sprint 1 :
        si ex-aequo, garder le premier rencontré
        */
            if ($slot && empty($out[$comp][$slot])) {

                $out[$comp][$slot] = [
                    'author' =>
                    trim($r['prenom'] . ' ' . $r['nom']),
                    'club' =>
                    $r['club'],
                    'total' =>
                    (int)$r['total'],
                    'nb_photos' =>
                    (int)$r['nb_photos']
                ];
            }
        }

        return array_values($out);
    }
}