<?php

namespace App\Services;

class DashboardURService
{
    public function build(array $rows, int $ur = 22): array
    {
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
        ];

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
        ];

        $comparison = [

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
                : 0
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
                $rows
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
        ];
    }


    /*
    ============================================================
    Agrégation clubs
    ============================================================
    */

    private function aggregateByClub(array $rows): array
    {
        $clubs = [];

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

                    'total_images' => 0
                ];
            }

            $points = (float)$r['points'];

            $clubs[$id]['points'] += $points;
            $clubs[$id]['total_images'] += (int)($r['total_images'] ?? 0);

            $level = strtoupper(trim((string)($r['level'] ?? '')));

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
                    $clubs[$id]['CDF'] += $points;
                    break;
            }
        }

        foreach ($clubs as &$club) {
            $club['N1_CDF'] = $club['N1'] + $club['CDF'];
        }

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

            $comp = trim($r['competition_nom']);
            $club = (string)$r['numero'];
            $points = (float)$r['points'];

            $level = strtoupper(
                trim((string)($r['level'] ?? ''))
            );

            if ($level === 'REGIONAL') {
                $target = &$regional;
            } else {
                $target = &$national;
            }

            if (!isset($target[$comp])) {
                $target[$comp] = [
                    'winner_club' => null,
                    'winner_author' => '—',
                    'winner_points' => 0,
                    'scores' => []
                ];
            }

            if (!isset($target[$comp]['scores'][$club])) {
                $target[$comp]['scores'][$club] = 0;
            }

            $target[$comp]['scores'][$club] += $points;

            if (
                $target[$comp]['scores'][$club] >
                $target[$comp]['winner_points']
            ) {
                $target[$comp]['winner_points'] =
                    $target[$comp]['scores'][$club];

                $target[$comp]['winner_club'] = $club;

                $target[$comp]['winner_author'] = $r['nom'];
            }
        }

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
}
