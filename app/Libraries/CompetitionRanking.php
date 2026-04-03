<?php

namespace App\Libraries;

use Config\Database;

class CompetitionRanking
{
    public function compute($competitionId)
    {
        $db = Database::connect();

        $rows =
            $db->query("
                SELECT
                    p.id,
                    SUM(n.note) AS score
                FROM photos p
                LEFT JOIN notes n
                    ON n.photos_id = p.id
                WHERE p.competitions_id = ?
                GROUP BY p.id
                ORDER BY score DESC
            ", [$competitionId])
            ->getResultArray();


        $place = 1;

        foreach ($rows as $r) {
            $db->table('photos')
                ->where('id', $r['id'])
                ->update([
                    'place' => $place
                ]);

            $place++;
        }
    }
}
