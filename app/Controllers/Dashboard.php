<?php

namespace App\Controllers;

use App\Services\DataProvider;
use App\Services\SyntheseService;
use App\Services\ClassementService;
use App\Services\JugementService;
use App\Services\DataPipelineService;
use App\Services\DashboardURService;
use App\Services\SeasonService;
use App\Services\CompetitionLaureatesService;
use App\Services\DataProviderClubs;

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

        $builder = new \App\Services\CompetitionMetaBuilder();

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

    public function coloc()
    {
        $db = \Config\Database::connect();

        /*
    ============================================================
    📅 SAISON
    ============================================================
    */
        $seasonService = new \App\Services\SeasonService();
        $annee = $seasonService->getCurrentSeason($db);

        /*
    ============================================================
    📊 DATA CLUBS (classementclubs)
    ============================================================
    */
        $provider = new \App\Services\DataProviderClubs();
        $rows = $provider->getAnnualData($annee);

        /*
    ============================================================
    🧠 DASHBOARD UR
    ============================================================
    */
        $dashboardService = new \App\Services\DashboardURService();
        helper('competition');

        $dashboardService = new DashboardURService();

        $dashboard = $dashboardService->build(
            $rows,
            currentUR()
        );

        /*
    ============================================================
    🏆 CLASSEMENT NATIONAL OFFICIEL (🔥 clé)
    ============================================================
    */
        $nationalService = new \App\Services\NationalDashboardService();
        $national = $nationalService->getRanking($annee);

        /*
    ============================================================
    📊 TOTAL CLUBS FPF (base complète)
    ============================================================
    */
        $totalClubs = $db->table('clubs')->countAllResults();

        /*
    ============================================================
    🧠 SYNTHÈSE JUGEMENT
    ============================================================
    */
        $jugementService = new \App\Services\JugementService();
        $jugement = $jugementService->computeJudgeStats($annee, []);

        /*
    ============================================================
    ✨ WOW (images fortes)
    ============================================================
    */
        $allImages = $jugement['all'] ?? [];

        usort($allImages, fn($a, $b) => ($b['moyenne'] ?? 0) <=> ($a['moyenne'] ?? 0));

        $wow = array_slice(
            array_filter($allImages, fn($img) => ($img['moyenne'] ?? 0) >= 16),
            0,
            60
        );


        /*
    ============================================================
    📦 VIEW
    ============================================================
    */
        return view('dashboard/coloc', [
            'annee'       => $annee,

            // 🔵 NATIONAL OFFICIEL
            'national'    => $national,

            // 🟢 UR22 + ANALYSE
            'dashboard'   => $dashboard,

            // 📊 CONTEXTE GLOBAL
            'totalClubs'  => $totalClubs,

            // 🔴 SYNTHÈSE
            'jugement'    => $jugement,

            // ✨ WOW
            'wow'         => $wow,
        ]);
    }



    public function analyse()
    {
        $db = \Config\Database::connect();

        $seasonService = new \App\Services\SeasonService();
        $annee = $seasonService->getCurrentSeason($db);

        // 🔥 NOUVEAU provider fiable
        $provider = new \App\Services\DataProviderClubs();
        $rows = $provider->getAnnualData($annee);

        $service = new \App\Services\CompetitionStatsService();
        $result = $service->compute($rows);

        $nationalService = new \App\Services\NationalDashboardService();

        $national = $nationalService->getRanking($annee);

        return view('dashboard/analyse', [
            'regional_by_comp' => $result['regional_by_comp'],
            'national' => $national,
            'debug'  => $result['debug'],
        ]);
    }

    public function export()
    {
        $annee = $this->getCurrentSeason(\Config\Database::connect());

        $pipeline = new DataPipelineService();
        $rows = $pipeline->getRowsClean($annee);

        $dashboard = new DashboardURService();
        $data = $dashboard->build($rows);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ONGLET 1
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clubs');

        $sheet->fromArray(['Rang', 'Club', 'Points'], NULL, 'A1');

        $r = 2;
        foreach ($data['classementClubs'] as $c) {
            $sheet->fromArray([$c['rang'] ?? '', $c['nom'], $c['points']], NULL, "A$r");
            $r++;
        }

        // ONGLET 2
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('UR');

        $sheet2->fromArray(['UR', 'Points', 'Clubs'], NULL, 'A1');

        $r = 2;
        foreach ($data['urRanking'] as $u) {
            $sheet2->fromArray([$u['ur'], $u['points'], $u['clubs']], NULL, "A$r");
            $r++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="dashboard.xlsx"');

        $writer->save('php://output');
        exit;
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

    public function rebuildAll()
    {
        $db = \Config\Database::connect();

        $competitions = $db->table('competitions')
            ->select('id')
            ->get()
            ->getResultArray();

        $service = new \App\Services\NationalStatsService();

        $count = 0;

        foreach ($competitions as $c) {
            $service->rebuildClassementClubs((int)$c['id']);
            $count++;
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'competitions' => $count
        ]);
    }


    public function rebuild($competitionId)
    {
        $service = new \App\Services\NationalStatsService();

        $result = $service->rebuildClassementClubs((int)$competitionId);

        return $this->response->setJSON([
            'status' => 'ok',
            'clubs' => count($result),
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
