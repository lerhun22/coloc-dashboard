<?php

namespace App\Services;

/**
 * =========================================================
 * SyntheseService
 * =========================================================
 * Stats globales + compétitions (version propre)
 * =========================================================
 */
class SyntheseService
{
    public function computeGlobalStats(array $rows): array
    {
        $clubs = [];
        $totalPoints = 0;
        $nbImages = count($rows);

        foreach ($rows as $row) {

            if (!empty($row['club_id'])) {
                $clubs[$row['club_id']] = true;
            }

            $totalPoints += $row['points'] ?? 0;
        }

        return [
            'nb_clubs' => count($clubs),
            'nb_images' => $nbImages,
            'moyenne' => $nbImages > 0 ? round($totalPoints / $nbImages, 2) : 0,
        ];
    }

    /**
     * =========================================================
     * COMPETITIONS STATS (basé sur level)
     * =========================================================
     */
    public function computeCompetitionStats(array $rows): array
    {
        $competitions = [];

        foreach ($rows as $row) {

            $cid = $row['competition_id'];
            if (!$cid) continue;

            if (!isset($competitions[$cid])) {
                $competitions[$cid] = [
                    'competition_id' => $cid,
                    'nom' => $row['competition_nom'],

                    // 🔥 basé sur level (plus de parsing)
                    'type' => $this->mapLevel($row['level'] ?? ''),

                    'images' => 0,
                    'clubs' => [],
                    'clubs_ur22' => [],
                ];
            }

            $competitions[$cid]['images']++;

            if (!empty($row['club_id'])) {

                $competitions[$cid]['clubs'][$row['club_id']] = true;

                if (!empty($row['is_ur22'])) {
                    $competitions[$cid]['clubs_ur22'][$row['club_id']] = true;
                }
            }
        }

        /*
        =========================
        NORMALISATION
        =========================
        */
        foreach ($competitions as &$c) {

            $c['nb_clubs'] = count($c['clubs']);
            $c['nb_clubs_ur22'] = count($c['clubs_ur22']);

            unset($c['clubs'], $c['clubs_ur22']);
        }

        /*
        =========================
        TRI MÉTIER
        =========================
        */
        $order = ['UR', 'N2', 'N1', 'CdF'];

        usort($competitions, function ($a, $b) use ($order) {
            return array_search($a['type'], $order)
                <=> array_search($b['type'], $order);
        });

        return $competitions;
    }

    /**
     * =========================================================
     * MAP LEVEL → LABEL
     * =========================================================
     */
    private function mapLevel(string $level): string
    {
        return match ($level) {
            'REGIONAL' => 'UR',
            'N2' => 'N2',
            'N1' => 'N1',
            'CDF', 'COUPE' => 'CdF',
            default => 'Autre',
        };
    }
}
