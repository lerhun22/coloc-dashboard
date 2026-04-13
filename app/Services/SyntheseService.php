<?php

namespace App\Services;

/**
 * =========================================================
 * SyntheseService
 * =========================================================
 * Service d'agrégation des données (stats globales, matrices)
 *
 * ⚠️ Version initiale (évolutive)
 * =========================================================
 */
class SyntheseService
{
    /**
     * =========================================================
     * computeGlobalStats
     * =========================================================
     * @param array $rows
     * @return array
     * =========================================================
     */
    public function computeGlobalStats(array $rows): array
    {
        $clubs = [];
        $auteurs = [];
        $totalPoints = 0;
        $nbImages = count($rows);

        foreach ($rows as $row) {

            if (!empty($row['club_id'])) {
                $clubs[$row['club_id']] = true;
            }

            if (!empty($row['auteur_id'])) {
                $auteurs[$row['auteur_id']] = true;
            }

            $totalPoints += $row['points'] ?? 0;
        }

        return [
            'nb_clubs' => count($clubs),
            'nb_auteurs' => count($auteurs),
            'nb_images' => $nbImages,
            'moyenne' => $nbImages > 0 ? round($totalPoints / $nbImages, 2) : 0,
        ];
    }

    /**
     * =========================================================
     * buildMatrice
     * =========================================================
     * ⚠️ placeholder (à améliorer)
     * =========================================================
     */
    public function buildMatrice(array $rows): array
    {
        return [
            'N1' => ['mono' => 0, 'couleur' => 0, 'nature' => 0],
            'N2' => ['mono' => 0, 'couleur' => 0, 'nature' => 0],
            'UR' => ['mono' => 0, 'couleur' => 0, 'nature' => 0],
        ];
    }

    /**
     * =========================================================
     * computeFromClassement
     * =========================================================
     * ⚠️ placeholder
     * =========================================================
     */
    public function computeFromClassement(array $classement): array
    {
        return $classement;
    }


    public function computeCompetitionStats(array $rows): array
    {
        $competitions = [];

        foreach ($rows as $row) {

            $cid = $row['competition_id'];

            if (!$cid) {
                continue;
            }

            if (!isset($competitions[$cid])) {
                $competitions[$cid] = [
                    'competition_id' => $cid,
                    'nom' => $row['competition_nom'],
                    'type' => $this->mapCompetitionType($row),

                    'images' => 0,
                    'auteurs_ur22' => [],
                    'auteurs' => [],
                    'clubs' => [],
                    'clubs_ur22' => [], // 🔥 nouveau
                ];
            }

            /*
        =========================
        AGRÉGATION
        =========================
        */
            $competitions[$cid]['images']++;

            if (!empty($row['auteur_id'])) {
                $competitions[$cid]['auteurs'][$row['auteur_id']] = true;
            }

            if (!empty($row['club_id'])) {
                $competitions[$cid]['clubs'][$row['club_id']] = true;

                // 🔥 filtrage UR22
                if (!empty($row['is_ur22'])) {
                    $competitions[$cid]['clubs_ur22'][$row['club_id']] = true;
                }
            }

            if (!empty($row['auteur_id'])) {

                $competitions[$cid]['auteurs'][$row['auteur_id']] = true;

                // 🔥 auteurs UR22
                if (!empty($row['is_ur22'])) {
                    $competitions[$cid]['auteurs_ur22'][$row['auteur_id']] = true;
                }
            }
        }

        /*
    =========================
    NORMALISATION
    =========================
    */
        foreach ($competitions as &$c) {

            $c['nb_auteurs'] = count($c['auteurs']);
            $c['nb_clubs'] = count($c['clubs']);

            // 🔥 NOUVEAU INDICATEUR
            $c['nb_clubs_ur22'] = count($c['clubs_ur22']);
            $c['nb_auteurs_ur22'] = count($c['auteurs_ur22']);
            unset($c['auteurs'], $c['clubs'], $c['clubs_ur22'], $c['auteurs_ur22']);
        }

        /*
    =========================
    TRI
    =========================
    */
        usort($competitions, function ($a, $b) {
            return strcmp($a['type'], $b['type']);
        });

        return $competitions;
    }

    private function mapCompetitionType(array $row): string
    {
        $nom = strtolower($row['competition_nom']);
        $ur = $row['competition_ur']; // urs_id

        /*
    =========================================================
    UR
    =========================================================
    */
        if (!empty($ur)) {
            return 'UR';
        }

        /*
    =========================================================
    NATIONAL
    =========================================================
    */
        if (str_contains($nom, 'coupe') || str_contains($nom, 'france')) {
            return 'CdF';
        }

        if (preg_match('/\bn1\b/', $nom)) {
            return 'N1';
        }

        if (preg_match('/\bn2\b/', $nom)) {
            return 'N2';
        }

        return 'National';
    }
}
