<?php

namespace App\Services;

class DataProviderClubs
{
    public function getAnnualData(int $annee): array
    {
        $db = \Config\Database::connect();

        return $db->table('classementclubs cc')
            ->select('
    cc.total as points,
    cc.nb_photos as total_images,
    cc.place as rank,
    c.id as club_id,
    c.nom as nom,
    c.numero,
    c.urs_id as ur,
    comp.nom as competition_nom,
    comp.type as level
')
            ->join('clubs c', 'c.id = cc.clubs_id')
            ->join('competitions comp', 'comp.id = cc.competitions_id')
            ->whereIn('comp.type', [2, 3, 4]) // N2, N1, CDF
            ->where('comp.saison', $annee)
            ->get()
            ->getResultArray();
    }
}
