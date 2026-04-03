<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Copain extends BaseConfig
{
    public $url_check_user =
    "https://copain.federation-photo.fr/webroot/coloc/get_concours.php";

    public $url_import_compet =
    "https://copain.federation-photo.fr/webroot/coloc/import_concours.php";

    public $url_generate_zip =
    "https://copain.federation-photo.fr/webroot/coloc/generate_zip.php";

    public $url_generate_zip_quadrimage =
    "https://copain.federation-photo.fr/webroot/coloc/generate_zip_quadrimage.php";

    public $url_json =
    "https://copain.federation-photo.fr/webroot/json/";

    public string $url_liste_competitions =
    'https://copain.federation-photo.fr/api/getCompetitions.php';

    /*
    ======================
    LOGIN AUTO (.env)
    ======================
    */

    public $email;
    public $password;
    public $profiluser;
    public $uruser;


    public function __construct()
    {
        parent::__construct();

        $this->email =
            env('copain.email');
        $this->password =
            env('copain.password');
        $this->profiluser =
            env('profil.user');
        $this->uruser =
            env('ur.user');
    }
}
