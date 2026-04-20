<?php

namespace App\Services;

class DashboardURService
{
    public function build(array $rows, int $ur = 22): array
    {
        /*
        ============================================================
        🧱 1. AGRÉGATION PAR CLUB (clé du système)
        ============================================================
        */
        $classement = $this->aggregateByClub($rows);

        /*
        ============================================================
        🏆 2. TRI + RANG
        ============================================================
        */
        usort($classement, fn($a, $b) => $b['points'] <=> $a['points']);

        foreach ($classement as $i => &$c) {
            $c['rang'] = $i + 1;
        }

        /*
        ============================================================
        🎯 3. FILTRE UR22
        ============================================================
        */
        $urClubs = array_values(array_filter(
            $classement,
            fn($c) => ($c['ur'] ?? 0) == $ur
        ));

        /*
        ============================================================
        🏆 4. TOP NATIONAL
        ============================================================
        */
        $topNational = array_slice($classement, 0, 5);

        /*
        ============================================================
        🌍 5. CLASSEMENT PAR UR
        ============================================================
        */
        $urRanking = $this->buildURRanking($classement);

        /*
        ============================================================
        📊 6. GLOBAL
        ============================================================
        */
        $globalFPF = [
            'nb_clubs'   => count($classement),
            'nb_points'  => array_sum(array_column($classement, 'points')),
            'nb_images'  => array_sum(array_column($classement, 'total_images')),
        ];

        $globalUR = [
            'nb_clubs'   => count($urClubs),
            'nb_points'  => array_sum(array_column($urClubs, 'points')),
            'nb_images'  => array_sum(array_column($urClubs, 'total_images')),
        ];

        /*
        ============================================================
        📈 7. COMPARAISON
        ============================================================
        */
        $comparison = [
            'ratio_points' => $globalFPF['nb_points']
                ? round($globalUR['nb_points'] / $globalFPF['nb_points'] * 100, 1)
                : 0,

            'ratio_images' => $globalFPF['nb_images']
                ? round($globalUR['nb_images'] / $globalFPF['nb_images'] * 100, 1)
                : 0,
        ];

        $comparison['delta'] =
            $comparison['ratio_points'] - $comparison['ratio_images'];



        $matrix = $this->buildCompetitionMatrix($rows, $ur);


        /*
        ============================================================
        🧠 8. INSIGHTS (COLOC)
        ============================================================
        */
        $insights = $this->buildInsights($classement, $urClubs);

        return [
            'classementClubs' => $classement,
            'topNational'     => $topNational,
            'urClubs'         => $urClubs,
            'urRanking'       => $urRanking,
            'globalFPF'       => $globalFPF,
            'globalUR'        => $globalUR,
            'comparison'      => $comparison,
            'insights'        => $insights,
            'competitionMatrix' => $matrix,
        ];
    }

    /*
    ============================================================
    🧱 AGRÉGATION PAR CLUB
    ============================================================
    */
    private function aggregateByClub(array $rows): array
    {
        $clubs = [];

        foreach ($rows as $r) {

            $id = $r['club_id'];
            $level = $r['level'] ?? 0; // 1=R,2=N2,3=N1,4=CDF

            if (!isset($clubs[$id])) {
                $clubs[$id] = [
                    'club_id' => $id,
                    'nom' => $r['nom'],
                    'numero' => $r['numero'],
                    'ur' => $r['ur'],

                    'points' => 0,
                    'R' => 0,
                    'N2' => 0,
                    'N1' => 0,
                    'CDF' => 0,

                    'total_images' => 0,
                ];
            }

            $points = (float)$r['points'];

            $clubs[$id]['points'] += $points;
            $clubs[$id]['total_images'] += (int)$r['total_images'];

            // 🔥 dispatch par niveau


            switch ($level) {

                case 1: // R
                    $clubs[$id]['R'] += $points;
                    break;

                case 2: // N2
                    $clubs[$id]['N2'] += $points;
                    break;

                case 3: // CDF (confirmé par ton exemple)
                    $clubs[$id]['CDF'] += $points;
                    break;

                case 4: // N1
                    $clubs[$id]['N1'] += $points;
                    break;
            }
        }

        return array_values($clubs);
    }

    /*
    ============================================================
    🌍 CLASSEMENT PAR UR
    ============================================================
    */

    private function buildCompetitionMatrix(array $rows, int $ur = 22): array
    {
        $matrix = [];

        foreach ($rows as $r) {

            if (($r['ur'] ?? 0) != $ur) continue;

            $comp = $r['competition_nom'];
            $club = $r['numero'];
            $points = $r['points'];

            if (!isset($matrix[$comp])) {
                $matrix[$comp] = [];
            }

            if (!isset($matrix[$comp][$club])) {
                $matrix[$comp][$club] = 0;
            }

            $matrix[$comp][$club] += $points;
        }

        return $matrix;
    }


    private function buildURRanking(array $classement): array
    {
        $urs = [];

        foreach ($classement as $c) {

            $ur = $c['ur'] ?? 0;
            if (!$ur) continue;

            if (!isset($urs[$ur])) {
                $urs[$ur] = [
                    'ur' => $ur,
                    'points' => 0,
                    'clubs' => 0
                ];
            }

            $urs[$ur]['points'] += $c['points'];
            $urs[$ur]['clubs']++;
        }

        $urs = array_values($urs);

        usort($urs, fn($a, $b) => $b['points'] <=> $a['points']);

        return $urs;
    }

    /*
    ============================================================
    🧠 INSIGHTS COLOC
    ============================================================
    */
    private function buildInsights(array $classement, array $urClubs): array
    {
        /*
        🏆 UR22 dans top 50 national
        */
        $topUR22 = array_filter(
            $classement,
            fn($c) => ($c['ur'] ?? 0) == 22 && ($c['rang'] ?? 999) <= 50
        );

        /*
        ⚠️ Sous-performance
        */
        $underperforming = array_filter($urClubs, function ($c) {

            $images = $c['total_images'] ?? 0;
            $points = $c['points'] ?? 0;

            if ($images < 20) return false;

            return ($points / max($images, 1)) < 8;
        });

        /*
        ⚡ Efficience
        */
        foreach ($urClubs as &$c) {
            $c['efficiency'] = ($c['total_images'] ?? 0)
                ? round($c['points'] / $c['total_images'], 2)
                : 0;
        }

        usort($urClubs, fn($a, $b) => $b['efficiency'] <=> $a['efficiency']);

        return [
            'top_ur22_national' => array_values($topUR22),
            'underperforming'   => array_values($underperforming),
            'top_efficiency'    => array_slice($urClubs, 0, 10),
        ];
    }
}
