<?php

namespace App\Services;

use Config\Database;

class DashboardURService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

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

        WHERE 
            UPPER(c.nom) LIKE '%NATIONAL%'
            OR UPPER(c.nom) LIKE '%COUPE DE FRANCE%'

        GROUP BY c.id
        ORDER BY c.saison DESC
    ", [$ur])->getResultArray();
    }

    public function getMatriceUR(int $ur = 22): array
    {
        $rows = $this->db->query("
        SELECT
            c.id,
            c.nom,

            COUNT(DISTINCT cl.id) AS clubs_count

        FROM competitions c

        LEFT JOIN photos p 
            ON p.competitions_id = c.id

        LEFT JOIN participants pa 
            ON pa.id = p.participants_id

        LEFT JOIN clubs cl 
            ON cl.id = pa.clubs_id
            AND cl.urs_id = ?

        WHERE 
            UPPER(c.nom) LIKE '%NATIONAL%'
            OR UPPER(c.nom) LIKE '%COUPE DE FRANCE%'

        GROUP BY c.id
    ", [$ur])->getResultArray();

        // 🧩 matrice vide
        $matrice = [
            'N1'  => ['MONO' => 0, 'COULEUR' => 0, 'NATURE' => 0],
            'N2'  => ['MONO' => 0, 'COULEUR' => 0, 'NATURE' => 0],
            'CdF' => ['MONO' => 0, 'COULEUR' => 0, 'NATURE' => 0],
        ];

        foreach ($rows as $r) {

            $niveau = \App\Helpers\CompetitionMapper::niveau($r['nom']);
            $categorie = \App\Helpers\CompetitionMapper::categorie($r['nom']);

            if (!$niveau || !$categorie) continue;

            if (!isset($matrice[$niveau])) continue;
            if (!isset($matrice[$niveau][$categorie])) continue;

            $matrice[$niveau][$categorie] = (int)$r['clubs_count'];
        }

        return $matrice;
    }
    public function getSyntheseClubs(int $ur = 22): array
    {
        $rows = $this->db->query("
        SELECT
            cl.nom AS club_nom,
            c.nom AS competition_nom,
            cc.place

        FROM classementclubs cc

        JOIN clubs cl ON cl.id = cc.clubs_id
        JOIN competitions c ON c.id = cc.competitions_id

        WHERE cl.urs_id = ?

        AND (
            UPPER(c.nom) LIKE '%NATIONAL%'
            OR UPPER(c.nom) LIKE '%COUPE DE FRANCE%'
        )

    ", [$ur])->getResultArray();

        $result = [];

        foreach ($rows as $r) {

            $club = $r['club_nom'];
            $nom  = $r['competition_nom'];
            $place = $r['place'];

            $niveau    = \App\Helpers\CompetitionMapper::niveau($nom);
            $support   = \App\Helpers\CompetitionMapper::support($nom);
            $categorie = \App\Helpers\CompetitionMapper::categorie($nom);

            // 🔧 INIT CLUB
            if (!isset($result[$club])) {
                $result[$club] = [
                    'N1' => null,
                    'N2' => null,
                    'CdF' => null,
                    'total' => 0,
                    'competitions' => [],

                    // 🔥 colonnes COULEUR
                    'N1_P_COULEUR'  => null,
                    'N1_IP_COULEUR' => null,
                    'N2_P_COULEUR'  => null,
                    'N2_IP_COULEUR' => null,
                    'CDF_P_COULEUR' => null,
                    'CDF_IP_COULEUR' => null,
                ];
            }

            // 🎯 NIVEAU GLOBAL
            if ($niveau && isset($result[$club][$niveau])) {
                $result[$club][$niveau] = $place;
            }

            // 📊 TOTAL
            $result[$club]['total']++;

            // 🏷 LISTE COMPÉTITIONS (SANS DOUBLON)
            $result[$club]['competitions'][$nom] = true;

            // 🎨 FILTRE COULEUR
            if ($categorie === 'COULEUR') {

                if ($niveau === 'N1' && $support === 'PAPIER') {
                    $result[$club]['N1_P_COULEUR'] = $place;
                }

                if ($niveau === 'N1' && $support === 'IMAGE PROJETEE') {
                    $result[$club]['N1_IP_COULEUR'] = $place;
                }

                if ($niveau === 'N2' && $support === 'PAPIER') {
                    $result[$club]['N2_P_COULEUR'] = $place;
                }

                if ($niveau === 'N2' && $support === 'IMAGE PROJETEE') {
                    $result[$club]['N2_IP_COULEUR'] = $place;
                }

                if ($niveau === 'CdF' && $support === 'PAPIER') {
                    $result[$club]['CDF_P_COULEUR'] = $place;
                }

                if ($niveau === 'CdF' && $support === 'IMAGE PROJETEE') {
                    $result[$club]['CDF_IP_COULEUR'] = $place;
                }
            }
        }

        return $result;
    }
}
