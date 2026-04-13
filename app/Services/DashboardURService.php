<?php

namespace App\Services;

use App\Services\DataProvider;
use App\Services\SyntheseService;
use App\Services\ClassementService;

/**
 * =========================================================
 * DashboardURService
 * =========================================================
 * 👉 ORCHESTRATEUR UNIQUEMENT
 * 👉 aucune logique métier ici
 * =========================================================
 */
class DashboardURService
{
    protected ClassementService $classementService;
    protected SyntheseService $syntheseService;

    public function __construct()
    {
        $this->classementService = new ClassementService();
        $this->syntheseService   = new SyntheseService();
    }



    public function index()
    {
        $annee = 2025;

        $dataProvider = new DataProvider();
        $rows = $dataProvider->getAnnualData($annee);

        $syntheseService = new SyntheseService();
        $classementService = new ClassementService();

        $data['global'] = $syntheseService->computeGlobalStats($rows);

        // 🔥 NOUVEAU
        $data['classementClubs'] = $classementService
            ->computeClubRankingFromRows($rows, [
                'ur_only' => true
            ]);

        return view('dashboard/index', $data);
    }

    /**
     * =========================================================
     * Dashboard complet UR
     * =========================================================
     */
    public function getDashboardUR(int $ur): array
    {
        /*
        =========================================================
        1. DATA SOURCE UNIQUE
        =========================================================
        */
        $classement = $this->classementService->computeClubRanking(2025, [
            'ur_only' => true
        ]);

        /*
        =========================================================
        2. SYNTHÈSE (réutilise classement)
        =========================================================
        */
        $synthese = $this->syntheseService->computeFromClassement($classement);

        /*
        =========================================================
        3. MATRICE
        =========================================================
        */
        $matrice = $this->syntheseService->buildMatrice($classement);

        return [
            'classement' => $classement,
            'syntheseClubs' => $synthese,
            'matriceUR' => $matrice,
        ];
    }
}
