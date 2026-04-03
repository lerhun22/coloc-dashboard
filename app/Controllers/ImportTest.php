<?php

namespace App\Controllers;

use App\Services\CopainImportService;
use App\Libraries\CopainClient;
use App\Services\CopainLegacyReaderService;
use App\Workflows\ImportWorkflow;

class ImportTest extends BaseController
{
    public function zip()
    {
        $service = new CopainImportService(
            new CopainClient(),
            new CopainLegacyReaderService(),
            new ImportWorkflow()
        );

        $result = $service->import([
            'zip_path' => WRITEPATH . 'uploads/test.zip'
        ]);

        //dd($result);
    }
    /*
    public function copain($id)
    {
        $service = new CopainImportService(
            new CopainClient(),
            new CopainLegacyReaderService(),
            new ImportWorkflow()
        );

        $result = $service->import([
            'id' => $id
        ]);

        dd($result);
    }
*/


    public function copain($id)

    {
        $email = "domgury@gmail.com";
        $password = "LhB!56DtV";

        $cookie = WRITEPATH . 'copain_cookie.txt';

        if (file_exists($cookie)) {
            unlink($cookie);
        }

        $legacy = new \App\Libraries\CopainLegacyReader();

        $login = $legacy->getCompetitions(
            $email,
            $password
        );

        if ($login['code'] != 0) {
            dd($login);
        }

        //dd($login);

        $comp = reset($login['rcompetitions']);

        //dd($comp);

        $client = new \App\Libraries\CopainImporter();

        $import = $client->importCompetition(
            723,
            "N",
            'oui'
        );

        dd($import);
    }
}