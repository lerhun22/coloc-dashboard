<?php

namespace App\Services;

use App\Libraries\CopainClient;
use App\Libraries\CompetitionStorage;

class NationalZipService
{
    public function process($competition): bool
    {
        /*
        =====================
        NORMALISATION
        =====================
        */

        if (is_array($competition)) {
            $competition = (object) $competition;
        }

        if (!$competition || empty($competition->id)) {
            throw new \Exception("Competition invalide");
        }

        $ref = $competition->id;

        log_message('debug', 'NATIONAL ZIP -> ' . $ref);

        $client  = new CopainClient();
        $storage = new CompetitionStorage();

        /*
        =====================
        STEP 0 — STRUCTURE
        =====================
        */

        $paths = $storage->ensureStructure($competition);

        log_message('debug', '[ZIP N] BASE PATH = ' . $paths['base']);

        /*
        =====================
        STEP 1 — GENERATE ASYNC
        =====================
        */

        log_message('debug', '[ZIP N] GENERATE ASYNC');

        $client->generateZipAsync($ref, 'N');

        /*
        =====================
        STEP 2 — WAIT ZIP READY
        =====================
        */

        log_message('debug', '[ZIP N] WAIT FOR ZIP');

        $zipUrl = $client->waitForZip($ref);

        if (!$zipUrl) {
            throw new \Exception("ZIP non prêt (timeout)");
        }

        log_message('debug', '[ZIP N] ZIP READY');

        /*
        =====================
        STEP 3 — DOWNLOAD
        =====================
        */

        $zipPath = WRITEPATH . 'imports/' . $ref . '.zip';

        // nettoyage ancien fichier
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $client->autoLogin();

        log_message('debug', '[ZIP N] DOWNLOAD START');

        $ok = $client->downloadFile($zipUrl, $zipPath);

        if (!$ok || !file_exists($zipPath)) {
            throw new \Exception("Download échoué");
        }

        $localSize = filesize($zipPath);

        log_message('debug', '[ZIP N] LOCAL SIZE = ' . $localSize);

        if ($localSize < 1000000) {
            throw new \Exception("ZIP invalide (trop petit)");
        }

        log_message('debug', '[ZIP N] DOWNLOAD OK');

        /*
        =====================
        STEP 4 — EXTRACT
        =====================
        */

        log_message('debug', '[ZIP N] EXTRACT');

        // sécurité gros ZIP
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $zipArchive = new \ZipArchive();

        if ($zipArchive->open($zipPath) !== true) {
            throw new \Exception("ZIP open failed");
        }

        $zipArchive->extractTo($paths['photos']);
        $zipArchive->close();

        log_message('debug', '[ZIP N] EXTRACT OK');

        /*
        =====================
        STEP 5 — FLATTEN (CRITIQUE NATIONAL)
        =====================
        */

        $this->flattenPhotos($paths['photos']);

        log_message('debug', '[ZIP N] FLATTEN OK');

        /*
        =====================
        STEP 6 — DEBUG FILES (OPTIONNEL MAIS UTILE)
        =====================
        */

        foreach (glob($paths['photos'] . '*') as $file) {
            log_message('debug', '[ZIP N] FILE = ' . $file);
        }

        /*
        =====================
        STEP 7 — THUMBS
        =====================
        */

        log_message('debug', '[ZIP N] THUMBS');

        try {
            $tool = new \App\Controllers\Tools\GenererVignettes();
            $tool->index($ref);

            log_message('debug', '[ZIP N] THUMBS OK');

        } catch (\Throwable $e) {
            log_message('error', '[ZIP N] THUMBS ERROR ' . $e->getMessage());
        }

        /*
        =====================
        STEP 8 — CLEANUP
        =====================
        */

        log_message('debug', '[ZIP N] CLEANUP');

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        /*
        =====================
        DONE
        =====================
        */

        log_message('debug', '[ZIP N] PROCESS DONE');

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
            new \RecursiveDirectoryIterator($photosPath, \FilesystemIterator::SKIP_DOTS)
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