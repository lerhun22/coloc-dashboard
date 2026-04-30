<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

helper('competition');
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
    public function getStats(int $competitionId, ?int $userUr = null): array
    {
        $userUr ??= currentUR();
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
    public function getStatsBulk(array $competitionIds, ?int $userUr = null): array
    {
        $userUr ??= currentUR();

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
            $participant = substr($ean, 0, 10); // UR+club+membre
            $club        = substr($ean, 0, 6);  // UR+club
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

    public function computeRanking(int $competitionId): array
    {
        $rows = $this->db->query("
        SELECT ean, note_totale
        FROM photos
        WHERE competitions_id = ?
        AND disqualifie = 0
    ", [$competitionId])->getResultArray();

        $clubs = [];

        foreach ($rows as $row) {

            $ean = $row['ean'] ?? null;
            if (!$ean) continue;

            // 🔥 extraction robuste
            $club = substr($ean, 2, 4);
            $ur   = (int) substr($ean, 0, 2);

            if (!isset($clubs[$club])) {
                $clubs[$club] = [
                    'club' => $club,
                    'ur' => $ur,
                    'points' => 0,
                    'photos' => 0,
                ];
            }

            $clubs[$club]['points'] += (float) ($row['note_totale'] ?? 0);
            $clubs[$club]['photos']++;
        }

        // 🔥 tri classement
        usort($clubs, fn($a, $b) => $b['points'] <=> $a['points']);

        // 🔥 ranking
        $rank = 1;
        foreach ($clubs as &$c) {
            $c['rank'] = $rank++;
        }

        return $clubs;
    }

    public function rebuildClassementClubs(int $competitionId): array
    {
        /*
    =========================================================
    📥 DATA
    =========================================================
    */
        $rows = $this->db->query("
        SELECT ean, note_totale
        FROM photos
        WHERE competitions_id = ?
        AND disqualifie = 0
    ", [$competitionId])->getResultArray();

        $clubs = [];

        /*
    =========================================================
    🧠 AGRÉGATION
    =========================================================
    */
        foreach ($rows as $row) {

            $ean = $row['ean'] ?? null;
            if (!$ean) continue;

            $clubNumero = substr($ean, 2, 4);
            $ur         = (int) substr($ean, 0, 2);

            if (!isset($clubs[$clubNumero])) {
                $clubs[$clubNumero] = [
                    'club_numero' => $clubNumero,
                    'ur'          => $ur,
                    'points'      => 0,
                    'nb_photos'   => 0,
                ];
            }

            $clubs[$clubNumero]['points'] += (float) ($row['note_totale'] ?? 0);
            $clubs[$clubNumero]['nb_photos']++;
        }

        /*
    =========================================================
    🔗 MAPPING CLUB ID
    =========================================================
    */
        foreach ($clubs as $numero => &$c) {

            $club = $this->db->table('clubs')
                ->where('numero', (int)$numero)
                ->get()
                ->getRowArray();

            $c['club_id'] = $club['id'] ?? null;
            $c['club_nom'] = $club['nom'] ?? 'UNKNOWN';
        }

        /*
    =========================================================
    🏆 RANKING
    =========================================================
    */
        $clubs = array_values(array_filter($clubs, fn($c) => $c['club_id'] !== null));

        usort($clubs, fn($a, $b) => $b['points'] <=> $a['points']);

        $rank = 1;
        foreach ($clubs as &$c) {
            $c['place'] = $rank++;
        }

        /*
    =========================================================
    💾 INSERT / UPDATE DB
    =========================================================
    */
        foreach ($clubs as $c) {

            $this->db->query("
            INSERT INTO classementclubs 
                (competitions_id, clubs_id, total, place, nb_photos)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                total = VALUES(total),
                place = VALUES(place),
                nb_photos = VALUES(nb_photos)
        ", [
                $competitionId,
                $c['club_id'],
                $c['points'],
                $c['place'],
                $c['nb_photos']
            ]);
        }

        return $clubs;
    }
}
