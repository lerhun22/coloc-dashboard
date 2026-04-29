<?php

namespace App\Services;

use App\Libraries\CopainClient;

class ImportService
{
    protected $client;

    public function __construct()
    {
        $this->client = new CopainClient();
    }

    public function importWithZip($ref, $type)
    {
        /*
        =====================
        IMPORT DATA
        =====================
        */

        $import = $this->client->importCompetition($ref, $type, 'non');

        if (!$import || $import['code'] != 0) {
            return [
                'status' => 'error',
                'step' => 'import',
                'data' => $import
            ];
        }

        /*
        =====================
        ZIP
        =====================
        */

        if ($type === 'N') {

            // 🔥 ASYNC
            $this->client->generateZipAsync($ref, 'N');

            $zipUrl = $this->client->waitForZip($ref);
        } else {

            // ✅ SYNCHRONE
            $zip = $this->client->generateZip($ref, 'R');

            if (!$zip || $zip['code'] != 0) {
                return [
                    'status' => 'error',
                    'step' => 'zip',
                    'data' => $zip
                ];
            }

            $zipUrl = $zip['zip_photos'];
        }

        if (!$zipUrl) {
            return [
                'status' => 'error',
                'step' => 'zip_timeout'
            ];
        }

        return [
            'status' => 'success',
            'zip' => $zipUrl
        ];
    }
}
