<?php

namespace App\Controllers;

use App\Libraries\CopainLegacyReader;
use App\Services\ClassementService;
use App\Models\PhotoModel;

class Home extends BaseController
{
    private $email;
    private $password;

    public function __construct()
    {
        // ✅ récupération depuis config
        $config = config('Copain');

        $this->email    = $config->email;
        $this->password = $config->password;
    }

    public function index()
    {
        $cid = 734; // ou dynamique
        $service = new ClassementService();
        $service->compute($cid, true);

        return redirect()->back()->with('success', 'Classements recalculés');
    }

    public function syntheseUR22()
    {
        $photoModel = new PhotoModel();

        $ur = '22';

        $results = $photoModel->getSyntheseClubsUR($ur);

        log_message('debug', 'SYNTHESE RESULTS: ' . json_encode($results));

        return view('home/synthese_ur22', [
            'results' => $results
        ]);
    }

    public function clubsUR22Nationaux()
    {
        $photoModel = new PhotoModel();

        $clubs = $photoModel->getClubsURParticipantNationaux('22');

        return view('home/clubs_ur22', [
            'clubs' => $clubs
        ]);
    }


    public function compute($cid)
    {
        service('classement')->compute((int)$cid, true);

        return $this->response->setJSON([
            'status' => 'ok',
            'cid' => $cid
        ]);
    }

    public function importnational($id)
    {
        $client = new \App\Libraries\CopainClient();

        /*
    =====================
    LOGIN
    =====================
    */

        $client->autoLogin();

        /*
    =====================
    IMPORT JSON
    =====================
    */

        $importer = new \App\Libraries\CopainImporter($client, $id);

        $result = $importer->importCompetition($id, 'N', 1); // ⚠️ type N

        if (($result['code'] ?? 1) != 0) {
            log_message('error', 'IMPORT JSON FAIL: ' . json_encode($result));
            throw new \RuntimeException('Copain import failed');
        }

        /*
    =====================
    LOAD DB
    =====================
    */

        $model = new \App\Models\CompetitionModel();

        $competition = $model->find($id);

        if (!$competition) {
            log_message('error', 'COMPETITION NOT FOUND: ' . $id);
            throw new \RuntimeException('Copain competition not found');
        }

        /*
    =====================
    ZIP NATIONAL
    =====================
    */

        $zip = new \App\Services\NationalZipService();

        $zip->process($competition);

        return ['code' => 0];
    }

    public function importregional($id)
    {

        session()->close();

        ignore_user_abort(true);
        set_time_limit(0);

        $client = new \App\Libraries\CopainClient();

        /*
    =====================
    LOGIN
    =====================
    */

        $client->autoLogin();

        /*
    =====================
    IMPORT JSON → DB
    =====================
    */

        $importer = new \App\Libraries\CopainImporter($client, $id);

        $result = $importer->importCompetition($id, 1, 1);
        //log_message('debug :', '$result = '.$result);

        if (($result['code'] ?? 1) != 0) {
            throw new \Exception("IMPORT JSON FAIL");
        }

        /*
    =====================
    LOAD DB
    =====================
    */

        $model = new \App\Models\CompetitionModel();

        $competition = $model->find($id);

        log_message('debug', 'COMPETITIONS COUNT=' . print_r($competition));

        if (!$competition) {
            throw new \RuntimeException(
                "Competition not found after import (ID {$id})"
            );
        }

        /*
    =====================
    ZIP
    =====================
    */

        $zip = new \App\Services\RegionalZipService();
        log_message('debug', 'ZIP SERVICE: ' . json_encode($zip));

        $zip->process($competition);

        //service('classement')->compute($id, true);

        return ['code' => 0];
    }






    public function importNationalFromCopain()
    {

        session()->close();

        $config = config('Copain');

        $email    = $config->email;
        $password = $config->password;

        $legacy = new \App\Libraries\CopainLegacyReader();

        /*
    =====================
    LOGIN + RÉCUP DATA
    =====================
    */

        $data = $legacy->getCompetitions($email, $password);

        if (!$data || $data['code'] != 0) {
            throw new \Exception("IMPORT JSON FAIL");
        }

        /*
    =====================
    FILTRER NATIONALES
    =====================
    */

        $competitions = $data['competitions'] ?? [];

        if (empty($competitions)) {
            throw new \Exception("IMPORT JSON FAIL");
        }

        /*
    =====================
    INSERT DB
    =====================
    */

        $model = new \App\Models\CompetitionModel();

        $count = 0;

        foreach ($competitions as $c) {

            // règle : national = urs_id NULL
            $isNational = empty($c['urs_id']);

            if (!$isNational) continue;

            $model->save([
                'id'     => $c['id'],
                'nom'    => $c['nom'],
                'saison' => $c['saison'],
                'urs_id' => null,
                'type'   => 0 // NATIONAL
            ]);

            $count++;
        }

        //service('classement')->compute($competitions['id']);

        return ['code' => 0];
    }



    private function deleteDir($dir)
    {
        if (!is_dir($dir)) return;

        $files = scandir($dir);

        foreach ($files as $file) {

            if ($file == '.' || $file == '..') continue;

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /*
    =====================
    TEST API
    =====================
    */

    public function testCopainApi()
    {
        $reader = new CopainLegacyReader();

        $data = $reader->getCompetitions(
            $this->email,
            $this->password
        );

        log_message('debug', 'API DATA: ' . json_encode($data));
    }

    /*
    =====================
    TEST ZIP COMPLET
    =====================
    */

    public function testZip()
    {
        $cookie = WRITEPATH . 'copain_cookie.txt';

        if (file_exists($cookie)) {
            unlink($cookie);
        }

        /*
        LOGIN
        */

        $legacy = new \App\Libraries\CopainLegacyReader();

        $login = $legacy->getCompetitions(
            $this->email,
            $this->password
        );

        if (!$login || $login['code'] != 0) {
            log_message('error', 'LOGIN FAIL: ' . json_encode($login));

            throw new \RuntimeException('Copain login failed');
        }

        /*
        CHOIX COMPET (ici régional pour test)
        */

        $compR = reset($login['rcompetitions']);

        $ref   = $compR['id'];
        $type  = $compR['type'] ?? 'O';
        $ordre = $compR['ordre'] ?? 'non';

        /*
        CLIENT
        */

        $client = new \App\Libraries\CopainClient();

        /*
        IMPORT
        */

        $import = $client->importCompetition(
            $ref,
            $type,
            $ordre
        );

        if (!$import || $import['code'] != 0) {
            log_message(
                'error',
                'IMPORT FAIL: ' . json_encode($import)
            );

            throw new \RuntimeException(
                'Copain import failed'
            );
        }

        /*
        JSON COMPET
        */

        $json = file_get_contents($import['file_compet']);
        $compet = json_decode($json, true);

        if (!$compet) {
            log_message(
                'error',
                '[JSON FAIL] Unable to decode competition payload'
            );

            throw new \RuntimeException(
                'Competition JSON decode failed'
            );
        }

        /*
        DOSSIER
        */

        $folder =
            $compet['saison'] . '_' .
            str_pad((int)$compet['urs_id'], 2, '0', STR_PAD_LEFT) . '_' .
            $compet['numero'] . '_' .
            $compet['id'];

        $baseDir = FCPATH . 'uploads/competitions/' . $folder;

        if (is_dir($baseDir)) {
            $this->deleteDir($baseDir);
        }

        mkdir($baseDir, 0777, true);
        mkdir($baseDir . '/photos', 0777, true);

        /*
        ZIP
        */

        $zip = $client->generateZip($ref, $type);

        if (!$zip || $zip['code'] != 0) {
            log_message(
                'error',
                'ZIP FAIL: ' . json_encode($zip)
            );

            throw new \RuntimeException(
                'Copain zip generation failed'
            );
        }

        /*
        DOWNLOAD
        */

        $tmpZip = WRITEPATH . 'zip_' . $ref . '.zip';

        $ok = $client->downloadFile(
            $zip['zip_photos'],
            $tmpZip
        );

        if (!$ok) {
            log_message(
                'error',
                'DOWNLOAD FAIL: ' . json_encode($zip)
            );

            throw new \RuntimeException(
                'Copain file download failed'
            );
        }

        /*
        EXTRACT
        */

        $zipArchive = new \ZipArchive;

        if ($zipArchive->open($tmpZip) === TRUE) {
            $zipArchive->extractTo($baseDir . '/photos');
            $zipArchive->close();
        } else {
            log_message(
                'error',
                '[UNZIP FAIL] ZipArchive open/extract failed'
            );

            throw new \RuntimeException(
                'ZIP extraction failed'
            );
        }

        /*
        NORMALISATION
        */

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir . '/photos')
        );

        $count = 0;

        foreach ($iterator as $file) {

            if ($file->isDir()) continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;

            $newName = uniqid() . '.' . $ext;

            rename(
                $file->getPathname(),
                $baseDir . '/photos/' . $newName
            );

            $count++;
        }

        /*
        RESULTAT
        */

        log_message(
            'debug',
            "IMPORT ZIP OK: Images = {$count}, Dossier = {$baseDir}"
        );
    }
}
