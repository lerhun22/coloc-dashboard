<?php

namespace App\Services;

class CompetitionStatsService
{
    public function compute(array $rows, bool $debug = false, int $urTarget = 22): array
    {
        $byLevel = $this->aggregateByLevel($rows);
        $matrix  = $this->buildClubMatrix($byLevel);

        // classement pondéré (CDF > N1 > N2 > R)
        usort($matrix, function ($a, $b) {
            return $b['score_weighted'] <=> $a['score_weighted'];
        });

        // ranking
        $rank = 1;
        foreach ($matrix as &$c) {
            $c['rank'] = $rank++;
        }

        return [
            'matrix' => $matrix,
            'byLevel' => $byLevel,
            'regional_disciplines' => $this->buildRegionalByDiscipline($rows),
            'regional_ur' => $this->buildRegionalUR($rows, $urTarget),
            'debug' => [
                'rows_input' => count($rows),
                'clubs' => count($matrix),
                'total_points' => array_sum(array_column($matrix, 'total')),
            ]
        ];
    }

    private function aggregateByLevel(array $rows): array
    {

        $data = [
            'REGIONAL' => [],
            'N2' => [],
            'N1' => [],
            'CDF' => [],
        ];

        foreach ($rows as $r) {
            if (empty($club) || $club == 0) continue;
            if ($r['is_individual']) continue;
            if ($r['is_disqualified']) continue;

            $level = $r['level'] ?? 'REGIONAL';
            $club  = $r['club_id'];

            if (!isset($data[$level][$club])) {
                $data[$level][$club] = [
                    'club_id'  => $club,
                    'club_nom' => $r['club_nom'],
                    'ur'       => $r['ur'] ?? 0,
                    'points'   => 0,
                    'images'   => 0,

                ];
            }

            // 🔥 toujours compter les points
            $points = $r['note_totale'];

            $data[$level][$club]['points'] += $points;
            $data[$level][$club]['images']++;
        }

        return $data;
    }

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

                $points = $stats['points'] ?? 0;

                $clubs[$clubId][$level] = $points;
                $clubs[$clubId]['total'] += $points;

                $clubs[$clubId]['score_weighted'] +=
                    $points * $weights[$level];
            }
        }

        return array_values($clubs);
    }

    private function buildRegionalByDiscipline(array $rows): array
    {
        $disciplines = [];

        foreach ($rows as $r) {

            if (($r['level'] ?? '') !== 'REGIONAL') continue;
            if ($r['is_individual']) continue;

            $nom = strtoupper($r['competition_nom']);

            preg_match('/(PM|PCN|IMN|IC|PN|IN|AV|QUAD)/', $nom, $match);
            $disc = $match[1] ?? 'AUTRE';

            $club = $r['club_id'];

            if (!isset($disciplines[$disc][$club])) {
                $disciplines[$disc][$club] = [
                    'club_nom' => $r['club_nom'],
                    'points'   => 0,
                ];
            }

            if ($r['is_selected']) {
                $disciplines[$disc][$club]['points'] += $r['note_totale'];
            }
        }

        return $disciplines;
    }

    private function buildRegionalUR(array $rows, int $urTarget): array
    {
        $result = [];

        foreach ($rows as $r) {

            // 🔥 uniquement régional
            if (($r['level'] ?? '') !== 'REGIONAL') continue;

            // 🔥 uniquement clubs UR cible
            if (($r['ur'] ?? 0) != $urTarget) continue;

            if ($r['is_individual']) continue;

            $discRaw = $r['discipline'] ?? 'UNKNOWN';
            $support = $r['support'] ?? 'UNKNOWN';

            $disc = match ($discRaw) {

                'MONOCHROME' => ($support === 'IP') ? 'IM' : 'PM',
                'COULEUR'    => ($support === 'IP') ? 'IC' : 'PC',
                'NATURE'     => ($support === 'IP') ? 'IN' : 'PN',

                'AUDIOVISUEL' => 'AV',
                'QUADRIMAGE'  => 'QUAD',

                default => 'AUTRE',
            };
            $club = $r['club_id'];

            if (!isset($result[$club])) {
                $result[$club] = [
                    'club_nom' => $r['club_nom'],
                    'PM' => 0,
                    'PC' => 0,
                    'IM' => 0,
                    'IC' => 0,
                    'PN' => 0,
                    'IN' => 0,
                    'AV' => 0,
                    'QUAD' => 0
                ];
            }

            // points
            $points = $r['note_totale'];

            if (isset($result[$club][$disc])) {
                $result[$club][$disc] += $points;
            }
        }

        return array_values($result);
    }
}
