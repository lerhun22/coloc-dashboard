<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ImportWorkflow;
use App\Services\CopainImportService;
use App\Models\CompetitionModel;

class ImportFromCopain extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | START
    |--------------------------------------------------------------------------
    */


public function index()
{
    $config = config('Copain');
    
    // 🔐 CONFIG
    $password = $config->password;
    $email   = $config->email;
    $profile = $config->profiluser;
    $userUr  = $config->uruser;


    $cookie = WRITEPATH . 'copain_cookie.txt';

    // 🔥 reset cookie AVANT login
    if (file_exists($cookie)) {
        unlink($cookie);
    }

    // =========================
    // LOGIN + SESSION
    // =========================
    $legacy = new \App\Libraries\CopainLegacyReader();

        $reader =
            new \App\Libraries\CopainLegacyReader();

        $data =
            $reader->getCompetitions(
                $config->email,
                $config->password
            );

        if (!$data) {
            $data = [
                'competitions' => [],
                'rcompetitions' => []
            ];
        }
    
    $data['competitions']  = $data['competitions']  ?? [];
    $data['rcompetitions'] = $data['rcompetitions'] ?? [];

    // 🎯 PROFIL
    $isNational = $profile === 'national';

    // 🧭 UR DISPONIBLES
    $urs = $isNational
        ? range(1, 25)
        : [$userUr];

    // formatage UR sur 2 digits
    $urs = array_map(fn($u) => str_pad($u, 2, '0', STR_PAD_LEFT), $urs);

    $defaultUR = str_pad($userUr, 2, '0', STR_PAD_LEFT);

    // 🔗 ROUTES CENTRALISÉES
    $routes = [
        'import' => [
            'start'    => base_url('import/start'),
            'progress' => base_url('import/progress'),
            'step'     => base_url('import/step'),
            'zip'      => base_url('import/zip'),
            'db'       => base_url('import/db'),
        ],
        'copain' => [
            'run' => base_url('competitions/import/run'),
        ]
    ];

    // séparation N / R (sécurisée même si vide)
    $competitionsNational = $data['competitions'];
    $competitionsRegional = $data['rcompetitions'];

    // 📦 COMPETITIONS
    $competitionsNational = $data['competitions'];
    $competitionsRegional = $data['rcompetitions'];

    // 🧪 DEBUG utile
    // log_message('debug', json_encode([
    //     'N' => count($competitionsNational),
    //     'R' => count($competitionsRegional),
    // ]));

    return view('import/copain', [
        // config
        'email'      => $email,
        'userProfil'    => $profile,
        'userUr'     => $userUr,

        // UR
        'urs'        => $urs,
        'defaultUR'  => $defaultUR,
        'isNational' => $isNational,

        // routes
        'routes'     => $routes,

        // compétitions

        'competitions'  => $competitionsNational,
        'rcompetitions'  => $competitionsRegional,
    ]);
}

    public function start($id)
    {
        $id = (int)$id;

        log_message('debug', 'START ' . $id);

        $wf = new ImportWorkflow($id);

        // 🔥 étape unique simplifiée
        $wf->setStep('process_zip', 0);

        return redirect()->to(
            base_url("import/progress/" . $id)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PROGRESS VIEW
    |--------------------------------------------------------------------------
    */

    public function progress($id)
    {
        return view('import/progress', [
            'id' => (int)$id
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STEP (ORCHESTRATION)
    |--------------------------------------------------------------------------
    */

    public function step($id)
    {
        $id = (int)$id;

        $wf = new ImportWorkflow($id);

        $state = $wf->getState();

        $step     = $state['step'];
        $progress = $state['progress'];

        log_message('debug', "[IMPORT] ID={$id} STEP={$step} PROGRESS={$progress}");

        switch ($step) {

            /*
            ---------------------------
            PROCESS GLOBAL (DB + ZIP)
            ---------------------------
            */

            case 'process_zip':

                $service = new CopainImportService();

                try {

                    // 🔥 pipeline complet
                    $result = $service->importZipFromCopain($id);

                    if (!empty($result['error'])) {

                        log_message('error', '[IMPORT ERROR] ' . $result['error']);

                        $wf->error($result['error']);
                        break;
                    }

                    /*
                    ---------------------------
                    DONE
                    ---------------------------
                    */

                    $wf->setStep('done', 100);

                } catch (\Throwable $e) {

                    log_message('error', '[IMPORT EXCEPTION] ' . $e->getMessage());

                    $wf->error('exception');
                }

                break;

            /*
            ---------------------------
            DONE
            ---------------------------
            */

            case 'done':
                break;

            /*
            ---------------------------
            ERROR
            ---------------------------
            */

            case 'error':
                break;
        }

        return $this->response->setJSON(
            $wf->getState()
        );
    }
}