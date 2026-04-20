<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * =========================================================
 * NationalDashboardService
 * =========================================================
 * Source officielle NATIONAL = classementclubs
 * =========================================================
 */
class NationalDashboardService
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * =========================================================
     * Classement national complet
     * =========================================================
     */
    public function getRanking(int $annee): array
    {
        $rows = $this->db->query("
        SELECT
            cc.competitions_id,
            cc.clubs_id,
            cc.total,
            cc.place,
            cc.nb_photos,

            cl.nom AS club_nom,
            cl.urs_id AS ur,

            cm.level

        FROM classementclubs cc

        JOIN clubs cl ON cl.id = cc.clubs_id
        JOIN competitions c ON c.id = cc.competitions_id
        JOIN competition_meta cm ON cm.competition_id = c.id

        WHERE c.saison = ?
        AND cm.level IN ('N2', 'N1', 'CDF')
        AND c.nom NOT LIKE '%REGIONAL%' -- 🔥 sécurité
    ", [$annee])->getResultArray();

        return $this->aggregate($rows);
    }

    /**
     * =========================================================
     * Agrégation clubs multi-niveaux
     * =========================================================
     */
    private function aggregate(array $rows): array
    {
        $clubs = [];

        foreach ($rows as $r) {

            $cid = $r['clubs_id'];

            if (!isset($clubs[$cid])) {
                $clubs[$cid] = [
                    'club_id'  => $cid,
                    'club_nom' => $r['club_nom'],
                    'ur'       => $r['ur'],

                    'N2'  => 0,
                    'N1'  => 0,
                    'CDF' => 0,
                    'total' => 0,
                ];
            }

            // 🔥 NORMALISATION CRITIQUE
            $level = strtoupper(trim($r['level']));

            // 🔥 FILTRE ABSOLU
            if (!in_array($level, ['N2', 'N1', 'CDF'])) {
                continue;
            }

            $clubs[$cid][$level] += (float)$r['total'];
            $clubs[$cid]['total'] += (float)$r['total'];
        }

        $clubs = array_values($clubs);

        usort($clubs, fn($a, $b) => $b['total'] <=> $a['total']);

        $rank = 1;
        foreach ($clubs as &$c) {
            $c['rank'] = $rank++;
        }

        return $clubs;
    }
}
