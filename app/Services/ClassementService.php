<?php

namespace App\Services;

use App\Models\PhotoModel;
use App\Models\CompetitionModel;

/**
 * =========================================================
 * ClassementService
 * =========================================================
 * Auteur : COLOC V3
 * Date : 2026-04
 * Objectif :
 * Centraliser toute la logique de classement (clubs / auteurs)
 *
 * ⚠️ SOURCE UNIQUE DE VÉRITÉ
 * - aucun calcul de points ailleurs
 * - aucun classement ailleurs
 *
 * =========================================================
 */
class ClassementService
{
    protected PhotoModel $photoModel;
    protected CompetitionModel $competitionModel;

    public function __construct()
    {
        $this->photoModel = new PhotoModel();
        $this->competitionModel = new CompetitionModel();
    }

    /**
     * =========================================================
     * computeClubRanking
     * =========================================================
     * Calcule le classement des clubs pour une année donnée
     *
     * @param int $annee
     * @param array $options
     *      - ur_only (bool)
     *      - competition_type (national|regional|null)
     *
     * @return array
     * =========================================================
     */
    public function computeClubRanking(int $annee): array
    {
        /*
    =========================================================
    SOURCE UNIQUE
    =========================================================
    */
        $dataProvider = new \App\Services\DataProvider();
        $rows = $dataProvider->getAnnualData($annee);

        $clubs = [];

        foreach ($rows as $row) {

            $clubId = $row['club_id'];

            if (!isset($clubs[$clubId])) {
                $clubs[$clubId] = [
                    'club_id' => $clubId,
                    'club_nom' => $row['club_nom'],
                    'points' => 0,
                    'images' => 0,
                ];
            }

            $clubs[$clubId]['points'] += $row['points'];
            $clubs[$clubId]['images']++;
        }

        return $clubs;
    }

    public function computeClubRankingFromRows(array $rows, array $options = []): array
    {
        $urOnly = $options['ur_only'] ?? false;

        $clubs = [];

        foreach ($rows as $row) {

            if ($urOnly && !$row['is_ur22']) {
                continue;
            }

            $clubId = $row['club_id'];

            if (!isset($clubs[$clubId])) {
                $clubs[$clubId] = [
                    'club_id' => $clubId,
                    'club_nom' => $row['club_nom'],
                    'points' => 0,
                    'images' => 0,
                    'auteurs' => [],
                ];
            }

            $clubs[$clubId]['points'] += $row['points'];
            $clubs[$clubId]['images']++;

            $clubs[$clubId]['auteurs'][$row['auteur_id']] = true;
        }

        /*
    =========================
    NORMALISATION
    =========================
    */
        foreach ($clubs as &$club) {
            $club['nb_auteurs'] = count($club['auteurs']);
            unset($club['auteurs']);
        }

        /*
    =========================
    TRI
    =========================
    */
        usort($clubs, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        /*
    =========================
    RANG
    =========================
    */
        $rank = 1;
        foreach ($clubs as &$club) {
            $club['rang'] = $rank++;
        }

        return $clubs;
    }

    public function computeAuthorRankingFromRows(array $rows, array $options = []): array
    {
        $urOnly = $options['ur_only'] ?? false;

        $auteurs = [];

        foreach ($rows as $row) {

            /*
        =========================================================
        FILTRE UR
        =========================================================
        */
            if ($urOnly && !$row['is_ur22']) {
                continue;
            }

            $auteurId = $row['auteur_id'];

            if (!$auteurId) {
                continue;
            }

            /*
        =========================================================
        INIT
        =========================================================
        */
            if (!isset($auteurs[$auteurId])) {
                $auteurs[$auteurId] = [
                    'auteur_id' => $auteurId,
                    'auteur_nom' => $row['auteur_nom'],
                    'member_code' => null,
                    'club_nom' => null,
                    'points' => 0,
                    'images' => 0,
                    'competitions' => [],
                ];
            }

            /*
        =========================================================
        AGRÉGATION
        =========================================================
        */
            $auteurs[$auteurId]['points'] += $row['points'];
            $auteurs[$auteurId]['images']++;

            /*
        =========================================================
        MEMBER CODE (EAN prioritaire, sinon participant)
        =========================================================
        */
            if (empty($auteurs[$auteurId]['member_code'])) {

                if (!empty($row['member_code'])) {
                    $auteurs[$auteurId]['member_code'] = $row['member_code'];
                } elseif (!empty($row['participant_id'])) {
                    $auteurs[$auteurId]['member_code'] = substr($row['participant_id'], -4);
                }
            }

            /*
        =========================================================
        CLUB PRINCIPAL
        =========================================================
        */
            if (
                empty($auteurs[$auteurId]['club_nom']) &&
                !empty($row['club_nom'])
            ) {
                $auteurs[$auteurId]['club_nom'] = $row['club_nom'];
            }

            /*
        =========================================================
        COMPÉTITIONS DISTINCTES
        =========================================================
        */
            if (!empty($row['competition_id'])) {
                $auteurs[$auteurId]['competitions'][$row['competition_id']] = true;
            }
        }

        /*
    =========================================================
    NORMALISATION
    =========================================================
    */
        foreach ($auteurs as &$auteur) {
            $auteur['nb_competitions'] = count($auteur['competitions']);
            unset($auteur['competitions']);
        }

        /*
    =========================================================
    TRI (points desc)
    =========================================================
    */
        usort($auteurs, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        /*
    =========================================================
    RANG
    =========================================================
    */
        $rank = 1;
        foreach ($auteurs as &$auteur) {
            $auteur['rang'] = $rank++;
        }

        return $auteurs;
    }


    public function computeTopByCompetition(array $rows, array $options = []): array
    {
        $urOnly = $options['ur_only'] ?? false;

        $competitions = [];

        foreach ($rows as $row) {

            if ($urOnly && !$row['is_ur22']) {
                continue;
            }

            $cid = $row['competition_id'];

            if (!$cid || empty($row['auteur_id'])) {
                continue;
            }

            /*
        =========================================================
        INIT COMPÉTITION
        =========================================================
        */
            if (!isset($competitions[$cid])) {
                $competitions[$cid] = [
                    'nom' => $row['competition_nom'],
                    'type' => $row['competition_type'], // ou mapping si besoin
                    'auteurs' => [],
                ];
            }

            $aid = $row['auteur_id'];

            /*
        =========================================================
        INIT AUTEUR
        =========================================================
        */
            if (!isset($competitions[$cid]['auteurs'][$aid])) {
                $competitions[$cid]['auteurs'][$aid] = [
                    'auteur_nom' => $row['auteur_nom'],
                    'member_code' => $row['member_code'] ?? null,
                    'points' => 0,
                ];
            }

            /*
        =========================================================
        AGRÉGATION
        =========================================================
        */
            $competitions[$cid]['auteurs'][$aid]['points'] += $row['points'];
        }

        /*
    =========================================================
    TRI + TOP 5
    =========================================================
    */
        foreach ($competitions as &$comp) {

            $auteurs = array_values($comp['auteurs']);

            usort($auteurs, function ($a, $b) {
                return $b['points'] <=> $a['points'];
            });

            $rank = 1;
            foreach ($auteurs as &$a) {
                $a['rang'] = $rank++;
            }

            // 🔥 TOP 5 uniquement
            $comp['top5'] = array_slice($auteurs, 0, 5);

            unset($comp['auteurs']);
        }

        return $competitions;
    }


    /**
     * =========================================================
     * computePoints
     * =========================================================
     * 👉 FONCTION CRITIQUE
     * 👉 À adapter selon règles FPF
     *
     * @param array $row
     * @return int
     * =========================================================
     */
    private function computePoints(array $row): int
    {
        /*
        Exemple :
        note / classement / sélection
        */

        if (isset($row['points'])) {
            return (int)$row['points'];
        }

        if (isset($row['note'])) {
            return (int) round($row['note']);
        }

        return 0;
    }
}
