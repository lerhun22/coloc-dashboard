<?php

namespace App\Libraries;

use Config\Database;

class CompetitionStatsBulkService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }
    public function computeFromEAN(array $photos, int $userUr = 22): array
    {
        $clubs = [];
        $participants = [];
        $clubsUR = [];

        foreach ($photos as $p) {

            $ean = $p['ean'] ?? null;

            if (!$ean || strlen($ean) < 10) {
                continue;
            }

            $ur     = substr($ean, 0, 2);
            $club   = substr($ean, 2, 4);
            $member = substr($ean, 6, 4);

            $clubKey = $ur . $club;
            $memberKey = $clubKey . $member;

            // 🔹 clubs total
            $clubs[$clubKey] = true;

            // 🔹 participants uniques
            $participants[$memberKey] = true;

            // 🔹 clubs UR utilisateur
            if ((int)$ur === $userUr) {
                $clubsUR[$clubKey] = true;
            }
        }

        return [
            'clubs_nat'        => count($clubs),
            'participants'     => count($participants),
            'clubs_ur'         => count($clubsUR),
        ];
    }

    public function getStatsForCompetitions(array $competitionIds): array
    {
        if (empty($competitionIds)) return [];

        // =========================
        // 📸 PHOTOS
        // =========================
        $photos = $this->db->query("
            SELECT competitions_id, COUNT(*) as photo_count
            FROM photos
            WHERE competitions_id IN (" . implode(',', $competitionIds) . ")
            GROUP BY competitions_id
        ")->getResultArray();

        // =========================
        // 👤 AUTEURS (AVANT JUGEMENT)
        // =========================
        $authors = $this->db->query("
            SELECT competitions_id, COUNT(DISTINCT participants_id) as author_count
            FROM photos
            WHERE competitions_id IN (" . implode(',', $competitionIds) . ")
            GROUP BY competitions_id
        ")->getResultArray();

        // =========================
        // 🏢 CLUBS (AVANT JUGEMENT)
        // =========================
        $clubs = $this->db->query("
            SELECT p.competitions_id, COUNT(DISTINCT pa.clubs_id) as club_count
            FROM photos p
            JOIN participants pa ON pa.id = p.participants_id
            WHERE p.competitions_id IN (" . implode(',', $competitionIds) . ")
            GROUP BY p.competitions_id
        ")->getResultArray();

        // =========================
        // 🟢 AUTEURS (APRES JUGEMENT)
        // =========================
        $authorsRanked = $this->db->query("
            SELECT competitions_id, COUNT(*) as author_count
            FROM classementauteurs
            WHERE competitions_id IN (" . implode(',', $competitionIds) . ")
            GROUP BY competitions_id
        ")->getResultArray();

        // =========================
        // 🟢 CLUBS (APRES JUGEMENT)
        // =========================
        $clubsRanked = $this->db->query("
            SELECT competitions_id, COUNT(*) as club_count
            FROM classementclubs
            WHERE competitions_id IN (" . implode(',', $competitionIds) . ")
            GROUP BY competitions_id
        ")->getResultArray();

        // =========================
        // 🧠 INDEXATION
        // =========================
        $stats = [];

        foreach ($competitionIds as $id) {
            $stats[$id] = [
                'photo_count' => 0,
                'author_count' => 0,
                'club_count' => 0,
                'is_judged' => false,
            ];
        }

        foreach ($photos as $row) {
            $stats[$row['competitions_id']]['photo_count'] = (int)$row['photo_count'];
        }

        foreach ($authors as $row) {
            $stats[$row['competitions_id']]['author_count'] = (int)$row['author_count'];
        }

        foreach ($clubs as $row) {
            $stats[$row['competitions_id']]['club_count'] = (int)$row['club_count'];
        }

        // =========================
        // 🔥 OVERRIDE SI JUGE
        // =========================
        foreach ($authorsRanked as $row) {
            $id = $row['competitions_id'];
            $stats[$id]['author_count'] = (int)$row['author_count'];
            $stats[$id]['is_judged'] = true;
        }

        foreach ($clubsRanked as $row) {
            $id = $row['competitions_id'];
            $stats[$id]['club_count'] = (int)$row['club_count'];
            $stats[$id]['is_judged'] = true;
        }

        // =========================
        // 📊 MOYENNES
        // =========================
        foreach ($stats as $id => &$s) {
            $s['avg_photos_per_author'] = $s['author_count'] > 0
                ? round($s['photo_count'] / $s['author_count'], 2)
                : 0;

            $s['avg_photos_per_club'] = $s['club_count'] > 0
                ? round($s['photo_count'] / $s['club_count'], 2)
                : 0;
        }

        return $stats;
    }
}
