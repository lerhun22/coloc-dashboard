<?php
/*253*/

namespace App\Libraries;

use Config\Database;
use App\Libraries\CopainClient;
use App\Libraries\CompetitionCleaner;

class CopainImporter
{
    private ?CopainClient $client = null;

    private ?string $dir = null;
    private ?string $zipFile = null;
    private ?string $extractDir = null;
    private $competitionId;

    public function __construct($clientOrId = null, $competitionId = null)
    {
        /*
    support old:
    new CopainImporter($id)

    support new:
    new CopainImporter(new CopainClient(), $id)
    */

        if ($clientOrId instanceof CopainClient) {

            $this->client = $clientOrId;
            $id = $competitionId;
        } else {

            $this->client = new CopainClient();
            $id = $clientOrId;
        }


        // ✅ IMPORTANT
        $this->competitionId = $id;


        if ($id) {

            $this->dir =
                WRITEPATH . "imports/$id/";

            $this->zipFile =
                $this->dir . "photos.zip";

            $this->extractDir =
                $this->dir . "extract/";


            if (!is_dir($this->dir))
                mkdir($this->dir, 0777, true);

            if (!is_dir($this->extractDir))
                mkdir($this->extractDir, 0777, true);
        }
    }

    /*
    ===========================
    GET JSON
    ===========================
    */

    private function getJson($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_TIMEOUT => 300

        ]);

        $data = curl_exec($curl);

        curl_close($curl);

        return $data;
    }


    /*
    ===========================
    IMPORT
    ===========================
    */

    public function importCompetition(
        $ref,
        $type,
        $ordre
    ) {
        $db = Database::connect();

        try {

            log_message(
                'debug',
                "IMPORT START $ref"
            );


            /*
            API
            */

            $response =
                $this->client->importCompetition(
                    $ref,
                    $type,
                    $ordre
                );

            log_message('debug', 'IMPORT REF = ' . $ref);

            if (
                !$response
                || ($response['code'] ?? 1) != 0
            ) {
                return ['code' => 'IMPORT_ERROR'];
            }


            /*
            CLEAN
            */

            $exists =
                $db->table('competitions')
                ->where('id', $ref)
                ->countAllResults();



            if ($exists) {

                log_message(
                    'debug',
                    'CLEAN'
                );

                (new CompetitionCleaner())
                    ->deleteCompetition($ref);
            }


            $db->transStart();


            /*
            =====================
            COMPETITION
            =====================
            */

            if (!empty($response['file_compet'])) {

                $compet =
                    json_decode(
                        $this->getJson(
                            $response['file_compet']
                        ),
                        true
                    );

                if ($compet) {

                    $db->table('competitions')->insert([

                        'id' => $compet['id'] ?? $ref,
                        'numero' => $compet['numero'] ?? 0,
                        'type' => $compet['type'] ?? 1,
                        'urs_id' => $compet['urs_id'] ?? null,
                        'saison' => $compet['saison'] ?? '',
                        'nom' => $compet['nom'] ?? '',
                        'date_competition' =>
                        $compet['date_competition'] ?? null,

                        'max_photos_club' =>
                        $compet['max_photos_club'] ?? 0,

                        'max_photos_auteur' =>
                        $compet['max_photos_auteur'] ?? 0,

                        'param_photos_club' =>
                        $compet['param_photos_club'] ?? 0,

                        'param_photos_auteur' =>
                        $compet['param_photos_auteur'] ?? 0,

                        'quota' =>
                        $compet['quota'] ?? 0,

                        'note_min' =>
                        $compet['note_min'] ?? 6,

                        'note_max' =>
                        $compet['note_max'] ?? 20,

                        'nb_auteurs_ur_n2' =>
                        $compet['nb_auteurs_ur_n2'] ?? 0,

                        'nb_clubs_ur_n2' =>
                        $compet['nb_clubs_ur_n2'] ?? 0,

                        'pte' =>
                        $compet['pte'] ?? 0,

                        'nature' =>
                        $compet['nature'] ?? 0,
                    ]);
                }
            }

            log_message('debug', 'COMPET OK');

            /*
            =====================
            CLUBS
            =====================
            */

            if (!empty($response['file_club'])) {

                $rows =
                    json_decode(
                        $this->getJson(
                            $response['file_club']
                        ),
                        true
                    );

                foreach ($rows ?? [] as $c) {

                    $db->table('clubs')
                        ->ignore(true)
                        ->insert($c);
                }
            }

            log_message('debug', 'CLUBS OK');

            /*
            =====================
            PARTICIPANTS
            =====================
            */

            if (!empty($response['file_participant'])) {

                $rows =
                    json_decode(
                        $this->getJson(
                            $response['file_participant']
                        ),
                        true
                    );

                foreach ($rows ?? [] as $p) {

                    $db->table('participants')
                        ->ignore(true)
                        ->insert([

                            'id' =>
                            str_replace(
                                '-',
                                '',
                                $p['id']
                            ),

                            'nom' =>
                            $p['nom'] ?? '',

                            'prenom' =>
                            $p['prenom'] ?? '',

                            'club_id' =>
                            $p['club_id'] ?? null,

                            'competitions_id' =>
                            $ref,
                        ]);
                }
            }

            log_message('debug', 'PARTICIPANTS OK');

            /*
            =====================
            JUGES
            =====================
            */

            if (!empty($response['file_juge'])) {

                $rows =
                    json_decode(
                        $this->getJson(
                            $response['file_juge']
                        ),
                        true
                    );

                foreach ($rows ?? [] as $j) {

                    $db->table('juges')
                        ->ignore(true)
                        ->insert([

                            'id' =>
                            $j['id'],

                            'nom' =>
                            $j['nom'] ?? '',

                            'competitions_id' =>
                            $ref,
                        ]);
                }
            }

            log_message('debug', 'JUGES OK');

            /*
            =====================
            PHOTOS
            =====================
            */

            if (!empty($response['file_photos'])) {

                $rows =
                    json_decode(
                        $this->getJson(
                            $response['file_photos']
                        ),
                        true
                    );

                foreach ($rows ?? [] as $p) {

                    $db->table('photos')
                        ->insert([

                            'id' =>
                            $p['id'],

                            'ean' =>
                            $p['ean'],

                            'competitions_id' =>
                            $ref,

                            'participants_id' =>
                            isset(
                                $p['participants_id']
                            )
                                ? str_replace(
                                    '-',
                                    '',
                                    $p['participants_id']
                                )
                                : 0,

                            'titre' =>
                            html_entity_decode(
                                $p['titre'] ?? ''
                            ),

                            'statut' =>
                            $p['statut'] ?? 0,

                            'saisie' =>
                            $p['saisie'] ?? 0,

                            'passage' =>
                            $p['passage'] ?? 0,

                            'disqualifie' =>
                            $p['disqualifie'] ?? 0,
                        ]);
                }
            }

            log_message('debug', 'PHOTOS OK');

            /*
            =====================
            NOTES
            =====================
            */

            if (!empty($response['file_note'])) {

                $rows =
                    json_decode(
                        $this->getJson(
                            $response['file_note']
                        ),
                        true
                    );

                foreach ($rows ?? [] as $n) {

                    $db->table('notes')
                        ->insert([

                            'juges_id' =>
                            $n['juges_id'],

                            'photos_id' =>
                            $n['photos_id'],

                            'note' =>
                            $n['note'],

                            'competitions_id' =>
                            $ref,
                        ]);
                }
            }

            log_message('debug', 'NOTES OK');

            /*
            =====================
            MEDAILLES
            =====================
            */

            if (!empty($response['file_medaille'])) {

                $rows =
                    json_decode(
                        $this->getJson(
                            $response['file_medaille']
                        ),
                        true
                    );

                foreach ($rows ?? [] as $m) {

                    $db->table('medailles')
                        ->ignore(true)
                        ->insert([

                            'id' =>
                            $m['id'],

                            'nom' =>
                            $m['nom'],

                            'fpf' =>
                            $m['fpf'] ?? 0,

                            'competitions_id' =>
                            $ref,
                        ]);
                }
            }

            log_message('debug', 'MEDAILLES OK');

            /*
=====================
CLASSEMENT AUTEURS
=====================
*/

            if (!empty($response['file_classement_auteurs'])) {

                $rows = json_decode(
                    $this->getJson($response['file_classement_auteurs']),
                    true
                );

                foreach ($rows ?? [] as $r) {

                    $db->table('classementauteurs')->replace([
                        'competitions_id' => $ref,
                        'participants_id' => str_replace('-', '', $r['participants_id']),
                        'total' => $r['total'] ?? 0,
                        'place' => $r['place'] ?? 0,
                        'nb_photos' => $r['nb_photos'] ?? 0,
                    ]);
                }
            }

            log_message('debug', 'CLASSEMENT AUTEURS OK');


            /*
=====================
CLASSEMENT CLUBS
=====================
*/

            if (!empty($response['file_classement_clubs'])) {

                $rows = json_decode(
                    $this->getJson($response['file_classement_clubs']),
                    true
                );

                foreach ($rows ?? [] as $r) {

                    $db->table('classementclubs')->replace([
                        'competitions_id' => $ref,
                        'clubs_id' => $r['clubs_id'],
                        'total' => $r['total'] ?? 0,
                        'place' => $r['place'] ?? 0,
                        'nb_photos' => $r['nb_photos'] ?? 0,
                    ]);
                }
            }

            log_message('debug', 'CLASSEMENT CLUBS OK');


            /*
=====================
FLAGS
=====================
*/

            $db->table('classements')->replace([
                'competitions_id' => $ref,
                'afaire'  => 0,
                'graphe'  => 1,
                'photos'  => 1,
                'clubs'   => 1,
                'auteurs' => 1,
            ]);

            log_message('debug', 'CLASSEMENTS FLAGS OK');




            $db->transComplete();

            log_message('debug', 'IMPORT END');


            return ['code' => 0];
        } catch (\Throwable $e) {
            log_message(
                'error',
                $e->getMessage()
            );

            return ['code' => 'EXCEPTION'];
        }
    }












    public function downloadChunk()
    {
        $ref = $this->competitionId;

        if (!$ref) return true;

        $wf =
            new \App\Libraries\ImportWorkflow($ref);


        $zipPath =
            WRITEPATH .
            "imports/$ref.zip";

        $infoFile =
            WRITEPATH .
            "imports/$ref/info.json";


        /*
    -----------------------
    get url + size once
    -----------------------
    */

        if (!file_exists($infoFile)) {

            $zipInfo =
                $this->client
                ->generateZip($ref, 1);

            if (
                !$zipInfo
                || empty($zipInfo['zip_photos'])
            ) {
                log_message('error', 'NO ZIP');
                return true;
            }

            $url =
                $zipInfo['zip_photos'];

            $size =
                $this->client
                ->getRemoteFileSize($url);

            file_put_contents(
                $infoFile,
                json_encode([
                    'url' => $url,
                    'size' => $size
                ])
            );
        }


        $info =
            json_decode(
                file_get_contents($infoFile),
                true
            );

        $url =
            $info['url'];

        $size =
            $info['size'] ?? 0;


        if (!$size) {
            log_message('error', 'SIZE ERROR');
            return true;
        }


        /*
    -----------------------
    local size
    -----------------------
    */

        $local =
            file_exists($zipPath)
            ? filesize($zipPath)
            : 0;


        $wf->update([

            'size' => $size,
            'downloaded' => $local

        ]);


        /*
    -----------------------
    done ?
    -----------------------
    */

        if ($local >= $size) {

            log_message(
                'debug',
                'DOWNLOAD DONE'
            );

            return true;
        }


        /*
    -----------------------
    chunk 5MB
    -----------------------
    */

        $chunk =
            5 * 1024 * 1024;

        $start =
            $local;

        $end =
            $local + $chunk - 1;


        $this->downloadRange(
            $url,
            $zipPath,
            $start,
            $end
        );


        return false;
    }
    private function downloadRange(
        $url,
        $dest,
        $start,
        $end
    ) {

        $fp =
            fopen(
                $dest,
                $start == 0 ? 'wb' : 'ab'
            );

        $curl =
            curl_init($url);

        curl_setopt_array($curl, [

            CURLOPT_FILE => $fp,

            CURLOPT_RANGE =>
            $start . "-" . $end,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_COOKIEJAR =>
            WRITEPATH . 'copain_cookie.txt',

            CURLOPT_COOKIEFILE =>
            WRITEPATH . 'copain_cookie.txt',

            CURLOPT_TIMEOUT => 0,

        ]);

        curl_exec($curl);

        curl_close($curl);

        fclose($fp);
    }
    public function cleanup()
    {
        $ref = $this->competitionId;

        if (!$ref) return;


        $zip =
            WRITEPATH .
            "imports/$ref.zip";

        $info =
            WRITEPATH .
            "imports/$ref/info.json";

        $tmp =
            WRITEPATH .
            "imports/tmp_$ref";


        /*
    delete zip
    */

        if (file_exists($zip)) {
            unlink($zip);
        }


        /*
    delete info
    */

        if (file_exists($info)) {
            unlink($info);
        }


        /*
    delete tmp folder
    */

        if (is_dir($tmp)) {

            $files =
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $tmp,
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );

            foreach ($files as $file) {

                if ($file->isDir()) {
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }

            rmdir($tmp);
        }


        log_message(
            'debug',
            'IMPORT CLEAN OK'
        );
    }

    public function extractChunk()
    {
        $ref = $this->competitionId ?? null;

        log_message('debug', 'COMPETITION = ' . json_encode($ref));

        if (!$ref) return true;

        $zipPath =
            WRITEPATH .
            'imports/' .
            $ref .
            '.zip';

        if (!file_exists($zipPath)) {

            log_message('error', 'ZIP NOT FOUND');

            return true;
        }

        /*
    -------------------
    LOAD COMPETITION
    -------------------
    */

        $db = \Config\Database::connect();

        $competition = $db->table('competitions')
            ->where('id', $ref)
            ->get()
            ->getRow();

        if (!$competition) {

            log_message('error', 'COMPETITION NOT FOUND');

            return true;
        }
        log_message('debug', 'COMPETITION = ' . json_encode($competition));
        /*
    -------------------
    STORAGE (SOURCE OF TRUTH)
    -------------------
    */

        $storage = new \App\Libraries\CompetitionStorage();

        // 🔥 crée toute la structure proprement
        $paths = $storage->ensureStructure($competition);

        $basePath  = $paths['base'];
        $photosDir = $paths['photos'];
        $thumbsDir = $paths['thumbs'];

        // autres dossiers
        $pteDir = $storage->getPtePath($competition);
        $pdfDir = $storage->getPdfPath($competition);
        $csvDir = $storage->resolvePath($competition) . 'csv/';

        // sécurité (au cas où)
        foreach ([$pteDir, $pdfDir, $csvDir] as $d) {
            if (!is_dir($d)) {
                mkdir($d, 0777, true);
            }
        }

        /*
    -------------------
    EXTRACT TO TMP
    -------------------
    */

        $tmpDir =
            WRITEPATH .
            'imports/tmp_' .
            $ref . '/';

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {

            log_message('error', 'ZIP OPEN ERROR');

            return true;
        }

        $zip->extractTo($tmpDir);

        $zip->close();

        log_message('debug', 'EXTRACT TMP OK');

        /*
    -------------------
    FIND JPG
    -------------------
    */

        $jpgs = $this->findJpg($tmpDir);

        log_message(
            'debug',
            'JPG FOUND ' . count($jpgs)
        );

        /*
    -------------------
    MOVE JPG → photos/
    -------------------
    */

        foreach ($jpgs as $file) {

            $name = basename($file);

            rename(
                $file,
                $photosDir . $name
            );
        }

        log_message('debug', 'MOVE OK');

        /*
    -------------------
    GENERATE THUMBS
    -------------------
    */

        try {

            $tool =
                new \App\Controllers\Tools\GenererVignettes();

            $tool->index($ref);

            log_message('debug', 'THUMBS OK');
        } catch (\Throwable $e) {

            log_message(
                'error',
                'THUMBS ERROR ' . $e->getMessage()
            );
        }

        return true;
    }

    private function getCompetitionFolder($id)
    {
        $db = \Config\Database::connect();

        $c = $db->table('competitions')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$c) {
            return $id;
        }

        $storage = new \App\Libraries\CompetitionStorage();

        return $storage->getCode($c);
    }

    private function findJpg($dir)
    {
        $result = [];

        $files = scandir($dir);

        foreach ($files as $f) {

            if ($f == '.' || $f == '..')
                continue;

            $path = $dir . '/' . $f;

            if (is_dir($path)) {

                $result = array_merge(
                    $result,
                    $this->findJpg($path)
                );
            } else {

                if (preg_match('/\.jpg$/i', $f)) {
                    $result[] = $path;
                }
            }
        }

        return $result;
    }

    public function extractZipOnly()
    {
        $ref = $this->competitionId;

        $zipPath =
            WRITEPATH .
            'imports/' .
            $ref .
            '.zip';

        $tmpDir =
            WRITEPATH .
            'imports/tmp_' .
            $ref . '/';

        if (!is_dir($tmpDir))
            mkdir($tmpDir, 0777, true);

        if (file_exists($tmpDir . 'done.txt'))
            return true;

        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true)
            return true;

        $zip->extractTo($tmpDir);

        $zip->close();

        file_put_contents(
            $tmpDir . 'done.txt',
            '1'
        );

        return true;
    }
    public function moveAllImages($tempPath, $photosPath)
    {
        $count = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempPath)
        );

        foreach ($iterator as $file) {

            if ($file->isDir()) continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;

            $dest = rtrim($photosPath, '/') . '/' . basename($file);

            if (!file_exists($dest)) {
                rename($file->getPathname(), $dest);
                $count++;
            }
        }

        log_message('debug', '[MOVE] moved files = ' . $count);

        return $count > 0;
    }
    public function moveChunk()
    {
        $ref = $this->competitionId;

        $tmpDir =
            WRITEPATH .
            'imports/tmp_' .
            $ref . '/';

        $folder =
            $this->getCompetitionFolder($ref);

        $photosDir =
            FCPATH .
            'uploads/competitions/' .
            $folder .
            '/photos/';

        if (!is_dir($photosDir))
            mkdir($photosDir, 0777, true);

        $files =
            $this->findJpg($tmpDir);

        $batch = 50;

        $moved = 0;

        foreach ($files as $f) {

            $name = basename($f);

            if (file_exists(
                $photosDir . $name
            ))
                continue;

            rename(
                $f,
                $photosDir . $name
            );

            $moved++;

            if ($moved >= $batch)
                return false;
        }

        return true;
    }

    public function thumbChunk()
    {
        $ref = $this->competitionId;

        $tool =
            new \App\Controllers\Tools\GenererVignettes();

        ob_start();

        $tool->index($ref);

        ob_end_clean();

        return true;
    }
}
