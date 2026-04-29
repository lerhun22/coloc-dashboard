<?php

namespace App\Services;

use App\Libraries\CopainClient;
use App\Libraries\CompetitionStorage;

class NationalZipService
{
    public function process($competition): bool
    {

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        log_message('debug', 'NATIONAL ZIP -> ' . ($competition->id ?? $competition['id'] ?? 'NULL'));

        if (!$competition) {
            throw new \Exception("Competition invalide");
        }

        // 🔒 récupération ID compatible array / object
        $ref = is_array($competition)
            ? ($competition['id'] ?? null)
            : ($competition->id ?? null);

        if (!$ref) {
            throw new \Exception("Competition sans ID");
        }

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

        // 🔒 sécurité ZIP corrompu
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

        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $zipArchive = new \ZipArchive();

        $status = $zipArchive->open($zipPath);

        if ($status !== true) {
            throw new \RuntimeException(
                'ZIP open failed code ' . $status
            );
        }

        if (!$zipArchive->extractTo($paths['photos'])) {
            throw new \RuntimeException(
                'ZIP extraction failed'
            );
        }

        $zipArchive->close();

        log_message('debug', '[ZIP N] EXTRACT OK');

        /*
        =====================
        STEP 5 — FLATTEN
        =====================
        */

        $this->flattenPhotos($paths['photos']);

        log_message('debug', '[ZIP N] FLATTEN OK');

        /*
        =====================
        STEP 6 — THUMBS
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
        STEP 7 — CLEANUP
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
        if (!is_dir($photosPath)) {
            log_message('error', '[ZIP N] flattenPhotos: dossier introuvable');
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($photosPath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {

            if ($file->isDir()) continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;

            $dest = $photosPath . basename($file);

            // 🔒 évite overwrite + boucles
            if ($file->getPathname() !== $dest && !file_exists($dest)) {
                rename($file->getPathname(), $dest);
            }
        }
    }
}
