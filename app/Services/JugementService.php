<?php

namespace App\Services;

use Config\Database;
use App\Libraries\CompetitionStorage;
use App\Libraries\ThumbnailService;

class JugementService
{
    protected $db;
    protected CompetitionStorage $storage;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->storage = new CompetitionStorage();
    }

    public function computeJudgeStats(int $annee, array $filters = []): array
    {
        /*
        =========================================================
        🎯 FILTRES SQL
        =========================================================
        */
        $where = ["c.saison = ?"];
        $params = [$annee];

        if (!empty($filters['type'])) {
            if ($filters['type'] === 'N') {
                $where[] = "c.urs_id IS NULL";
            }
            if ($filters['type'] === 'UR') {
                $where[] = "c.urs_id IS NOT NULL";
            }
        }

        if (!empty($filters['categorie'])) {
            $where[] = "c.nom LIKE ?";
            $params[] = '%' . $filters['categorie'] . '%';
        }

        if (!empty($filters['ur'])) {
            $where[] = "c.urs_id = ?";
            $params[] = $filters['ur'];
        }

        if (!empty($filters['competition_id'])) {
            $where[] = "c.id = ?";
            $params[] = $filters['competition_id'];
        }

        $whereSQL = implode(' AND ', $where);

        /*
        =========================================================
        SQL
        =========================================================
        */
        $sql = "
        SELECT
            p.id AS photo_id,
            p.titre,
            p.ean,

            c.id AS competition_id,
            c.nom AS competition_nom,
            c.numero,
            c.saison,
            c.urs_id AS competition_ur,

            AVG(n.note) as moyenne,
            MIN(n.note) as min_note,
            MAX(n.note) as max_note,
            (MAX(n.note) - MIN(n.note)) as ecart,

            GROUP_CONCAT(n.note ORDER BY n.note DESC) as notes

        FROM notes n
        JOIN photos p ON p.id = n.photos_id
        JOIN competitions c ON c.id = p.competitions_id

        WHERE $whereSQL
        GROUP BY p.id
        ";

        $rows = $this->db->query($sql, $params)->getResultArray();

        /*
        =========================================================
        🔒 INIT DATA
        =========================================================
        */
        foreach ($rows as &$row) {

            // notes
            $row['notes_array'] = !empty($row['notes'])
                ? array_map('intval', explode(',', $row['notes']))
                : [];

            // outlier
            $row['juge_outlier'] = null;

            if (count($row['notes_array']) === 3) {

                $moy = (float)$row['moyenne'];

                $ecarts = array_map(fn($n) => abs($n - $moy), $row['notes_array']);
                $maxIndex = array_keys($ecarts, max($ecarts))[0];

                $row['juge_outlier'] = $maxIndex + 1;
            }

            /*
            =========================================================
            📸 PHOTO URL (fallback toujours dispo)
            =========================================================
            */
            $competition = [
                'id' => $row['competition_id'],
                'numero' => $row['numero'],
                'saison' => $row['saison'],
                'urs_id' => $row['competition_ur'],
            ];

            $photosPath = $this->storage->getPhotosPath($competition);

            if (!empty($photosPath)) {

                $photoFullPath = $photosPath . $row['ean'] . '.jpg';

                if (file_exists($photoFullPath)) {

                    $relative = str_replace(FCPATH, '', $photoFullPath);
                    $row['photo_url'] = base_url($relative);
                } else {
                    $row['photo_url'] = base_url('assets/img/no-image.jpg');
                }
            } else {
                $row['photo_url'] = base_url('assets/img/no-image.jpg');
            }

            // thumb par défaut = fallback
            $row['thumb_url'] = $row['photo_url'];
        }
        unset($row);

        /*
        =========================================================
        🔥 CLIVANTES
        =========================================================
        */
        $clivantes = $rows;
        usort($clivantes, fn($a, $b) => $b['ecart'] <=> $a['ecart']);
        $clivantes = array_slice($clivantes, 0, 10);

        /*
        =========================================================
        🤝 CONSENSUELLES
        =========================================================
        */
        $consensuelles = array_filter($rows, fn($r) => $r['moyenne'] >= 12);

        usort(
            $consensuelles,
            fn($a, $b) => ($a['ecart'] <=> $b['ecart']) ?: ($b['moyenne'] <=> $a['moyenne'])
        );

        $consensuelles = array_slice($consensuelles, 0, 10);

        /*
        =========================================================
        ⚖️ JUGE DÉCISIF
        =========================================================
        */
        $jugeDecisif = array_filter(
            $rows,
            fn($r) =>
            $r['moyenne'] >= 12 &&
                $r['max_note'] >= 16 &&
                $r['min_note'] <= 8 &&
                $r['ecart'] >= 8
        );

        usort(
            $jugeDecisif,
            fn($a, $b) => ($b['ecart'] <=> $a['ecart']) ?: ($b['moyenne'] <=> $a['moyenne'])
        );

        $jugeDecisif = array_slice($jugeDecisif, 0, 10);

        /*
        =========================================================
        🖼️ THUMBS (UNIQUEMENT POUR LES LISTES UTILES)
        =========================================================
        */
        $thumbService = new ThumbnailService();
        $processed = [];

        $process = function (&$list) use ($thumbService, &$processed) {

            foreach ($list as &$row) {

                if (isset($processed[$row['ean']])) continue;

                $competition = [
                    'id' => $row['competition_id'],
                    'numero' => $row['numero'],
                    'saison' => $row['saison'],
                    'urs_id' => $row['competition_ur'],
                ];

                $photoPath = $this->storage->getPhotosPath($competition);

                if (empty($photoPath)) continue;

                $photoFullPath = $photoPath . $row['ean'] . '.jpg';

                if (file_exists($photoFullPath)) {

                    $row['thumb_url'] = $thumbService->getThumbUrlFromPath(
                        $photoFullPath,
                        $competition,
                        $row['ean']
                    );

                    $processed[$row['ean']] = true;
                }
            }
        };

        $process($clivantes);
        $process($consensuelles);
        $process($jugeDecisif);

        /*
        =========================================================
        🧠 BIAIS JUGES
        =========================================================
        */
        $jugesStats = [];

        foreach ($rows as $r) {
            foreach ($r['notes_array'] as $i => $note) {
                $jugesStats[$i]['notes'][] = $note;
            }
        }

        foreach ($jugesStats as $i => &$j) {

            $count = count($j['notes']);

            if ($count === 0) continue;

            $avg = array_sum($j['notes']) / $count;

            $variance = array_sum(array_map(
                fn($n) => pow($n - $avg, 2),
                $j['notes']
            )) / $count;

            $j = [
                'juge_nom'   => 'Juge ' . ($i + 1),
                'moyenne'    => round($avg, 2),
                'ecart_type' => round(sqrt($variance), 2),
            ];
        }
        unset($j);

        /*
        =========================================================
        RETURN
        =========================================================
        */
        return [
            'all' => $rows,
            'top_clivantes'     => $clivantes,
            'top_consensuelles' => $consensuelles,
            'top_juge_decisif'  => $jugeDecisif,
            'juges'             => $jugesStats,
        ];
    }
}
