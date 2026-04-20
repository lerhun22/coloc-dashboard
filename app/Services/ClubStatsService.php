<?php

namespace App\Services;

class ClubStatsService
{
    public function compute(array $rows): array
    {
        $clubs = [];
        $seen = [];

        foreach ($rows as $r) {

            /*
            =====================================================
            🔥 FILTRE UR22 (participant OU EAN)
            =====================================================
            */
            $isUR22 =
                ($r['participant_ur'] ?? null) == 22
                || (!empty($r['ean']) && substr($r['ean'], 0, 2) == '22');

            if (!$isUR22) continue;

            /*
            =====================================================
            🔥 DEDUP MÉTIER (EAN + COMPETITION_ID)
            =====================================================
            */
            if (empty($r['ean']) || empty($r['competition_id'])) continue;

            $key = $r['ean'] . '_' . $r['competition_id'];

            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            /*
            =====================================================
            CLUB
            =====================================================
            */
            $clubId = $r['club_key'] ?? null;
            if (!$clubId) continue;

            /*
            =====================================================
            🔥 CLASSIFICATION COMPÉTITION
            =====================================================
            */
            $level = $this->classifyCompetition(
                $r['competition_nom'],
                $r['competition_ur']
            );

            if (!$level) continue;

            if (!isset($clubs[$clubId])) {
                $clubs[$clubId] = $this->initClub($r);
            }

            $this->accumulate($clubs[$clubId], $r, $level);
        }

        /*
        ============================================================
        🔥 TRI COMPÉTITIONS
        ============================================================
        */
        foreach ($clubs as &$club) {

            foreach (['cdf', 'n1', 'n2', 'r'] as $lvl) {

                if (!isset($club[$lvl])) continue;

                $club[$lvl]['competitions'] = array_values($club[$lvl]['competitions']);

                usort(
                    $club[$lvl]['competitions'],
                    fn($a, $b) => $b['points'] <=> $a['points']
                );
            }
        }
        unset($club);

        /*
============================================================
👤 CALCUL NB AUTEURS
============================================================
*/
        foreach ($clubs as &$club) {
            $club['nb_auteurs'] = count($club['auteurs'] ?? []);
            unset($club['auteurs']); // clean
        }
        unset($club);

        return array_values($clubs);
    }

    /*
    ============================================================
    🎯 CLASSIFICATION ROBUSTE
    ============================================================
    */
    private function classifyCompetition($name, $urs_id): ?string
    {
        $n = mb_strtolower($name);
        $n = iconv('UTF-8', 'ASCII//TRANSLIT', $n);
        $n = trim($n);

        /*
        =====================================================
        ❌ EXCLUSIONS
        =====================================================
        */
        if (
            str_contains($n, 'challenge') ||
            str_contains($n, 'defi') ||
            str_contains($n, 'pcvm') ||
            str_contains($n, 'deverminage')
        ) {
            return null;
        }

        /*
        =====================================================
        🟥 NATIONAL
        =====================================================
        */
        if (preg_match('/coupe|france|cdf/', $n)) return 'cdf';
        if (preg_match('/national\s*1/', $n)) return 'n1';
        if (preg_match('/national\s*2/', $n)) return 'n2';

        /*
        =====================================================
        🟧 REGIONAL UR22
        =====================================================
        */
        if (trim((string)$urs_id) == '22') {

            if (str_contains($n, 'papier')) return 'r';
            if (str_contains($n, 'image projete')) return 'r'; // 🔥 robuste
            if (str_contains($n, 'quadrimage')) return 'r';
            if (str_contains($n, 'auteur')) return 'r';
            if (str_contains($n, 'audiovisuel')) return 'r';
        }

        return null;
    }

    /*
    ============================================================
    🏗 INIT CLUB
    ============================================================
    */
    private function initClub($r): array
    {
        return [
            'nom' => $r['club_nom'],
            'numero' => $r['club_numero'] ?? $r['club_key'],

            'cdf' => $this->initLevel(),
            'n1'  => $this->initLevel(),
            'n2'  => $this->initLevel(),
            'r'   => $this->initLevel(),

            'total_images' => 0,
            'total_points' => 0,

            // 🔥 AJOUT
            'auteurs' => []
        ];
    }

    /*
    ============================================================
    🧱 INIT LEVEL
    ============================================================
    */
    private function initLevel(): array
    {
        return [
            'count' => 0,
            'competitions' => [],
            'images' => 0,
            'points' => 0
        ];
    }

    /*
    ============================================================
    ➕ ACCUMULATION
    ============================================================
    */
    private function accumulate(&$club, $r, $level)
    {
        if (!isset($club[$level])) {
            $club[$level] = $this->initLevel();
        }

        $compName = $r['competition_nom'];
        $points   = (float)($r['points'] ?? 0);

        if (!isset($club[$level]['competitions'][$compName])) {
            $club[$level]['competitions'][$compName] = [
                'nom' => $compName,
                'points' => 0,
                'images' => 0
            ];

            $club[$level]['count']++;
        }

        $club[$level]['competitions'][$compName]['points'] += $points;
        $club[$level]['competitions'][$compName]['images']++;

        $club[$level]['images']++;
        $club[$level]['points'] += $points;

        $club['total_images']++;
        $club['total_points'] += $points;

        /*
    =====================================================
    👤 AUTEURS (clé unique)
    =====================================================
    */

        $auteurId = $r['auteur_id'] ?? null;

        if ($auteurId) {
            $club['auteurs'][$auteurId] = true;
        }
    }
}
