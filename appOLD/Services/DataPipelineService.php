<?php

namespace App\Services;

class DataPipelineService
{
    public function getRowsClean(int $annee): array
    {
        $provider = new DataProvider();
        $rows = $provider->getAnnualData($annee);

        $dedup = [];

        foreach ($rows as $r) {

            // =====================================================
            // FILTRES MÉTIER
            // =====================================================

            if (empty($r['is_official'])) continue;
            if ($r['is_disqualified']) continue;
            if ($r['is_individual']) continue;

            if (empty($r['ean'])) continue;

            // =====================================================
            // DEDUP (EAN + COMPET)
            // =====================================================
            $key = $r['ean'] . '_' . $r['competition_id'];

            if (!isset($dedup[$key])) {
                $dedup[$key] = $r;
            } else {
                if ($r['note_totale'] > $dedup[$key]['note_totale']) {
                    $dedup[$key] = $r;
                }
            }
        }

        return array_values($dedup);
    }
}
