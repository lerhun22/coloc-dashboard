<?php

namespace App\Controllers;

use App\Libraries\ColocPipelineService;

class ColocController extends BaseController
{
    /**
     * ============================================================
     * 🚀 DEBUG COMPETITION
     * ============================================================
     *
     * URL :
     * /coloc/debug/714
     *
     * ============================================================
     */
    public function debug(int $competitionId)
    {
        $pipeline = new ColocPipelineService();

        $result = $pipeline->processCompetition($competitionId, true);

        $status = $result['status'] ?? 'error';

        /*
        ============================================================
        ❌ ERREUR
        ============================================================
        */
        if ($status === 'error') {
            dd($result);
        }

        /*
        ============================================================
        🟡 COMPÉTITION NON JUGÉE
        ============================================================
        */
        if ($status === 'empty') {

            echo "<h1>COMPETITION {$competitionId}</h1>";
            echo "<h2 style='color:orange'>⏳ Compétition non jugée</h2>";

            echo "<pre>";
            print_r($result['competition'] ?? []);
            echo "</pre>";

            return;
        }

        /*
        ============================================================
        🟢 COMPÉTITION JUGÉE
        ============================================================
        */

        $competition = $result['competition']
            ?? $result['debug']['competition']
            ?? null;

        $clubs = $result['clubs']
            ?? $result['debug']['clubs']
            ?? [];

        $hasHistory = $result['debug']['has_history'] ?? null;

        /*
        ============================================================
        🖥️ AFFICHAGE
        ============================================================
        */

        echo "<h1>DEBUG COMPETITION {$competitionId}</h1>";

        /*
        ------------------------
        COMPETITION
        ------------------------
        */
        echo "<h2>Competition</h2>";
        echo "<pre>";
        print_r($competition);
        echo "</pre>";

        /*
        ------------------------
        TOP 10 CLUBS
        ------------------------
        */
        echo "<h2>Top 10 Clubs</h2>";

        $topClubs = array_slice($clubs, 0, 10);

        foreach ($topClubs as $club) {

            $sixth = $club['photos'][5]['score'] ?? '-';

            echo "<b>#{$club['rank']} {$club['club_name']}</b><br>";
            echo "Score: {$club['score']}<br>";
            echo "6e photo: {$sixth}<br>";
            echo "Nb 20: {$club['nb_20']} | Nb 19: {$club['nb_19']}<br>";

            // 🔥 statut historique
            if (array_key_exists('is_new', $club)) {
                if ($club['is_new'] === true) {
                    echo "<span style='color:green'>Barrage R</span><br>";
                } elseif ($club['is_new'] === false) {
                    echo "<span style='color:gray'>Déjà présent</span><br>";
                } else {
                    echo "<span style='color:orange'>Historique inconnu</span><br>";
                }
            }

            echo "<br>";
        }

        /*
        ------------------------
        ÉGALITÉS (SAFE)
        ------------------------
        */
        echo "<h2>Égalités détectées</h2>";

        $rawScores = array_column($clubs, 'score');

        $cleanScores = [];

        foreach ($rawScores as $s) {

            if ($s === null) continue;

            $cleanScores[] = (string) round((float)$s, 2);
        }

        $scores = array_count_values($cleanScores);

        foreach ($scores as $score => $count) {

            if ($count > 1) {

                echo "<b>Score = {$score}</b><br>";

                foreach ($clubs as $club) {

                    if ((string) round((float)$club['score'], 2) === $score) {

                        $sixth = $club['photos'][5]['score'] ?? '-';

                        echo "{$club['rank']} - {$club['club_name']} ";
                        echo "(6e: {$sixth}, 20: {$club['nb_20']}, 19: {$club['nb_19']})<br>";
                    }
                }

                echo "<br>";
            }
        }

        /*
        ------------------------
        DUMP COMPLET
        ------------------------
        */
        echo "<h2>Dump complet (clubs)</h2>";
        echo "<pre>";
        print_r($clubs);
        echo "</pre>";

        /*
        ------------------------
        META
        ------------------------
        */
        echo "<h2>Meta</h2>";
        echo "<pre>";
        echo "Historique dispo: " . ($hasHistory ? 'OUI' : 'NON');
        echo "</pre>";
    }
}
