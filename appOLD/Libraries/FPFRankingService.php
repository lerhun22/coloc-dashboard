<?php

namespace App\Libraries;

class FPFRankingService
{
    public function compute(array $photos, object $competition): array
    {
        $clubs = [];

        foreach ($photos as $p) {

            // exclusion individuels
            if (str_starts_with($p['club_name'], 'Club #')) {
                continue;
            }

            $id = (int)$p['club_id'];

            if (!isset($clubs[$id])) {
                $clubs[$id] = [
                    'club_id'   => $id,
                    'club_name' => $p['club_name'],
                    'photos'    => []
                ];
            }

            $clubs[$id]['photos'][] = $p;
        }

        $limit = $competition->rules['photos_retained'] ?? null;

        foreach ($clubs as &$club) {

            if (empty($club['photos'])) {
                continue;
            }

            // tri décroissant
            usort($club['photos'], fn($a, $b) => $b['score'] <=> $a['score']);

            // fallback si pas défini
            $effectiveLimit = $limit ?? count($club['photos']);

            $selected = array_slice($club['photos'], 0, $effectiveLimit);

            $club['score'] = array_sum(array_column($selected, 'score'));

            // tie-break sécurisé float
            $club['nb_20'] = count(array_filter($selected, fn($p) => abs($p['score'] - 20) < 0.01));
            $club['nb_19'] = count(array_filter($selected, fn($p) => abs($p['score'] - 19) < 0.01));

            $club['nb_photos'] = count($selected);
        }

        // tri global
        usort($clubs, function ($a, $b) {

            // 1. score total
            if ($b['score'] !== $a['score']) {
                return $b['score'] <=> $a['score'];
            }

            // 2. 6e photo (N2)
            $a6 = $a['photos'][5]['score'] ?? 0;
            $b6 = $b['photos'][5]['score'] ?? 0;

            if ($b6 !== $a6) {
                return $b6 <=> $a6;
            }

            // 3. nombre de 20
            if ($b['nb_20'] !== $a['nb_20']) {
                return $b['nb_20'] <=> $a['nb_20'];
            }

            // 4. nombre de 19
            return $b['nb_19'] <=> $a['nb_19'];
        });

        // ranking brut
        $rank = 1;
        foreach ($clubs as &$club) {
            $club['rank'] = $rank++;
        }

        return $clubs;
    }
}
