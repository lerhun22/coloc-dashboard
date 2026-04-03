<?php

namespace App\Services;

use App\Services\ZipServiceInterface;
use App\Libraries\CopainClient;
use App\Libraries\CompetitionStorage;

class RegionalZipService implements ZipServiceInterface
{
    public function process($competition): bool
    {
        // 🔥 NORMALISATION EN PREMIER
        if (is_array($competition)) {
            $competition = (object) $competition;
        }

        log_message('debug', 'REGIONAL ZIP -> ' . ($competition->id ?? 'NULL'));

        if (!$competition || empty($competition->id)) {
            throw new \Exception("Competition invalide");
        }

        $ref = $competition->id;

        $client  = new CopainClient();
        $storage = new CompetitionStorage();

        /*
        =====================
        STEP 0 — STRUCTURE
        =====================
        */

        $paths = $storage->ensureStructure($competition);

        log_message('debug', '[ZIP R] BASE PATH = ' . $paths['base']);

        /*
        =====================
        STEP 1 — GENERATE ZIP
        =====================
        */

        log_message('debug', '[ZIP R] GENERATE');

        $zip = $client->generateZip(
            $ref,
            $competition->type
        );

        if (!$zip || ($zip['code'] ?? 1) != 0) {
            throw new \Exception("ZIP generation failed");
        }

        $zipUrl = $zip['zip_photos'] ?? null;

        if (!$zipUrl) {
            throw new \Exception("ZIP URL manquante");
        }

        /*
        =====================
        STEP 2 — DOWNLOAD
        =====================
        */

        $zipPath = WRITEPATH . 'imports/' . $ref . '.zip';

        log_message('debug', '[ZIP R] DOWNLOAD');

        if (!$client->downloadFile($zipUrl, $zipPath)) {
            throw new \Exception("Download failed");
        }

        /*
        =====================
        STEP 3 — EXTRACT
        =====================
        */

        log_message('debug', '[ZIP R] EXTRACT');

        $zipArchive = new \ZipArchive();

        if ($zipArchive->open($zipPath) !== true) {
            throw new \Exception("ZIP open failed");
        }

        // extraction dans photos directement
        $zipArchive->extractTo($paths['photos']);
        $zipArchive->close();

        log_message('debug', '[ZIP R] EXTRACT OK');

        /*
        =====================
        STEP 4 — NORMALISATION (OPTION)
        =====================
        */

        $this->flattenPhotos($paths['photos']);

        /*
        =====================
        STEP 5 — THUMBS
        =====================
        */

        log_message('debug', '[ZIP R] THUMBS');

        $tool = new \App\Controllers\Tools\GenererVignettes();
        $tool->index($ref);

        /*
        =====================
        CLEAN
        =====================
        */

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        log_message('debug', '[ZIP R] DONE');

        return true;
    }

    /*
    =====================
    UTIL — FLATTEN DOSSIERS
    =====================
    */

    private function flattenPhotos(string $photosPath): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($photosPath)
        );

        foreach ($iterator as $file) {

            if ($file->isDir()) continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;

            $dest = $photosPath . basename($file);

            // évite overwrite
            if ($file->getPathname() !== $dest) {
                rename($file->getPathname(), $dest);
            }
        }
    }
}