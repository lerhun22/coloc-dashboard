<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * ============================================================
 * DataProviderClubs
 * ============================================================
 * Source fiable basée sur classementclubs
 *
 * - données officielles FPF
 * - pas de dépendance photos
 * - pas de problème de jointure
 *
 * ============================================================
 */
class DataProviderClubs
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAnnualData(int $annee): array
    {
        $sql = "
            SELECT
                cc.clubs_id AS club_id,
                cl.nom AS club_nom,
                cl.urs_id AS ur,

                cc.total AS points,
                cc.nb_photos,
                cc.place,

                c.id AS competition_id,
                c.nom AS competition_nom,
                c.saison,

                cm.level,
                cm.discipline,
                cm.support

            FROM classementclubs cc

            JOIN clubs cl ON cl.id = cc.clubs_id
            JOIN competitions c ON c.id = cc.competitions_id
            JOIN competition_meta cm ON cm.competition_id = c.id

            WHERE c.saison = ?
        ";

        $rows = $this->db->query($sql, [$annee])->getResultArray();

        // 🔧 normalisation simple
        foreach ($rows as &$r) {
            $r['club_id'] = (int)$r['club_id'];
            $r['points']  = (float)$r['points'];
            $r['ur']      = (int)$r['ur'];

            // compat avec ancien service
            $r['is_individual'] = false;
            $r['is_disqualified'] = false;
            $r['is_selected'] = true; // 🔥 déjà filtré en amont
        }

        return $rows;
    }
}
