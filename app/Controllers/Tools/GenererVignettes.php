<?php

namespace App\Controllers\Tools;

use App\Controllers\BaseController;

class GenererVignettes extends BaseController
{
    public function index($competition_id = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        if (!$competition_id) {
            echo "Competition non spécifiée";
            return;
        }

        /*
        =========================
        LOAD COMPETITION
        =========================
        */

        $db = \Config\Database::connect();

        $competition = $db->table('competitions')
            ->where('id', $competition_id)
            ->get()
            ->getRow();

        if (!$competition) {
            echo "Competition introuvable";
            return;
        }

        /*
        =========================
        STORAGE (SOURCE UNIQUE)
        =========================
        */

        $storage = new \App\Libraries\CompetitionStorage();

        $photosPath = $storage->getPhotosPath($competition);
        $thumbsPath = $storage->getThumbsPath($competition);

        /*
        =========================
        SECURITY
        =========================
        */

        if (!is_dir($photosPath)) {
            echo "Dossier photos introuvable";
            return;
        }

        if (!is_dir($thumbsPath)) {
            mkdir($thumbsPath, 0775, true);
        }

        /*
        =========================
        PROCESS FILES
        =========================
        */

        $files = glob($photosPath . '*.jpg');
        sort($files);

        $imageService = \Config\Services::image();

        $batch = 100;
        $processed = 0;
        $generated = 0;

        foreach ($files as $file) {

            $filename = basename($file);

            if (!preg_match('/^[0-9]+\.jpg$/', $filename)) {
                continue;
            }

            $thumbFile = $thumbsPath . $filename;

            if (file_exists($thumbFile)) {
                continue;
            }

            try {

                $imageService
                    ->withFile($file)
                    ->fit(300, 300, 'center')
                    ->save($thumbFile);

                $imageService->clear();

                echo "✔ $filename<br>";

                $generated++;
                $processed++;

            } catch (\Throwable $e) {

                echo "❌ erreur $filename<br>";
            }

            if ($processed >= $batch) {
                break;
            }

            flush();
        }

        /*
        =========================
        SUMMARY
        =========================
        */

        echo "<hr>";
        echo "Batch traité : $processed<br>";
        echo "Vignettes générées : $generated<br>";
    }
}