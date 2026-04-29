<?php

namespace App\Controllers\Tools;

use App\Controllers\BaseController;

class GenererVignettes extends BaseController
{
    /*
    ==================================================
    🔁 MÉTHODE EXISTANTE (IMPORT - BATCH)
    ==================================================
    ⚠️ Conservée mais sécurisée
    */

    public function index($competition_id = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        if (!$competition_id) {
            echo "Competition non spécifiée";
            return;
        }

        $competition = $this->getCompetition($competition_id);

        if (!$competition) {
            echo "Competition introuvable";
            return;
        }

        [$photosPath, $thumbsPath] = $this->initPaths($competition);

        $files = $this->getFiles($photosPath);

        $manager = \Config\Services::image();

        $batch = 100;
        $processed = 0;
        $generated = 0;

        foreach ($files as $file) {

            $filename = basename($file);

            // 🔒 garder ton filtre import
            if (!preg_match('/^[0-9]+\.jpg$/i', $filename)) {
                continue;
            }

            $thumbFile = $thumbsPath . $filename;

            if (file_exists($thumbFile)) {
                continue;
            }

            try {

                $image = clone $manager;

                $image
                    ->withFile($file)
                    ->fit(300, 300, 'center')
                    ->save($thumbFile);

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

        echo "<hr>";
        echo "Batch : $processed<br>";
        echo "Générées : $generated<br>";
    }

    /*
    ==================================================
    🔥 REGENERATION COMPLETE
    ==================================================
    */

    public function regenerateAll($competition_id = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        if (!$competition_id) {
            echo "Competition non spécifiée";
            return;
        }

        $competition = $this->getCompetition($competition_id);

        if (!$competition) {
            echo "Competition introuvable";
            return;
        }

        [$photosPath, $thumbsPath] = $this->initPaths($competition);

        // 🔥 suppression totale
        if (is_dir($thumbsPath)) {
            foreach (glob($thumbsPath . '*') as $file) {
                unlink($file);
            }
        }

        $files = $this->getFiles($photosPath);

        $generated = 0;
        $errors = 0;

        foreach ($files as $file) {

            $filename = basename($file);
            $thumbFile = $thumbsPath . $filename;

            try {

                $this->generateThumb($file, $thumbFile);

                echo "✔ $filename<br>";
                $generated++;
            } catch (\Throwable $e) {

                echo "❌ $filename<br>";
                $errors++;
            }

            flush();
        }

        echo "<hr>";
        echo "Total : " . count($files) . "<br>";
        echo "Générées : $generated<br>";
        echo "Erreurs : $errors<br>";
    }

    /*
    ==================================================
    🧩 REPRISE (MANQUANTES UNIQUEMENT)
    ==================================================
    */

    public function resumeMissing($competition_id = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        if (!$competition_id) {
            echo "Competition non spécifiée";
            return;
        }

        $competition = $this->getCompetition($competition_id);

        if (!$competition) {
            echo "Competition introuvable";
            return;
        }

        [$photosPath, $thumbsPath] = $this->initPaths($competition);

        $files = $this->getFiles($photosPath);

        $generated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($files as $file) {

            $filename = basename($file);
            $thumbFile = $thumbsPath . $filename;

            if (file_exists($thumbFile)) {
                $skipped++;
                continue;
            }

            try {

                $this->generateThumb($file, $thumbFile);

                echo "✔ $filename<br>";
                $generated++;
            } catch (\Throwable $e) {

                echo "❌ $filename<br>";
                $errors++;
            }

            flush();
        }

        echo "<hr>";
        echo "Total : " . count($files) . "<br>";
        echo "Générées : $generated<br>";
        echo "Déjà existantes : $skipped<br>";
        echo "Erreurs : $errors<br>";
    }

    /*
    ==================================================
    🧱 HELPERS (FACTORISATION PROPRE)
    ==================================================
    */

    protected function getCompetition($id)
    {
        return \Config\Database::connect()
            ->table('competitions')
            ->where('id', $id)
            ->get()
            ->getRow();
    }

    protected function initPaths($competition)
    {
        $storage = new \App\Libraries\CompetitionStorage();

        $photosPath = $storage->getPhotosPath($competition);
        $thumbsPath = $storage->getThumbsPath($competition);

        if (!is_dir($photosPath)) {
            die("Dossier photos introuvable");
        }

        if (!is_dir($thumbsPath)) {
            mkdir($thumbsPath, 0775, true);
        }

        return [$photosPath, $thumbsPath];
    }

    protected function getFiles($photosPath)
    {
        $files = glob($photosPath . '*.{jpg,JPG,jpeg,JPEG}', GLOB_BRACE);
        sort($files);

        return $files;
    }

    protected function generateThumb($source, $dest)
    {
        $image = \Config\Services::image();

        $image
            ->withFile($source)
            ->fit(300, 300, 'center')
            ->save($dest);

        $image->clear();
    }

    public function resumeAllCompetitions()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $controlFile = WRITEPATH . 'tmp/thumbs_control.json';

        // créer fichier si absent
        if (!file_exists($controlFile)) {
            file_put_contents($controlFile, json_encode([
                'stop' => false,
                'last_dir' => null
            ]));
        }

        $control = json_decode(file_get_contents($controlFile), true);

        log_message('debug', 'START RESUME ALL COMPETITIONS');
        echo "<h2>🚀 START</h2>";
        flush();

        $basePath = FCPATH . 'uploads/competitions/';

        $dirs = array_filter(scandir($basePath), function ($dir) use ($basePath) {
            return !in_array($dir, ['.', '..']) &&
                !str_starts_with($dir, '.') &&
                is_dir($basePath . $dir);
        });

        sort($dirs);

        $resumeMode = $control['last_dir'] !== null;
        $startProcessing = !$resumeMode;

        foreach ($dirs as $dir) {

            // 🔁 REPRISE : on saute jusqu'au dernier dossier
            if ($resumeMode && !$startProcessing) {
                if ($dir === $control['last_dir']) {
                    $startProcessing = true;
                }
                continue;
            }

            // 🔴 STOP DEMANDÉ
            $control = json_decode(file_get_contents($controlFile), true);
            if (!empty($control['stop'])) {
                echo "<br>🛑 STOP DEMANDÉ - arrêt propre<br>";
                log_message('debug', 'STOP REQUESTED');
                return;
            }

            // sauvegarde checkpoint
            $control['last_dir'] = $dir;
            file_put_contents($controlFile, json_encode($control));

            $competitionPath = $basePath . $dir . '/';
            $photosPath = $competitionPath . 'photos/';
            $thumbsPath = $competitionPath . 'thumbs/';

            if (!is_dir($photosPath)) {
                log_message('debug', "SKIP $dir (no photos)");
                continue;
            }

            if (!is_dir($thumbsPath)) {
                mkdir($thumbsPath, 0775, true);
            }

            echo "<h3>📁 $dir</h3>";
            log_message('debug', "PROCESS $dir");

            $files = glob($photosPath . '*.{jpg,JPG,jpeg,JPEG}', GLOB_BRACE);

            $count = 0;

            foreach ($files as $file) {

                $filename = basename($file);
                $thumbFile = $thumbsPath . $filename;

                if (file_exists($thumbFile)) {
                    continue;
                }

                try {

                    $this->generateThumb($file, $thumbFile);
                    echo "✔ $filename<br>";
                } catch (\Throwable $e) {

                    echo "❌ $filename<br>";
                    log_message('error', "ERROR $filename : " . $e->getMessage());
                }

                $count++;

                // ⚡ éviter freeze navigateur
                if ($count % 20 === 0) {
                    flush();
                }
            }

            echo "<hr>";
            flush();
        }

        // reset contrôle
        file_put_contents($controlFile, json_encode([
            'stop' => false,
            'last_dir' => null
        ]));

        echo "<h2>✅ TERMINÉ</h2>";
        log_message('debug', 'END RESUME ALL');
    }


    public function scanCompetitionsStatus()
    {
        $basePath = FCPATH . 'uploads/competitions/';

        if (!is_dir($basePath)) {
            return $this->response->setJSON(['error' => 'Dossier introuvable']);
        }

        $dirs = scandir($basePath);

        /*
    =========================
    LOAD DB + INDEX PAR ID
    =========================
    */

        $db = \Config\Database::connect();
        $competitions = $db->table('competitions')->get()->getResult();

        $competitionsById = [];

        foreach ($competitions as $comp) {
            if (!empty($comp->id)) {
                $competitionsById[(int)$comp->id] = $comp;
            }
        }

        /*
    =========================
    SCAN FILESYSTEM
    =========================
    */

        $results = [];

        foreach ($dirs as $dir) {

            if ($dir === '.' || $dir === '..' || str_starts_with($dir, '.')) {
                continue;
            }

            $competitionPath = $basePath . $dir . '/';
            $photosPath = $competitionPath . 'photos/';
            $thumbsPath = $competitionPath . 'thumbs/';

            if (!is_dir($photosPath)) {
                continue;
            }

            /*
        =========================
        EXTRACTION ID (INT)
        =========================
        */

            $parts = explode('_', $dir);

            $id = (isset($parts[2]) && is_numeric($parts[2]))
                ? (int)$parts[2]
                : null;

            /*
        =========================
        MATCH DB
        =========================
        */

            $comp = ($id !== null && isset($competitionsById[$id]))
                ? $competitionsById[$id]
                : null;

            $nom = $comp->nom ?? ('⚠️ ' . $dir);

            /*
        =========================
        COUNT FILES
        =========================
        */

            $photos = glob($photosPath . '*.{jpg,JPG,jpeg,JPEG}', GLOB_BRACE);
            $thumbs = is_dir($thumbsPath)
                ? glob($thumbsPath . '*.{jpg,JPG,jpeg,JPEG}', GLOB_BRACE)
                : [];

            $nbPhotos = count($photos);
            $nbThumbs = count($thumbs);

            /*
        =========================
        STATUS
        =========================
        */

            if ($nbPhotos === 0) {
                $status = 'empty';
            } elseif ($nbThumbs === 0) {
                $status = 'missing';
            } elseif ($nbThumbs < $nbPhotos) {
                $status = 'incomplete';
            } else {
                $status = 'ok';
            }

            /*
        =========================
        RESULT
        =========================
        */

            $results[] = [
                'code' => $dir,
                'id' => $id,
                'nom' => $nom,
                'photos' => $nbPhotos,
                'thumbs' => $nbThumbs,
                'missing' => max(0, $nbPhotos - $nbThumbs),
                'status' => $status,
                'completion' => $nbPhotos > 0
                    ? round(($nbThumbs / $nbPhotos) * 100)
                    : 0,
                'has_db' => $comp !== null
            ];
        }

        /*
    =========================
    SORT
    =========================
    */

        usort($results, function ($a, $b) {
            return $a['completion'] <=> $b['completion'];
        });

        return $this->response->setJSON($results);
    }
}
