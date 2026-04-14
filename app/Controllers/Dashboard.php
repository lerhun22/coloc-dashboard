<?php

namespace App\Controllers;

use App\Services\DataProvider;
use App\Services\SyntheseService;
use App\Services\ClassementService;
use App\Services\JugementService;
use App\Services\ClubStatsService;

class Dashboard extends BaseController
{
    /*
    ============================================================
    🏠 ACCUEIL
    ============================================================
    */
    public function index()
    {
        $configPath = WRITEPATH . 'config/application.ini';

        $app_config = file_exists($configPath)
            ? parse_ini_file($configPath)
            : [];

        $data = array_merge($this->data, [
            'current_version'      => $app_config['version-no'] ?? '',
            'current_version_date' => $app_config['version-update'] ?? '',
            'official_build'       => $app_config['official-build'] ?? '0',
            'local_build_date'     => $app_config['local-build-date'] ?? '',
            'environment'          => strtoupper($app_config['environment'] ?? ENVIRONMENT),
            'build_number'         => $app_config['build-number'] ?? '',
            'author_email'         => $app_config['local-author-email'] ?? '',
            'origin'               => $app_config['local-origin'] ?? '',
        ]);

        return view('dashboard/index', $data);
    }

    /*
    ============================================================
    📊 ANALYSE GLOBALE
    ============================================================
    */






    public function analyse()
    {
        $db = \Config\Database::connect();

        $annee = $this->getCurrentSeason($db);

        $dataProvider = new \App\Services\DataProvider();
        $rows = $dataProvider->getAnnualData($annee);

        $synthese = new \App\Services\SyntheseService();
        $jugementService = new \App\Services\JugementService();
        $clubStatsService = new \App\Services\ClubStatsService();
        $classementService = new \App\Services\ClassementService();

        /*
    ============================================================
    GLOBAL
    ============================================================
    */
        $global = $synthese->computeGlobalStats($rows);
        $competitions = $synthese->computeCompetitionStats($rows);
        $auteurs = $classementService->computeAuthorRankingFromRows($rows);
        $jugement = $jugementService->computeJudgeStats($annee);

        $national = array_filter($competitions, fn($c) => empty($c['urs_id']));
        $regional = array_filter($competitions, fn($c) => !empty($c['urs_id']));

        /*
    ============================================================
    CLUBS UR22
    ============================================================
    */
        $clubsExtended = $clubStatsService->compute($rows);

        /*
    ============================================================
    TRI FINAL (ALIGNÉ SQL)
    ============================================================
    */
        usort(
            $clubsExtended,
            fn($a, $b) =>
            $b['total_points'] <=> $a['total_points']
        );

        /*
    ============================================================
    RANG
    ============================================================
    */
        $rank = 1;
        foreach ($clubsExtended as &$c) {
            $c['rang'] = $rank++;
        }
        unset($c);

        /*
    ============================================================
    VIEW
    ============================================================
    */
        return view('dashboard/analyse', [
            'annee' => $annee,
            'global' => $global,
            'national' => $national,
            'regional' => $regional,
            'clubsExtended' => $clubsExtended,
            'auteurs' => $auteurs,
            'jugement' => $jugement,
        ]);
    }

    /*
    ============================================================
    🎯 SYNTHÈSE VISUELLE (JUGEMENT + WOW)
    ============================================================
    */
    public function synthese()
    {
        $db = \Config\Database::connect();

        /*
    ============================================================
    📅 ANNÉE
    ============================================================
    */
        $row = $db->query("SELECT MAX(saison) as max_saison FROM competitions")
            ->getRowArray();

        $annee = (int)($row['max_saison'] ?? date('Y'));

        /*
    ============================================================
    🎯 FILTRES
    ============================================================
    */
        $filters = [
            'type' => $this->request->getGet('type'),
            'categorie' => $this->request->getGet('categorie'),
            'ur' => $this->request->getGet('ur'),
            'competition_id' => $this->request->getGet('competition_id'),
        ];

        /*
    ============================================================
    📊 DATA
    ============================================================
    */
        $dataProvider = new DataProvider();
        $clubsMap = [];

        $clubsDb = $db->table('clubs')->get()->getResultArray();

        foreach ($clubsDb as $club) {
            $clubsMap[$club['id']] = $club['numero']; // <-- matricule FPF
        }

        $rows = $dataProvider->getAnnualData($annee);

        $syntheseService = new SyntheseService();
        $classementService = new ClassementService();
        $jugementService = new JugementService();

        $global = $syntheseService->computeGlobalStats($rows);

        $classementClubs = $classementService
            ->computeClubRankingFromRows($rows, ['ur_only' => true]);

        $auteurs = $classementService
            ->computeAuthorRankingFromRows($rows, ['ur_only' => true]);

        $competitions = $syntheseService->computeCompetitionStats($rows);

        /*
    ============================================================
    🔥 JUGEMENT (UNE SEULE FOIS)
    ============================================================
    */
        $jugement = $jugementService->computeJudgeStats($annee, $filters);

        /*
    ============================================================
    🧠 BASE IMAGES (ALL)
    ============================================================
    */
        $allImages = $jugement['all'] ?? [];

        // fallback si "all" absent
        if (empty($allImages)) {
            $allImages = array_merge(
                $jugement['top_clivantes'] ?? [],
                $jugement['top_consensuelles'] ?? [],
                $jugement['top_juge_decisif'] ?? []
            );
        }

        // tri global
        usort($allImages, fn($a, $b) => $b['moyenne'] <=> $a['moyenne']);

        /*
    ============================================================
    ✨ WOW GLOBAL
    ============================================================
    */
        $wow = array_filter(
            $allImages,
            fn($img) => ($img['moyenne'] ?? 0) >= 16
        );

        $wow = array_slice($wow, 0, 60);

        /*
    ============================================================
    🏆 WOW ELITE (CdF / N1)
    ============================================================
    */
        $wowElite = array_filter($allImages, function ($img) {

            $nom = strtolower($img['competition_nom'] ?? '');

            // 🔥 détection robuste
            $isN1 =
                str_contains($nom, 'national 1') ||
                str_contains($nom, 'n1');

            $isCDF =
                str_contains($nom, 'coupe') ||
                str_contains($nom, 'france') ||
                str_contains($nom, 'cdf');

            $isElite = $isN1 || $isCDF;

            return $isElite
                && ($img['moyenne'] ?? 0) >= 16
                && ($img['ecart'] ?? 999) <= 6;
        });

        // tri
        usort($wowElite, fn($a, $b) => $b['moyenne'] <=> $a['moyenne']);

        // limite
        $wowElite = array_slice($wowElite, 0, 60);

        /*
============================================================
🏆 WOW UR22
============================================================
*/

        $wowUR22 = array_filter($allImages, function ($img) {

            $ur = (int)($img['competition_ur'] ?? 0);

            $isUR22 = ($ur === 22);

            return $isUR22
                && ($img['moyenne'] ?? 0) >= 14
                && ($img['ecart'] ?? 999) <= 8;
        });

        usort($wowUR22, fn($a, $b) => $b['moyenne'] <=> $a['moyenne']);

        $wowUR22 = array_slice($wowUR22, 0, 60);


        //dd(array_unique(array_map(function ($img) {
        //    return $img['competition_nom'];
        //}, $allImages)));

        /*
    ============================================================
    📌 COMPÉTITION INFO
    ============================================================
    */
        $competitionInfo = null;

        if (!empty($filters['competition_id'])) {
            $competitionInfo = $db->table('competitions')
                ->where('id', $filters['competition_id'])
                ->get()
                ->getRowArray();
        }

        /*
    ============================================================
    📦 VIEW
    ============================================================
    */
        return view('dashboard/stat_saison', [
            'annee' => $annee,
            'global' => $global,
            'classementClubs' => $classementClubs,
            'auteurs' => $auteurs,
            'competitions' => $competitions,
            'jugement' => $jugement,
            'filters' => $filters,
            'competitionInfo' => $competitionInfo,
            'wow' => $wow,
            'wowElite' => $wowElite,
            'wowUR22' => $wowUR22,
        ]);
    }

    /*
    ============================================================
    🧠 HELPER
    ============================================================
    */
    private function getCurrentSeason($db): int
    {
        $row = $db->query("SELECT MAX(saison) as max_saison FROM competitions")
            ->getRowArray();

        return (int)($row['max_saison'] ?? date('Y'));
    }
}
