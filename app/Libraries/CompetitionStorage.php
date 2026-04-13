<?php

namespace App\Libraries;

use App\Models\CompetitionModel;

class CompetitionStorage
{
    protected string $basePath;
    private array $cache = [];

    public function __construct()
    {
        $this->basePath = rtrim(FCPATH, '/') . '/uploads/competitions/';
    }

    /*
    ============================================================
    🎯 CORE : UNIQUE SOURCE OF TRUTH
    ============================================================
    */

    public function findCompetitionFolder(array $competition): ?string
    {
        if (empty($competition['id'])) {
            log_message('error', 'CompetitionStorage: id manquant');
            return null;
        }

        $key = $competition['id'];

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $saison = $competition['saison'] ?? null;
        $id     = $competition['id'];
        $numero = str_pad($competition['numero'] ?? 0, 4, '0', STR_PAD_LEFT);

        $ursId = $competition['urs_id'] ?? null;

        $isNational = ($ursId === null || $ursId == 0);

        $type = $isNational ? 'N' : 'R';

        $ur = $isNational
            ? '00'
            : str_pad($ursId, 2, '0', STR_PAD_LEFT);

        /*
        ============================================================
        🧱 FORMAT PRINCIPAL (NOUVEAU)
        ============================================================
        */
        $path = "{$this->basePath}{$saison}_{$type}_{$id}_{$ur}_{$numero}/";

        if (is_dir($path)) {
            return $this->cache[$key] = $path;
        }

        /*
        ============================================================
        🔁 FALLBACK (ANCIEN FORMAT POSSIBLE)
        ============================================================
        */
        $fallback = "{$this->basePath}{$saison}_{$type}_{$numero}_{$ur}_{$id}/";

        if (is_dir($fallback)) {
            log_message('debug', 'Fallback folder used: ' . $fallback);
            return $this->cache[$key] = $fallback;
        }

        log_message('error', 'Folder NOT FOUND: ' . json_encode($competition));

        return $this->cache[$key] = null;
    }

    /*
    ============================================================
    📁 PATHS
    ============================================================
    */

    public function getPhotosPath($competition): string
    {
        $folder = $this->findCompetitionFolder((array)$competition);
        return $folder ? $folder . 'photos/' : '';
    }

    public function getThumbsPath($competition): string
    {
        $folder = $this->findCompetitionFolder((array)$competition);
        return $folder ? $folder . 'thumbs/' : '';
    }

    public function getPdfPath($competition): string
    {
        $folder = $this->findCompetitionFolder((array)$competition);
        return $folder ? $folder . 'pdf/' : '';
    }

    public function getExportPath($competition): string
    {
        $folder = $this->findCompetitionFolder((array)$competition);
        return $folder ? $folder . 'export/' : '';
    }

    /*
    ============================================================
    🌐 URLS
    ============================================================
    */

    public function getPhotosUrl($competition): string
    {
        return $this->toUrl($this->getPhotosPath($competition));
    }

    public function getThumbsUrl($competition): string
    {
        return $this->toUrl($this->getThumbsPath($competition));
    }

    private function toUrl(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        return str_replace(
            rtrim(FCPATH, '/') . '/',
            base_url() . '/',
            $path
        );
    }

    /*
    ============================================================
    🏗️ CREATE STRUCTURE
    ============================================================
    */

    public function create($competition): string
    {
        $folder = $this->findCompetitionFolder((array)$competition);

        // 🔥 si pas trouvé → on crée le dossier principal
        if (!$folder) {

            $saison = $competition['saison'];
            $id     = $competition['id'];
            $numero = str_pad($competition['numero'], 4, '0', STR_PAD_LEFT);

            $ursId = $competition['urs_id'] ?? null;
            $type = empty($ursId) ? 'N' : 'R';
            $ur   = empty($ursId) ? '00' : str_pad($ursId, 2, '0', STR_PAD_LEFT);

            $folder = "{$this->basePath}{$saison}_{$type}_{$id}_{$ur}_{$numero}/";

            mkdir($folder, 0777, true);
        }

        $folders = ['photos', 'thumbs', 'pdf', 'pte', 'export', 'temp', 'csv'];

        foreach ($folders as $sub) {

            $dir = $folder . $sub;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return $folder;
    }

    /*
    ============================================================
    🔍 CHECKS
    ============================================================
    */

    public function hasPhotos($competition): bool
    {
        $path = $this->getPhotosPath($competition);

        if (!is_dir($path)) return false;

        $files = array_diff(scandir($path), ['.', '..']);
        return !empty($files);
    }

    public function hasThumbs($competition): bool
    {
        $path = $this->getThumbsPath($competition);
        return is_dir($path) && count(scandir($path)) > 2;
    }

    public function isJudged($competition): bool
    {
        $folder = $this->findCompetitionFolder((array)$competition);

        return $folder && file_exists($folder . 'csv/jugement.csv');
    }

    /*
    ============================================================
    🗃️ DB REGISTER
    ============================================================
    */

    private function resolveType(array $compet): int
    {
        if (isset($compet['type']) && in_array((int)$compet['type'], [1, 2, 3])) {
            return (int)$compet['type'];
        }

        if (!empty($compet['nom']) && stripos($compet['nom'], 'nat') !== false) {
            return 2;
        }

        return 1;
    }

    public function registerCompetition(array $compet)
    {
        $model = new CompetitionModel();

        $existing = $model->where('id', $compet['id'])->first();

        if ($existing) return $existing['id'];

        $data = [
            'id'     => $compet['id'],
            'numero' => $compet['numero'],
            'urs_id' => $compet['urs_id'] ?? null,
            'saison' => $compet['saison'],
            'nom'    => $compet['nom'],
            'type'   => $this->resolveType($compet),
        ];

        $model->insert($data);

        return $compet['id'];
    }
}
