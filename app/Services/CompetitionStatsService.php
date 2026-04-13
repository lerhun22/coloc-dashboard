<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * =========================================================
 * 📊 CompetitionStatsService
 * =========================================================
 *
 * 📅 Date       : 2026-04
 * 👤 Auteur     : COLOC refactor
 * 📍 Localisation : app/Services/CompetitionStatsService.php
 *
 * ---------------------------------------------------------
 * 🎯 OBJECTIFS
 * ---------------------------------------------------------
 * - Centraliser TOUTES les stats de compétition
 * - Supprimer les doublons SQL (Bulk + Dashboard + Model)
 * - Fournir une source unique fiable
 *
 * ---------------------------------------------------------
 * ⚠️ RISQUES
 * ---------------------------------------------------------
 * - Requêtes lourdes si mal utilisées
 * - Doit être utilisé PARTOUT (sinon incohérences)
 *
 * =========================================================
 */

class CompetitionStatsService
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * =========================================================
     * 📊 GET STATS - SINGLE COMPETITION
     * =========================================================
     *
     * @param int $competitionId
     * @return array
     *
     * Retour :
     * [
     *   photo_count => int,
     *   author_count => int,
     *   club_count => int
     * ]
     */
    public function getStats(int $competitionId): array
    {
        $result = $this->getStatsBulk([$competitionId]);

        return $result[$competitionId] ?? [
            'photo_count'  => 0,
            'author_count' => 0,
            'club_count'   => 0,
        ];
    }

    /**
     * =========================================================
     * 📊 GET STATS - BULK (OPTIMISÉ)
     * =========================================================
     *
     * @param array $competitionIds
     * @return array
     *
     * Format :
     * [
     *   competition_id => [
     *       photo_count,
     *       author_count,
     *       club_count
     *   ]
     * ]
     */
    public function getStatsBulk(array $competitionIds): array
    {
        if (empty($competitionIds)) {
            return [];
        }

        // sécurisation IDs
        $competitionIds = array_map('intval', $competitionIds);
        $ids = implode(',', $competitionIds);

        // init résultat
        $stats = [];
        foreach ($competitionIds as $id) {
            $stats[$id] = [
                'photo_count'  => 0,
                'author_count' => 0,
                'club_count'   => 0,
            ];
        }

        /*
        =========================================================
        📸 1. PHOTOS
        =========================================================
        */
        $photos = $this->db->query("
            SELECT competitions_id, COUNT(*) as photo_count
            FROM photos
            WHERE competitions_id IN ($ids)
            GROUP BY competitions_id
        ")->getResultArray();

        foreach ($photos as $row) {
            $stats[$row['competitions_id']]['photo_count'] = (int)$row['photo_count'];
        }

        /*
        =========================================================
        👤 2. AUTEURS (participants)
        =========================================================
        */
        $authors = $this->db->query("
            SELECT competitions_id, COUNT(DISTINCT participants_id) as author_count
            FROM photos
            WHERE competitions_id IN ($ids)
            GROUP BY competitions_id
        ")->getResultArray();

        foreach ($authors as $row) {
            $stats[$row['competitions_id']]['author_count'] = (int)$row['author_count'];
        }

        /*
        =========================================================
        🏢 3. CLUBS
        =========================================================
        */
        $clubs = $this->db->query("
            SELECT p.competitions_id, COUNT(DISTINCT pa.clubs_id) as club_count
            FROM photos p
            JOIN participants pa ON pa.id = p.participants_id
            WHERE p.competitions_id IN ($ids)
            GROUP BY p.competitions_id
        ")->getResultArray();

        foreach ($clubs as $row) {
            $stats[$row['competitions_id']]['club_count'] = (int)$row['club_count'];
        }

        return $stats;
    }
}
