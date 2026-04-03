<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $configPath = WRITEPATH . 'config/application.ini';

        $app_config = file_exists($configPath)
            ? parse_ini_file($configPath)
            : [];

        $data = array_merge($this->data, [
            'current_version'      => $app_config['version-no'] ?? '',
            'current_version_date' => $app_config['version-update'] ?? '',
            'official_build'       => $app_config['official-build'] ?? '0',
            'local_build_date'     => $app_config['local-build-date'] ?? '',
            'environment'          => strtoupper($app_config['environment'] ?? ENVIRONMENT),
            'build_number'         => $app_config['build-number'] ?? '',
            'author_email'         => $app_config['local-author-email'] ?? '',
            'origin'               => $app_config['local-origin'] ?? '',
        ]);

        return view('dashboard/index', $data);
    }
}
