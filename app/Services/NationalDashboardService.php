<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * =========================================================
 * NationalDashboardService
 * Classement national officiel FPF
 * Source : classementclubs + competition_meta
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

                cl.nom      AS club_nom,
                cl.urs_id   AS ur,

                cm.level

            FROM classementclubs cc

            INNER JOIN clubs cl
                ON cl.id = cc.clubs_id

            INNER JOIN competitions c
                ON c.id = cc.competitions_id

            INNER JOIN competition_meta cm
                ON cm.competition_id = c.id

            WHERE c.saison = ?
              AND cm.level IN ('N2','N1','CDF')

            /* ordre stable inter-machines */
            ORDER BY
                cc.clubs_id,
                cm.level,
                c.id
        ", [$annee])->getResultArray();

        return $this->aggregate($rows);
    }


    /**
     * =========================================================
     * Agrégation multi niveaux par club
     * =========================================================
     */
    private function aggregate(array $rows): array
    {
        $clubs = [];

        foreach ($rows as $r) {

            $clubId = (int)$r['clubs_id'];
            $level  = strtoupper(trim($r['level']));
            $points = (float)$r['total'];

            if (!in_array($level, ['N2', 'N1', 'CDF'])) {
                continue;
            }

            if (!isset($clubs[$clubId])) {
                $clubs[$clubId] = [
                    'club_id'  => $clubId,
                    'club_nom' => $r['club_nom'],
                    'ur'       => $r['ur'],

                    'N2'   => 0,
                    'N1'   => 0,
                    'CDF'  => 0,
                    'total' => 0,
                ];
            }

            // cumul (pas écrasement)
            $clubs[$clubId][$level] += $points;
            $clubs[$clubId]['total'] += $points;
        }

        $clubs = array_values($clubs);

        /*
        =========================================================
        Tri stable :
        1. total desc
        2. nom club asc (tie-break)
        =========================================================
        */
        usort($clubs, function ($a, $b) {

            if ($b['total'] != $a['total']) {
                return $b['total'] <=> $a['total'];
            }

            return strcmp($a['club_nom'], $b['club_nom']);
        });

        /*
        =========================================================
        Rangs
        =========================================================
        */
        $rank = 1;

        foreach ($clubs as &$club) {
            $club['rank'] = $rank++;
        }

        return $clubs;
    }
}