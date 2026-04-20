<?php

namespace App\Services;

class SeasonService
{
    public function getCurrentSeason($db): int
    {
        $row = $db->query("
            SELECT MAX(saison) as max_saison 
            FROM competitions
        ")->getRowArray();

        return (int)($row['max_saison'] ?? date('Y'));
    }
}
