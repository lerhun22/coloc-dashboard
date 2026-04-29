<?php

namespace App\Libraries;

use Config\Database;

class CompetitionStatsService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getStats(int $competitionId): array
    {
        $isJudged = $this->isJudged($competitionId);

        // =========================
        // 📸 PHOTOS
        // =========================
        $photo_count = $this->db->table('photos')
            ->where('competitions_id', $competitionId)
            ->countAllResults();

        // =========================
        // 👤 AUTEURS
        // =========================
        if ($isJudged) {
            $author_count = $this->db->table('classementauteurs')
                ->where('competitions_id', $competitionId)
                ->countAllResults();
        } else {
            $author_count = $this->db->table('photos')
                ->select('COUNT(DISTINCT participants_id) as total')
                ->where('competitions_id', $competitionId)
                ->get()
                ->getRow()
                ->total ?? 0;
        }

        // =========================
        // 🏢 CLUBS
        // =========================
        if ($isJudged) {
            $club_count = $this->db->table('classementclubs')
                ->where('competitions_id', $competitionId)
                ->countAllResults();
        } else {
            $club_count = $this->db->table('photos p')
                ->select('COUNT(DISTINCT pa.clubs_id) as total')
                ->join('participants pa', 'pa.id = p.participants_id')
                ->where('p.competitions_id', $competitionId)
                ->get()
                ->getRow()
                ->total ?? 0;
        }

        // =========================
        // 📊 MOYENNES
        // =========================
        $avg_photos_per_author = $author_count > 0
            ? round($photo_count / $author_count, 2)
            : 0;

        $avg_photos_per_club = $club_count > 0
            ? round($photo_count / $club_count, 2)
            : 0;

        return [
            'photo_count' => $photo_count,
            'author_count' => $author_count,
            'club_count' => $club_count,
            'avg_photos_per_author' => $avg_photos_per_author,
            'avg_photos_per_club' => $avg_photos_per_club,
            'is_judged' => $isJudged,
        ];
    }

    protected function isJudged(int $competitionId): bool
    {
        return $this->db->table('classementauteurs')
            ->where('competitions_id', $competitionId)
            ->countAllResults() > 0;
    }

    private function getCompetitionWinners(): array
    {
        $db = \Config\Database::connect();

        $rows = $db->query("
SELECT
c.id competition_id,
a.nom auteur,
cl.id club_id
FROM classements clt
JOIN auteurs a ON a.id=clt.auteur_id
JOIN clubs cl ON cl.id=a.club_id
JOIN competitions c ON c.id=clt.competition_id
WHERE clt.place=1
")->getResultArray();

        $out = [];

        foreach ($rows as $r) {
            $out[$r['competition_id']] = [
                'winner_name' => $r['auteur'],
                'club_id_winner' => $r['club_id']
            ];
        }

        return $out;
    }
}
