<?php

namespace App\Models;

use CodeIgniter\Model;

class CompetitionModel extends Model
{
    protected $table = 'competitions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id',
        'numero',
        'type',
        'urs_id',
        'saison',
        'nom',
        'date_competition',
        'max_photos_club',
        'max_photos_auteur',
        'param_photos_club',
        'param_photos_auteur',
        'quota',
        'note_min',
        'note_max',
        'nb_auteurs_ur_n2',
        'nb_clubs_ur_n2',
        'pte',
        'nature'
    ];

    protected $returnType = 'array';


    /* =============================
       Stats pour UNE compétition
    ============================= */

    public function getCompetitionStats($id)
    {
        return $this->db->table('competitions c')
            ->select("
                c.*,
                COUNT(p.id) AS photo_count,
                COUNT(DISTINCT pa.id) AS author_count,
                COUNT(DISTINCT cl.id) AS club_count,
                COUNT(DISTINCT CASE WHEN pa.clubs_id IS NULL THEN pa.id END) AS federation_count,
                ROUND(COUNT(p.id) / NULLIF(COUNT(DISTINCT pa.id),0),2) AS avg_photos_per_author,
                ROUND(COUNT(p.id) / NULLIF(COUNT(DISTINCT cl.id),0),2) AS avg_photos_per_club
            ")
            ->join('photos p', 'p.competitions_id = c.id', 'left')
            ->join('participants pa', 'pa.id = p.participants_id', 'left')
            ->join('clubs cl', 'cl.id = pa.clubs_id', 'left')
            ->where('c.id', $id)
            ->groupBy('c.id')
            ->get()
            ->getRowArray();
    }


    /* =============================
       Stats pour LISTE compétitions
    ============================= */

    public function getCompetitionsWithCount()
    {
        return $this->db->table('competitions c')
            ->select("
                c.*,
                COUNT(p.id) AS photo_count,
                COUNT(DISTINCT pa.id) AS author_count,
                COUNT(DISTINCT cl.id) AS club_count,
                COUNT(DISTINCT CASE WHEN pa.clubs_id IS NULL THEN pa.id END) AS federation_count,
                ROUND(COUNT(p.id) / NULLIF(COUNT(DISTINCT pa.id),0),2) AS avg_photos_per_author,
                ROUND(COUNT(p.id) / NULLIF(COUNT(DISTINCT cl.id),0),2) AS avg_photos_per_club
            ")
            ->join('photos p', 'p.competitions_id = c.id', 'left')
            ->join('participants pa', 'pa.id = p.participants_id', 'left')
            ->join('clubs cl', 'cl.id = pa.clubs_id', 'left')
            ->groupBy('c.id')
            ->orderBy('c.nom')
            ->get()
            ->getResultArray();
    }
}
