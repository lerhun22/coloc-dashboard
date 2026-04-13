<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * =========================================================
 * 🇫🇷 NationalStatsService
 * =========================================================
 *
 * 📅 Date       : 2026-04
 * 👤 Auteur     : COLOC refactor
 * 📍 Localisation : app/Services/NationalStatsService.php
 *
 * ---------------------------------------------------------
 * 🎯 OBJECTIFS
 * ---------------------------------------------------------
 * - Calculer les stats spécifiques aux compétitions nationales
 * - Basé sur EAN (participants / clubs / UR)
 * - Remplacer computeFromEAN + logique Controller
 *
 * ---------------------------------------------------------
 * ⚠️ RISQUES
 * ---------------------------------------------------------
 * - dépend du format EAN
 * - logique métier sensible → centraliser ici uniquement
 *
 * =========================================================
 */

class NationalStatsService
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * =========================================================
     * 📊 STATS NATIONALES (1 COMPÉTITION)
     * =========================================================
     *
     * @param int $competitionId
     * @param int $userUr
     * @return array
     */
    public function getStats(int $competitionId, int $userUr = 22): array
    {
        $result = $this->getStatsBulk([$competitionId], $userUr);

        return $result[$competitionId] ?? [
            'participants' => 0,
            'clubs_nat'    => 0,
            'clubs_ur'     => 0,
        ];
    }

    /**
     * =========================================================
     * 📊 STATS BULK (PLUSIEURS COMPÉTITIONS)
     * =========================================================
     *
     * @param array $competitionIds
     * @param int $userUr
     * @return array
     */
    public function getStatsBulk(array $competitionIds, int $userUr = 22): array
    {
        if (empty($competitionIds)) {
            return [];
        }

        $competitionIds = array_map('intval', $competitionIds);
        $ids = implode(',', $competitionIds);

        $results = [];

        foreach ($competitionIds as $id) {
            $results[$id] = [
                'participants' => 0,
                'clubs_nat'    => 0,
                'clubs_ur'     => 0,
            ];
        }

        /*
        =========================================================
        📥 RÉCUP EAN
        =========================================================
        */
        $rows = $this->db->query("
            SELECT competitions_id, ean
            FROM photos
            WHERE competitions_id IN ($ids)
        ")->getResultArray();

        /*
        =========================================================
        🧠 TRAITEMENT MÉTIER
        =========================================================
        */
        $buffer = [];

        foreach ($rows as $row) {

            $cid = (int)$row['competitions_id'];
            $ean = $row['ean'] ?? null;

            if (!$ean) continue;

            // init
            if (!isset($buffer[$cid])) {
                $buffer[$cid] = [
                    'participants' => [],
                    'clubs_nat'    => [],
                    'clubs_ur'     => [],
                ];
            }

            /*
            =====================================================
            🎯 EXTRACTION EAN
            =====================================================
            ⚠️ Adapter si ton format change
            */
            $participant = substr($ean, 0, 6);
            $club        = substr($ean, 0, 4);
            $ur          = (int) substr($ean, 0, 2);

            $buffer[$cid]['participants'][$participant] = true;
            $buffer[$cid]['clubs_nat'][$club]           = true;

            if ($ur === $userUr) {
                $buffer[$cid]['clubs_ur'][$club] = true;
            }
        }

        /*
        =========================================================
        📊 FINALISATION
        =========================================================
        */
        foreach ($buffer as $cid => $data) {
            $results[$cid] = [
                'participants' => count($data['participants']),
                'clubs_nat'    => count($data['clubs_nat']),
                'clubs_ur'     => count($data['clubs_ur']),
            ];
        }

        return $results;
    }
}
