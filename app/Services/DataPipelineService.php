<?php

namespace App\Services;

class DataPipelineService
{
    public function getRowsClean(int $annee): array
    {
        $provider = new DataProvider();
        $rows = $provider->getAnnualData($annee);

        // Debug ponctuel
        // dd($rows);

        $clean = [];

        foreach ($rows as $r) {

            // Sécurité minimale
            if (empty($r['competition_id'])) continue;

            // OPTIONNEL : exclure clubs inconnus
            // if (empty($r['club_id'])) continue;

            $clean[] = [
                'club_id'         => (int) ($r['club_id'] ?? 0),
                'club_nom'        => $r['club_nom'] ?? '',
                'ur'              => (int) ($r['ur'] ?? 0),
                'points'          => (float) ($r['points'] ?? 0),
                'nb_photos'       => (int) ($r['nb_photos'] ?? 0),
                'place'           => (int) ($r['place'] ?? 0),
                'competition_id'  => (int) ($r['competition_id'] ?? 0),
                'competition_nom' => $r['competition_nom'] ?? '',
                'saison'          => $r['saison'] ?? '',
                'level'           => $r['level'] ?? '',
                'discipline'      => $r['discipline'] ?? '',
                'support'         => $r['support'] ?? '',
            ];
        }

        return $clean;
    }
}
