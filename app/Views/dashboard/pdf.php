<?php

namespace App\Controllers;

use App\Services\DashboardURService;

class Dashboard extends BaseController
{
    public function index()
    {
        $configPath = \WRITEPATH . 'config/application.ini';

        $app_config = file_exists($configPath)
            ? parse_ini_file($configPath)
            : [];

        /*
        ============================================================
        📊 DASHBOARD UR (NOUVEAU)
        ============================================================
        */
        $dashboardService = new DashboardURService();

        $ur = getenv('copain.uruser') ?? '22';
        $ur = (int)$ur;

        $dashboardUR = $dashboardService->getPresenceUR($ur);

        $matriceUR = $dashboardService->getMatriceUR($ur);

        $data['matriceUR'] = $matriceUR;

        $syntheseClubs = $dashboardService->getSyntheseClubs($ur);

        $data['syntheseClubs'] = $syntheseClubs;

        /*
        ============================================================
        📦 DATA VIEW
        ============================================================
        */
        $data = array_merge($this->data, [
            'current_version'      => $app_config['version-no'] ?? '',
            'current_version_date' => $app_config['version-update'] ?? '',
            'official_build'       => $app_config['official-build'] ?? '0',
            'local_build_date'     => $app_config['local-build-date'] ?? '',
            'environment'          => strtoupper($app_config['environment'] ?? ENVIRONMENT),
            'build_number'         => $app_config['build-number'] ?? '',
            'author_email'         => $app_config['local-author-email'] ?? '',
            'origin'               => $app_config['local-origin'] ?? '',

            // 🔥 NOUVEAU
            'dashboardUR'          => $dashboardUR,
            'matriceUR'            => $matriceUR,
            'syntheseClubs'        => $syntheseClubs,
        ]);

        return view('competitions/dashboard/index', $data);
    }

    public function exportPdf()
    {
        $dashboardService = new \App\Services\DashboardURService();

        $ur = 22;

        $data = [
            'matriceUR' => $dashboardService->getMatriceUR($ur),
            'dashboardUR' => $dashboardService->getPresenceUR($ur),
            'syntheseClubs' => $dashboardService->getSyntheseClubs($ur),
        ];

        $html = view('dashboard/pdf', $data);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();

        file_put_contents(FCPATH . 'pdf/syntheseUR.pdf', $output);

        return "PDF généré";
    }
}
