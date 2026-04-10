<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotoModel extends Model
{
    protected $table = 'photos';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [

        'id',

        'ean',

        'competitions_id',

        'participants_id',

        'titre',

        'statut',

        'place',

        'note_totale',

        'saisie',

        'retenue',

        'medailles_id',

        'passage',

        'disqualifie'

    ];


    public function getPresenceUR(int $ur = 22): array
    {
        return $this->db->query("
        SELECT
            c.id,
            c.nom,
            c.saison,

            COUNT(DISTINCT cl.id) AS clubs_count,

            COUNT(CASE WHEN cl.id IS NOT NULL THEN p.id END) AS photos,

            GROUP_CONCAT(DISTINCT cl.nom ORDER BY cl.nom SEPARATOR ', ') AS clubs_list

        FROM competitions c

        LEFT JOIN photos p 
            ON p.competitions_id = c.id

        LEFT JOIN participants pa 
            ON pa.id = p.participants_id

        LEFT JOIN clubs cl 
            ON cl.id = pa.clubs_id
            AND cl.urs_id = ?

        WHERE c.type = 2

        GROUP BY c.id
        ORDER BY c.saison DESC
    ", [$ur])->getResultArray();
    }
}
