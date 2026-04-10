<?php

namespace App\Services;

use Config\Database;

/**
 * ============================================================
 * 📊 ClassementService
 * ============================================================
 * - Calcul des notes photos
 * - Classement photos
 * - Classement auteurs
 * - Classement clubs
 *
 * Compatible MySQL 5.x (pas de window functions)
 */
class ClassementService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * ============================================================
     * 🚀 COMPUTE GLOBAL
     * ============================================================
     */
    public function compute(int $cid, bool $debug = false): void
    {
        if ($debug) log_message('info', "Compute START cid={$cid}");

        $this->computePhotoTotals($cid, $debug);
        $this->computePhotoRanking($cid, $debug);
        $this->computeAuteurRanking($cid, $debug);
        $this->computeClubRanking($cid, $debug);

        if ($debug) log_message('info', "Compute DONE cid={$cid}");
    }

    /**
     * ============================================================
     * 📸 TOTAL NOTES PHOTOS (SQL optimisé)
     * ============================================================
     */
    private function computePhotoTotals(int $cid, bool $debug): void
    {
        if ($debug) log_message('debug', "STEP totals");

        $this->db->query("
            UPDATE photos p
            JOIN (
                SELECT photos_id, SUM(note) as total
                FROM notes
                WHERE competitions_id = ?
                GROUP BY photos_id
            ) n ON n.photos_id = p.id
            SET p.note_totale = n.total
            WHERE p.competitions_id = ?
        ", [$cid, $cid]);
    }

    /**
     * ============================================================
     * 🏆 CLASSEMENT PHOTOS (PHP → fiable + ex-aequo)
     * ============================================================
     */
    private function computePhotoRanking(int $cid, bool $debug): void
    {
        if ($debug) log_message('debug', "STEP photos ranking");

        $photos = $this->db->query("
            SELECT id, note_totale
            FROM photos
            WHERE competitions_id = ?
            ORDER BY note_totale DESC
        ", [$cid])->getResult();

        $place = 0;
        $prev = null;
        $pos = 0;

        foreach ($photos as $p) {

            $pos++;

            if ($prev !== null && $p->note_totale == $prev) {
                // même place
            } else {
                $place = $pos;
            }

            $this->db->query(
                "UPDATE photos SET place = ? WHERE id = ?",
                [$place, $p->id]
            );

            $prev = $p->note_totale;
        }
    }

    /**
     * ============================================================
     * 👤 CLASSEMENT AUTEURS
     * ============================================================
     */
    private function computeAuteurRanking(int $cid, bool $debug): void
    {
        if ($debug) log_message('debug', "STEP auteurs");

        // reset
        $this->db->query("DELETE FROM classementauteurs WHERE competitions_id = ?", [$cid]);

        // agrégation
        $rows = $this->db->query("
            SELECT 
                participants_id,
                SUM(note_totale) as total,
                COUNT(*) as nb_photos
            FROM photos
            WHERE competitions_id = ?
            GROUP BY participants_id
            ORDER BY total DESC
        ", [$cid])->getResult();

        $place = 0;
        $prev = null;
        $pos = 0;

        foreach ($rows as $r) {

            $pos++;

            if ($prev !== null && $r->total == $prev) {
                // ex-aequo
            } else {
                $place = $pos;
            }

            $this->db->query("
                INSERT INTO classementauteurs 
                (competitions_id, participants_id, total, place, nb_photos)
                VALUES (?, ?, ?, ?, ?)
            ", [$cid, $r->participants_id, $r->total, $place, $r->nb_photos]);

            $prev = $r->total;
        }
    }

    /**
     * ============================================================
     * 🏢 CLASSEMENT CLUBS
     * ============================================================
     */
    private function computeClubRanking(int $cid, bool $debug): void
    {
        if ($debug) log_message('debug', "STEP clubs");

        // reset
        $this->db->query("DELETE FROM classementclubs WHERE competitions_id = ?", [$cid]);

        // agrégation
        $rows = $this->db->query("
            SELECT 
                u.clubs_id,
                SUM(p.note_totale) as total,
                COUNT(*) as nb_photos
            FROM photos p
            JOIN participants u ON p.participants_id = u.id
            WHERE p.competitions_id = ?
            AND u.clubs_id IS NOT NULL
            GROUP BY u.clubs_id
            ORDER BY total DESC
        ", [$cid])->getResult();

        $place = 0;
        $prev = null;
        $pos = 0;

        foreach ($rows as $r) {

            $pos++;

            if ($prev !== null && $r->total == $prev) {
                // ex-aequo
            } else {
                $place = $pos;
            }

            $this->db->query("
                INSERT INTO classementclubs 
                (competitions_id, clubs_id, total, place, nb_photos)
                VALUES (?, ?, ?, ?, ?)
            ", [$cid, $r->clubs_id, $r->total, $place, $r->nb_photos]);

            $prev = $r->total;
        }
    }
}
