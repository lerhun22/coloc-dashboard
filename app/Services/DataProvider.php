<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * =========================================================
 * DataProvider
 * =========================================================
 * Auteur : COLOC V3
 * Date : 2026-04
 *
 * OBJECTIF :
 * Source unique de données pour toute l'analyse annuelle
 *
 * ⚠️ RÈGLES :
 * - 1 seule requête SQL
 * - aucune logique métier (uniquement normalisation)
 * - compatible UR + National (EAN)
 *
 * =========================================================
 */
class DataProvider
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * =========================================================
     * getAnnualData
     * =========================================================
     * Récupère toutes les données photos enrichies
     *
     * @param int $annee
     * @return array
     * =========================================================
     */
    public function getAnnualData(int $annee): array
    {
        /*
        =========================================================
        1. REQUÊTE SQL UNIQUE
        =========================================================
        */
        $sql = "
            SELECT
                p.id AS photo_id,
                p.ean,
                p.titre,
                p.note_totale,
                p.place,
                p.retenue,
                p.disqualifie,

                c.id AS competition_id,
                c.nom AS competition_nom,
                c.type AS competition_type,
                c.saison,
                c.urs_id AS competition_ur,

                pa.id AS participant_id,
                pa.nom AS participant_nom,
                pa.prenom AS participant_prenom,
                pa.urs_id AS participant_ur,

                cl.id AS club_id,
                cl.nom AS club_nom,
                cl.urs_id AS club_ur

            FROM photos p

            LEFT JOIN competitions c 
                ON c.id = p.competitions_id

            LEFT JOIN participants pa 
                ON pa.id = p.participants_id

            LEFT JOIN clubs cl 
                ON cl.id = pa.clubs_id

            WHERE c.saison = ?
        ";

        $rows = $this->db->query($sql, [$annee])->getResultArray();

        /*
        =========================================================
        2. NORMALISATION
        =========================================================
        */
        foreach ($rows as &$row) {

            /*
            =====================================================
            INITIALISATION SAFE
            =====================================================
            */
            $row['source'] = null;
            $row['auteur_id'] = null;
            $row['auteur_nom'] = null;
            $row['ur'] = null;

            /*
            =====================================================
            CAS 1 : PARTICIPANT (UR fiable)
            =====================================================
            */
            if (!empty($row['participant_id'])) {

                $row['auteur_id'] = $row['participant_id'];

                $row['auteur_nom'] = trim(
                    ($row['participant_prenom'] ?? '') . ' ' .
                        ($row['participant_nom'] ?? '')
                );

                $row['ur'] = $row['participant_ur'] ?? $row['club_ur'];

                $row['source'] = 'participant';
            }

            /*
            =====================================================
            CAS 2 : NATIONAL (EAN)
            =====================================================
            */ else {

                $ean = $row['ean'];

                if ($ean && preg_match('/^\d{12}$/', $ean)) {

                    /*
                    EAN STRUCTURE
                    [0-1]   UR
                    [2-5]   CLUB
                    [6-9]   MEMBRE
                    [10-11] ignore
                    */

                    $ur     = substr($ean, 0, 2);
                    $club   = substr($ean, 2, 4);
                    $member = substr($ean, 6, 4);

                    $row['auteur_id'] = $ur . '_' . $club . '_' . $member;
                    $row['auteur_nom'] = 'Auteur ' . $member;

                    $row['ur'] = (int)$ur;

                    // ⚠️ mapping club approximatif (national)
                    $row['club_id'] = $club;
                    $row['club_nom'] = 'Club #' . $club;

                    $row['member_code'] = $member;

                    $row['source'] = 'ean';
                }
            }

            /*
            =====================================================
            POINTS NORMALISÉS
            =====================================================
            */
            $row['points'] = (int)($row['note_totale'] ?? 0);

            /*
            =====================================================
            FLAGS UTILES (optimisation future)
            =====================================================
            */
            $row['is_ur22'] = ((int)$row['ur'] === 22);

            $row['is_selected'] = ((int)$row['retenue'] === 1);
            $row['is_disqualified'] = ((int)$row['disqualifie'] === 1);
        }

        return $rows;
    }
}
