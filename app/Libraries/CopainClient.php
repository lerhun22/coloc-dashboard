<?php
/*253*/

namespace App\Libraries;

class CopainClient
{
    private string $cookie;

    private string $url_check_user;
    private string $url_import;
    private string $url_generate_zip;
    private string $url_liste;


    public function __construct()
    {
        $config = config('Copain');

        $this->url_check_user   = $config->url_check_user;
        $this->url_import       = $config->url_import_compet;
        $this->url_generate_zip = $config->url_generate_zip;
        $this->url_liste        = $config->url_liste_competitions;

        $this->cookie =
            WRITEPATH .
            'copain_cookie.txt';

        log_message('debug', 'COOKIE FILE = ' . $this->cookie);

        if (!file_exists($this->cookie)) {
            file_put_contents(
                $this->cookie,
                ''
            );
        }
    }


    /*
    ===================================
    LOGIN
    ===================================
    */

    public function login($email, $password)
    {
        $params = [

            'pass'  => trim($password),
            'date'  => $email,
            'time'  => $password,
            'login' => uniqid(),

        ];

        return $this->curlPost(
            $this->url_check_user,
            $params
        );
    }


    /*
    ===================================
    IMPORT COMPETITION
    ===================================
    */

    public function importCompetition(
        $ref,
        $type,
        $ordre
    ) {
        $params = [

            'ref'   => $ref,
            'type'  => $type,
            'ordre' => $ordre,

        ];

        return $this->curlPost(
            $this->url_import,
            $params
        );
    }

    public function generateZipAsync($ref, $type)
    {
        $url = $this->url_generate_zip;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query([
                'ref' => $ref,
                'type' => $type
            ]),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 1,
        ]);

        curl_exec($ch);
        curl_close($ch);

        return true;
    }

    /*
    ===================================
    GENERATE ZIP
    ===================================
    */

    public function generateZip(
        $ref,
        $type
    ) {
        $params = [

            'ref'  => $ref,
            'type' => $type,

        ];

        return $this->curlPost(
            $this->url_generate_zip,
            $params
        );
    }


    /*
    ===================================
    DOWNLOAD FILE (ZIP)
    stable gros fichiers
    ===================================
    */

    public function downloadFile($url, $dest)
    {
        set_time_limit(0);

        log_message('debug', 'DOWNLOAD URL = ' . $url);

        $dir = dirname($dest);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_exists($dest)) {
            unlink($dest);
        }

        $fp = fopen($dest, 'wb');

        if (!$fp) {
            log_message('error', 'FOPEN FAIL ' . $dest);
            return false;
        }

        $ch = curl_init($url);

        // 🔥 seuil de log = 50 MB
        $logStep = 50 * 1024 * 1024;

        // 🔥 prochain seuil à atteindre
        $nextLog = $logStep;

        curl_setopt_array($ch, [

            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_COOKIEJAR  => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_TIMEOUT => 0,

            CURLOPT_USERAGENT => "Mozilla/5.0",
            CURLOPT_REFERER => "https://copain.federation-photo.fr/",

            CURLOPT_HTTPHEADER => [
                "Accept: */*",
                "Connection: keep-alive",
                "Origin: https://copain.federation-photo.fr"
            ],

            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => function (
                $resource,
                $download_size,
                $downloaded,
                $upload_size,
                $uploaded
            ) use (&$nextLog, $logStep) {

                // 👉 log uniquement tous les 50 MB
                if ($downloaded < $nextLog) {
                    return;
                }

                // 👉 avancer le prochain seuil
                $nextLog += $logStep;

                if ($download_size > 0) {

                    $percent = round(($downloaded / $download_size) * 100, 1);

                    log_message(
                        'debug',
                        "[DOWNLOAD] {$percent}% (" .
                            round($downloaded / 1024 / 1024, 1) . " MB / " .
                            round($download_size / 1024 / 1024, 1) . " MB)"
                    );
                } else {

                    log_message(
                        'debug',
                        "[DOWNLOAD] " . round($downloaded / 1024 / 1024, 1) . " MB"
                    );
                }
            },
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            log_message('error', 'CURL ERROR: ' . curl_error($ch));
        }

        curl_close($ch);
        fclose($fp);

        return $result !== false;
    }

    /*
    ===================================
    REMOTE SIZE
    ===================================
    */

    public function getRemoteFileSize(
        $url
    ) {
        $curl = curl_init($url);

        curl_setopt_array($curl, [

            CURLOPT_NOBODY => true,

            CURLOPT_COOKIEJAR =>
            $this->cookie,

            CURLOPT_COOKIEFILE =>
            $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_RETURNTRANSFER => true,

        ]);

        curl_exec($curl);

        $size =
            curl_getinfo(
                $curl,
                CURLINFO_CONTENT_LENGTH_DOWNLOAD
            );

        curl_close($curl);

        return $size;
    }


    /*
    ===================================
    LISTE COMPETITIONS
    ===================================
    */

    public function getCompetitions()
    {
        $config = config('Copain');

        $url =
            $config->url_json .
            'concours.json';

        $json =
            @file_get_contents($url);

        if (!$json) {

            log_message(
                'error',
                'JSON LIST ERROR'
            );

            return [
                'competitions' => [],
                'rcompetitions' => []
            ];
        }

        $data =
            json_decode(
                $json,
                true
            );

        if (!$data) {

            return [
                'competitions' => [],
                'rcompetitions' => []
            ];
        }

        return $data;
    }

    /*
    ===================================
    CURL POST GENERIC
    ===================================
    */

    private function curlPost($url, $params)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [

                CURLOPT_URL => $url,

                CURLOPT_POST => true,

                CURLOPT_POSTFIELDS =>
                http_build_query($params),

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_COOKIEJAR =>
                $this->cookie,

                CURLOPT_COOKIEFILE =>
                $this->cookie,

                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,

                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 300,

                CURLOPT_FOLLOWLOCATION => true,

                CURLOPT_USERAGENT =>
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",

                CURLOPT_REFERER =>
                "https://copain.federation-photo.fr/",

                CURLOPT_HTTPHEADER => [

                    "Accept: */*",
                    "Connection: keep-alive",
                    "Origin: https://copain.federation-photo.fr"

                ],

            ]
        );

        $response = curl_exec($curl);

        if (curl_errno($curl)) {

            log_message(
                'error',
                'CURL ERROR: ' . curl_error($curl)
            );
        }

        curl_close($curl);

        return json_decode($response, true);
    }

    /*
======================
AUTO LOGIN
======================
*/

    public function autoLogin()
    {
        $config = config('Copain');

        if (
            empty($config->email)
            || empty($config->password)
        ) {
            throw new \Exception(
                "Copain email/password manquant"
            );
        }

        return $this->login(
            $config->email,
            $config->password
        );
    }

    public function debugListe()
    {
        $url = $this->url_liste;

        $curl = curl_init($url);

        curl_setopt_array($curl, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_COOKIEJAR  => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $res = curl_exec($curl);

        curl_close($curl);

        echo $res;
        exit;
    }

    public function waitForZip($ref, $timeout = 300)
    {
        $url = "https://copain.federation-photo.fr/webroot/json/zip_photos_{$ref}.zip";

        $start = time();

        while ((time() - $start) < $timeout) {

            $headers = @get_headers($url);

            log_message('debug', '[WAIT ZIP] headers=' . ($headers[0] ?? 'NONE'));

            if ($headers && isset($headers[0]) && str_contains($headers[0], '200')) {

                log_message('debug', '[WAIT ZIP] ZIP DISPONIBLE');

                return $url; // 🔥 STOP ici
            }

            sleep(5);
        }

        return false;
    }

    public function waitForZipOLD($ref, $timeout = 300)
    {
        $url = "https://copain.federation-photo.fr/webroot/json/zip_photos_{$ref}.zip";

        $start = time();
        $lastSize = 0;
        $stableCount = 0;

        while ((time() - $start) < $timeout) {

            $headers = @get_headers($url);

            log_message('debug', '[WAIT ZIP] headers=' . ($headers[0] ?? 'NONE'));

            if ($headers && isset($headers[0]) && str_contains($headers[0], '200')) {

                // 🔥 taille réelle
                $size = $this->getRemoteFileSize($url);

                log_message('debug', '[WAIT ZIP] size=' . $size);

                if ($size > 3000000) {

                    if ($size === $lastSize) {
                        $stableCount++;
                    } else {
                        $stableCount = 0;
                    }

                    $lastSize = $size;

                    // 👉 taille stable 2 fois = ZIP terminé
                    if ($stableCount >= 2) {
                        return $url;
                    }
                }
            }

            sleep(5);
        }

        return false;
    }
}
