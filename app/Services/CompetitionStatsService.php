<?php

namespace App\Services;

/**
 * ============================================================
 * CompetitionStatsService
 * ============================================================
 * Brique 1 : Classement clubs FIABLE
 *
 * - agrégation par niveau (R / N2 / N1 / CDF)
 * - cumul des points (sélection uniquement)
 * - score pondéré
 * - ranking final
 *
 * ============================================================
 */
class CompetitionStatsService
{

    public function compute(array $rows, bool $debug = false, int $urTarget = 22): array
    {
        // =====================================================
        // 🧱 1. AGRÉGATION PAR NIVEAU
        // =====================================================
        $byLevel = $this->aggregateByLevel($rows);

        // =====================================================
        // 🧱 2. MATRICE CLUBS (R / N2 / N1 / CDF)
        // =====================================================
        $matrix = $this->buildClubMatrix($byLevel);

        // =====================================================
        // 🟢 3. REGIONAL PAR COMPETITION + RANK
        // =====================================================
        $regionalByCompetition = [];

        foreach ($rows as $r) {

            if (($r['level'] ?? '') !== 'REGIONAL') continue;

            $club = (int)($r['club_id'] ?? 0);
            if ($club === 0) continue;

            $comp = $r['competition_nom'];

            if (!isset($regionalByCompetition[$comp][$club])) {
                $regionalByCompetition[$comp][$club] = [
                    'club_id'  => $club,
                    'club_nom' => $r['club_nom'],
                    'points'   => 0,
                ];
            }

            $regionalByCompetition[$comp][$club]['points'] += (float)$r['points'];
        }

        // tri + rank
        foreach ($regionalByCompetition as $comp => &$clubs) {

            uasort($clubs, fn($a, $b) => $b['points'] <=> $a['points']);

            $rank = 1;
            foreach ($clubs as &$c) {
                $c['rank'] = $rank++;
            }
        }
        unset($clubs);

        // =====================================================
        // 🔵 NATIONAL (uniquement clubs nationaux)
        // =====================================================
        $national = array_filter($matrix, function ($c) {
            return ($c['N2'] + $c['N1'] + $c['CDF']) > 0;
        });

        // reindex
        $national = array_values($national);

        // tri
        usort($national, function ($a, $b) {
            $aTotal = $a['N2'] + $a['N1'] + $a['CDF'];
            $bTotal = $b['N2'] + $b['N1'] + $b['CDF'];

            return $bTotal <=> $aTotal;
        });

        // ranking
        $rank = 1;
        foreach ($national as &$c) {
            $c['rank'] = $rank++;
            $c['total_national'] = $c['N2'] + $c['N1'] + $c['CDF'];
        }
        unset($c);

        // =====================================================
        // 🧪 5. DEBUG
        // =====================================================
        $debugData = [];

        if ($debug) {
            $debugData = [
                'rows_input'   => count($rows),
                'clubs'        => count($matrix),
                'total_points' => array_sum(array_column($matrix, 'total')),
            ];
        }

        // =====================================================
        // 🔁 6. RETURN FINAL
        // =====================================================
        return [
            'matrix' => $matrix,
            'regional_by_comp' => $regionalByCompetition,
            'national' => $national,
            'debug' => $debugData,
        ];
    }

    /**
     * ============================================================
     * AGRÉGATION PAR NIVEAU
     * ============================================================
     */
    private function aggregateByLevel(array $rows): array
    {
        $data = [];

        foreach ($rows as $r) {

            $club = (int)($r['club_id'] ?? 0);
            if ($club === 0) continue;

            // 🔥 normalisation niveaux
            $level = match ($r['level'] ?? '') {
                'REGIONAL' => 'R',
                'N2' => 'N2',
                'N1' => 'N1',
                'CDF', 'COUPE' => 'CDF',
                default => 'R'
            };

            $points = (float)($r['points'] ?? 0);

            // =====================================================
            // 🔴 CAS REGIONAL → MAX PAR DISCIPLINE
            // =====================================================
            if ($level === 'R') {

                $disc = $r['discipline'] ?? 'X';

                if (!isset($data['R'][$club][$disc])) {
                    $data['R'][$club][$disc] = [
                        'club_id'  => $club,
                        'club_nom' => $r['club_nom'],
                        'ur'       => $r['ur'] ?? 0,
                        'points'   => 0,
                    ];
                }

                // 🔥 garder la meilleure perf uniquement
                $data['R'][$club][$disc]['points'] = max(
                    $data['R'][$club][$disc]['points'],
                    $points
                );

                continue;
            }

            // =====================================================
            // 🟢 CAS N2 / N1 / CDF → CUMUL NORMAL
            // =====================================================
            if (!isset($data[$level][$club])) {
                $data[$level][$club] = [
                    'club_id'  => $club,
                    'club_nom' => $r['club_nom'],
                    'ur'       => $r['ur'] ?? 0,
                    'points'   => 0,
                ];
            }

            $data[$level][$club]['points'] += $points;
        }

        // =====================================================
        // 🔧 FLATTEN REGIONAL (discipline → total club)
        // =====================================================
        if (isset($data['R'])) {
            foreach ($data['R'] as $club => $disciplines) {

                $sum = 0;
                $clubNom = '';
                $ur = 0;

                foreach ($disciplines as $d) {
                    $sum += $d['points'];
                    $clubNom = $d['club_nom'];
                    $ur = $d['ur'];
                }

                $data['R'][$club] = [
                    'club_id'  => $club,
                    'club_nom' => $clubNom,
                    'ur'       => $ur,
                    'points'   => $sum,
                ];
            }
        }

        return $data;
    }
    /**
     * ============================================================
     * CONSTRUCTION MATRICE CLUBS
     * ============================================================
     */
    private function buildClubMatrix(array $data): array
    {
        $clubs = [];

        $weights = [
            'R'   => 1,
            'N2'  => 2,
            'N1'  => 3,
            'CDF' => 4,
        ];

        foreach ($data as $level => $clubsLevel) {

            foreach ($clubsLevel as $clubId => $stats) {

                if (!isset($clubs[$clubId])) {
                    $clubs[$clubId] = [
                        'club_id'  => $clubId,
                        'club_nom' => $stats['club_nom'],
                        'ur'       => $stats['ur'],

                        'R' => 0,
                        'N2' => 0,
                        'N1' => 0,
                        'CDF' => 0,
                        'total' => 0,


                        'score_weighted' => 0,
                    ];
                }

                $clubs[$clubId][$level] = $stats['points'];
                $clubs[$clubId]['total'] += $stats['points'];

                $clubs[$clubId]['score_weighted'] +=
                    $stats['points'] * $weights[$level];
            }
        }


        return array_values($clubs);
    }
}
