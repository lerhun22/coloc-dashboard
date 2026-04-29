<?php

namespace App\Libraries;

class CopainLegacyReader
{
    private string $cookie;

    public function __construct()
    {
        $this->cookie = WRITEPATH . 'copain_cookie.txt';
    }

    public function getCompetitions($email, $password)
    {
        $config = config('Copain');

        $params = [
            'pass'  => trim($password),
            'date'  => $email,
            'time'  => $password,
            'login' => uniqid()
        ];

        $url = $config->url_check_user;

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => http_build_query($params),

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_COOKIEJAR => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 100,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_USERAGENT =>
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",

            CURLOPT_REFERER =>
            "https://copain.federation-photo.fr/",

        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response, true);
        //dd($data['competitions'][0]);
        //dd($data['competitions']);
        //dd($data['rcompetitions']);
        //print_r($data['competitions'][0]);
        //print_r($data['rcompetitions'][0]);
        //exit;

        return json_decode($response, true);
    }
}
