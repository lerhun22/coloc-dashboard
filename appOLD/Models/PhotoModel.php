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

    public function getPhotosWithContext(int $annee = null): array
    {
        $sql = "
        SELECT
            p.id AS photo_id,
            p.points,
            p.note,

            c.id AS competition_id,
            c.nom AS competition_nom,
            c.saison,
            c.type AS competition_type,

            pa.id AS participant_id,

            cl.id AS club_id,
            cl.nom AS club_nom,
            cl.urs_id AS ur

        FROM photos p

        LEFT JOIN competitions c 
            ON c.id = p.competitions_id

        LEFT JOIN participants pa 
            ON pa.id = p.participants_id

        LEFT JOIN clubs cl 
            ON cl.id = pa.clubs_id
    ";

        if ($annee) {
            $sql .= " WHERE c.saison = " . (int)$annee;
        }

        return $this->db->query($sql)->getResultArray();
    }

    public function computePresenceUR(array $rows, int $ur): array
    {
        $competitions = [];

        foreach ($rows as $row) {

            if ($row['competition_type'] != 2) {
                continue;
            }

            if ($row['ur'] != $ur) {
                continue;
            }

            $cid = $row['competition_id'];

            if (!isset($competitions[$cid])) {
                $competitions[$cid] = [
                    'id' => $cid,
                    'nom' => $row['competition_nom'],
                    'saison' => $row['saison'],
                    'clubs' => [],
                    'photos' => 0,
                ];
            }

            $competitions[$cid]['photos']++;

            $competitions[$cid]['clubs'][$row['club_id']] = $row['club_nom'];
        }

        /*
    =========================================================
    NORMALISATION
    =========================================================
    */
        foreach ($competitions as &$c) {
            $c['clubs_count'] = count($c['clubs']);
            $c['clubs_list'] = implode(', ', $c['clubs']);
            unset($c['clubs']);
        }

        return array_values($competitions);
    }
}
