<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ImportWorkflow;
use App\Libraries\CompetitionRanking;
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

        /*
    ============================================================
    🔐 CONFIG
    ============================================================
    */

        $email   = $config->email;
        $password = $config->password;
        $userUr  = env('copain.uruser') ?: 22;
        $profile = env('profil.user') ?: 'regional';

        /*
    ============================================================
    🍪 RESET COOKIE
    ============================================================
    */

        $cookie = WRITEPATH . 'copain_cookie.txt';
        if (file_exists($cookie)) {
            unlink($cookie);
        }

        /*
    ============================================================
    📡 FETCH COMPETITIONS
    ============================================================
    */

        $reader = new \App\Libraries\CopainLegacyReader();

        $data = $reader->getCompetitions($email, $password) ?? [
            'competitions' => [],
            'rcompetitions' => []
        ];

        $competitions  = $data['competitions'] ?? [];
        $rcompetitions = $data['rcompetitions'] ?? [];

        /*
    ============================================================
    👤 USER CONTEXT (⚠️ distinct du type compétition)
    ============================================================
    */

        $isNationalUser = stripos($profile, 'national') !== false;

        $urs = $isNationalUser ? range(1, 25) : [$userUr];
        $urs = array_map(fn($u) => str_pad((string)$u, 2, '0', STR_PAD_LEFT), $urs);

        $defaultUR = str_pad((string)$userUr, 2, '0', STR_PAD_LEFT);

        /*
    ============================================================
    🔗 ROUTES
    ============================================================
    */

        $routes = [
            'base' => base_url(),
            'import' => [
                'start'    => base_url('import/start'),
                'progress' => base_url('import/progress'),
                'step'     => base_url('import/step'),
                'zip'      => base_url('import/zip'),
                'db'       => base_url('import/db'),
                'full'     => base_url('import/full'),
            ]
        ];

        /*
    ============================================================
    🗄️ MAP LOCAL DB
    ============================================================
    */

        $model = new \App\Models\CompetitionModel();
        $locals = $model->findAll();

        $map = [];
        foreach ($locals as $l) {
            $map[$l['id']] = $l;
        }

        /*
    ============================================================
    ⚙️ NORMALISATION + ÉTATS
    ============================================================
    */

        $sessionStates = [];

        $normalize = function ($list) use ($map, &$sessionStates) {

            foreach ($list as &$c) {

                $id = $c['id'] ?? null;
                if (!$id) continue;

                /*
            ---------------------------
            🔍 TYPE COMPETITION (CORRECT)
            ---------------------------
            */
                $type = ($c['urs_id'] === null) ? 'N' : 'R';

                $isN = ($type === 'N');
                $isR = ($type === 'R');

                $c['type']       = $type;
                $c['isNational'] = $isN;
                $c['isRegional'] = $isR;

                /*
            ---------------------------
            🔎 LOCAL DB
            ---------------------------
            */
                $local = $map[$id] ?? null;
                $dateRaw = $local['date_competition'] ?? null;

                /*
            ---------------------------
            📅 DATE / STATUS
            ---------------------------
            */
                $isJudged = false;
                $isPending = true;
                $dateFormatted = null;

                if ($dateRaw && $dateRaw !== '0000-00-00') {

                    $date = strtotime($dateRaw);
                    $today = strtotime(date('Y-m-d'));

                    $dateFormatted = date('d/m/Y', $date);

                    $isJudged = ($date <= $today);
                    $isPending = !$isJudged;
                }

                /*
            ---------------------------
            📦 IMPORT STATUS
            ---------------------------
            */
                $DBok = !empty($local);
                $ZIPok = false; // à brancher plus tard

                /*
            ---------------------------
            🎯 ENRICHISSEMENT VIEW
            ---------------------------
            */
                $c['isNational']   = $isN;
                $c['isRegional']   = $isR;
                $c['dateJugement'] = $dateFormatted;
                $c['isJudged']     = $isJudged;
                $c['isPending']    = $isPending;
                $c['is_imported']  = $DBok;

                /*
            ---------------------------
            💾 SESSION STATE
            ---------------------------
            */
                $sessionStates[$id] = [
                    'type'      => $type,
                    'isN'       => $isN,
                    'isR'       => $isR,
                    'isJudged'  => $isJudged,
                    'isPending' => $isPending,
                    'DBok'      => $DBok,
                    'ZIPok'     => $ZIPok,
                ];
            }

            return $list;
        };

        /*
    ============================================================
    🔄 APPLY
    ============================================================
    */

        $competitions  = $normalize($competitions);
        $rcompetitions = $normalize($rcompetitions);

        /*
    ============================================================
    💾 SESSION STRUCTURÉE
    ============================================================
    */
        $defaultUR = str_pad((string)$userUr, 2, '0', STR_PAD_LEFT);
        log_message('debug', '[USER UR] = ' . $userUr);

        session()->set([
            'user' => [
                'ur'         => $userUr,
                'profil'     => $profile,
                'isNational' => $isNationalUser
            ],
            'filters' => [
                'activeUR' => $defaultUR,
                'activeMode' => 'regional' // 🔥 important
            ],
            'urs' => $urs,
            'competition_states' => $sessionStates
        ]);

        /*
    ============================================================
    VIEW
    ============================================================
    */

        return view('import/copain', [
            'email'         => $email,
            'userProfil'    => $profile,
            'userUr'        => $userUr,
            'urs'           => $urs,
            'defaultUR'     => $defaultUR,
            'isNational'    => $isNationalUser, // ⚠️ USER uniquement
            'routes'        => $routes,
            'competitions'  => $competitions,
            'rcompetitions' => $rcompetitions,
        ]);
    }

    public function importViaController($id, $type)
    {
        $home = new \App\Controllers\Home();

        if ($type === 'N') {
            return $home->importnational($id);
        }

        return $home->importregional($id);
    }

    public function importFull($id)
    {
        $id = (int)$id;
        $type = $this->request->getGet('type') ?? 'R';

        log_message('debug', "[FULL IMPORT] START ID={$id} TYPE={$type}");

        try {

            /*
        ============================================================
        🔵 FULL IMPORT (DB + ZIP déjà inclus)
        ============================================================
        */

            $home = new \App\Controllers\Home();

            if ($type === 'N') {

                log_message('debug', "[FULL IMPORT] NATIONAL");

                $home->importnational($id);
            } else {

                log_message('debug', "[FULL IMPORT] REGIONAL");

                $home->importregional($id);
            }
            /*
        ============================================================
        🧠 COMPUTE CLASSEMENT
        ============================================================
        */

            log_message('debug', "[FULL IMPORT] COMPUTE START");

            $ranking = new CompetitionRanking();
            $ranking->compute($id);

            log_message('debug', "[FULL IMPORT] COMPUTE DONE");


            /*
        ============================================================
        ✅ DONE
        ============================================================
        */

            return $this->response->setJSON([
                'status' => 'ok',
                'type'   => $type,
                'id'     => $id
            ]);
        } catch (\Throwable $e) {

            log_message('error', "[FULL IMPORT] EXCEPTION " . $e->getMessage());

            return $this->response->setJSON([
                'status'  => 'error',
                'step'    => 'full',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function importOne($id)
    {
        $id = (int)$id;

        log_message('debug', '[IMPORT DB] ID=' . $id);

        /*
    --------------------------------------------------
    🎯 TYPE (DEPUIS FRONT)
    --------------------------------------------------
    */
        $type = $this->request->getGet('type') ?? 'R';

        /*
    --------------------------------------------------
    🔧 TYPE COPAIN
    --------------------------------------------------
    */
        $typeCopain = ($type === 'N') ? 'N' : 1;

        log_message('debug', '[IMPORT DB] TYPE=' . $type . ' | COPAIN=' . $typeCopain);

        /*
    --------------------------------------------------
    🔐 LOGIN
    --------------------------------------------------
    */
        $client = new \App\Libraries\CopainClient();
        $client->autoLogin();

        /*
    --------------------------------------------------
    📦 IMPORT
    --------------------------------------------------
    */
        $importer = new \App\Libraries\CopainImporter($client, $id);

        try {

            $result = $importer->importCompetition($id, $typeCopain, 1);

            if (($result['code'] ?? 1) != 0) {

                log_message('error', '[IMPORT DB FAIL] ' . json_encode($result));

                return $this->response->setJSON([
                    'status' => 'error',
                    'type'   => $type,
                    'id'     => $id
                ]);
            }

            /*
        --------------------------------------------------
        🧠 COMPUTE
        --------------------------------------------------
        */
            service('classement')->compute($id);

            /*
        --------------------------------------------------
        📦 RELOAD COMPETITION (APRÈS IMPORT)
        --------------------------------------------------
        */
            $model = new \App\Models\CompetitionModel();
            $competition = $model->find($id);

            /*
        --------------------------------------------------
        ✅ RESPONSE
        --------------------------------------------------
        */
            return $this->response->setJSON([
                'status' => 'ok',
                'type'   => $type,
                'urs_id' => $competition['urs_id'] ?? null,
                'id'     => $id
            ]);
        } catch (\Throwable $e) {

            log_message('error', '[IMPORT DB EXCEPTION] ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'id' => $id
            ]);
        }
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
