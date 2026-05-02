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
    🎯 CORE : RESOLVE FOLDER (NO CREATION HERE)
    ============================================================
    */
    private function normalize($competition): array
    {
        return is_array($competition) ? $competition : (array) $competition;
    }

    public function findCompetitionFolder(array $competition): ?string
    {
        $competition = $this->normalize($competition);

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

        $ursId = $competition['urs_id'] ?? null;
        $isNational = ($ursId === null || $ursId == 0);

        $type = $isNational ? 'N' : 'R';
        $ur   = $isNational ? '00' : str_pad($ursId, 2, '0', STR_PAD_LEFT);

        $numero = $competition['numero'] ?? null;

        // ============================================================
        // 1. FORMAT COMPLET (si numero dispo)
        // ============================================================
        if ($numero !== null) {
            $numero = str_pad($numero, 4, '0', STR_PAD_LEFT);

            $path = "{$this->basePath}{$saison}_{$type}_{$id}_{$ur}_{$numero}/";

            if (is_dir($path)) {
                return $this->cache[$key] = $path;
            }

            // fallback ancien format
            $fallback = "{$this->basePath}{$saison}_{$type}_{$numero}_{$ur}_{$id}/";

            if (is_dir($fallback)) {
                log_message('debug', 'Fallback folder used: ' . $fallback);
                return $this->cache[$key] = $fallback;
            }
        }

        // ============================================================
        // 2. FORMAT SANS NUMERO (CRITIQUE)
        // ============================================================
        $pattern = "{$this->basePath}{$saison}_{$type}_{$id}_{$ur}*";

        $matches = glob($pattern);

        if (!empty($matches)) {
            // prend le premier match trouvé
            return $this->cache[$key] = rtrim($matches[0], '/') . '/';
        }

        log_message('debug', 'Folder not found: ' . $id);

        return $this->cache[$key] = null;
    }


    public function findCompetitionFolderOLD(array $competition): ?string
    {

        $competition = $this->normalize($competition);

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
        $ur   = $isNational ? '00' : str_pad($ursId, 2, '0', STR_PAD_LEFT);

        // FORMAT PRINCIPAL
        $path = "{$this->basePath}{$saison}_{$type}_{$id}_{$ur}_{$numero}/";

        if (is_dir($path)) {
            return $this->cache[$key] = $path;
        }

        // FALLBACK ANCIEN FORMAT
        $fallback = "{$this->basePath}{$saison}_{$type}_{$numero}_{$ur}_{$id}/";

        if (is_dir($fallback)) {
            log_message('debug', 'Fallback folder used: ' . $fallback);
            return $this->cache[$key] = $fallback;
        }

        // ⚠️ ne log que si nécessaire (évite spam)
        log_message('debug', 'Folder not found (will create): ' . $id);

        return $this->cache[$key] = null;
    }

    /*
    ============================================================
    🏗️ CREATE STRUCTURE (SOURCE DE VÉRITÉ)
    ============================================================
    */

    public function create(array $competition): string
    {
        $competition = $this->normalize($competition);

        $folder = $this->findCompetitionFolder($competition);

        if (!$folder) {

            $saison = $competition['saison'];
            $id     = $competition['id'];
            $numero = $competition['numero'] ?? null;

            if ($numero === null) {
                throw new \RuntimeException("Numero manquant pour création compétition {$competition['id']}");
            }

            $numero = str_pad($numero, 4, '0', STR_PAD_LEFT);

            $ursId = $competition['urs_id'] ?? null;
            $type = empty($ursId) ? 'N' : 'R';
            $ur   = empty($ursId) ? '00' : str_pad($ursId, 2, '0', STR_PAD_LEFT);

            $folder = "{$this->basePath}{$saison}_{$type}_{$id}_{$ur}_{$numero}/";

            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
        }

        // sous-dossiers standards COLOC
        $folders = ['photos', 'thumbs', 'pdf', 'pte', 'export', 'temp', 'csv'];

        foreach ($folders as $sub) {
            $dir = $folder . $sub . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return $folder;
    }

    /*
    ============================================================
    🧠 COMPATIBILITÉ LEGACY (IMPORTANT)
    ============================================================
    */

    public function ensureStructure($competition): array
    {
        $competition = $this->normalize($competition);

        $base = $this->create($competition);

        return [
            'base'   => $base,
            'photos' => $base . 'photos/',
            'thumbs' => $base . 'thumbs/',
            'pdf'    => $base . 'pdf/',
            'export' => $base . 'export/',
        ];
    }

    /*
    ============================================================
    📁 PATHS (AUTO-RÉSILIENT)
    ============================================================
    */

    public function getPhotosPath($competition): string
    {
        $competition = $this->normalize($competition);

        $folder = $this->findCompetitionFolder($competition);

        if (!$folder) {
            $folder = $this->create($competition);
        }

        return $folder . 'photos/';
    }

    public function getThumbsPath($competition): string
    {

        $competition = $this->normalize($competition);

        $folder = $this->findCompetitionFolder((array)$competition);

        if (!$folder) {
            $folder = $this->create((array)$competition);
        }

        return $folder . 'thumbs/';
    }

    public function getPdfPath($competition): string
    {
        $competition = $this->normalize($competition);

        $folder = $this->findCompetitionFolder((array)$competition);

        if (!$folder) {
            $folder = $this->create((array)$competition);
        }

        return $folder . 'pdf/';
    }

    public function getExportPath($competition): string
    {
        $competition = $this->normalize($competition);

        $folder = $this->findCompetitionFolder((array)$competition);

        if (!$folder) {
            $folder = $this->create((array)$competition);
        }

        return $folder . 'export/';
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
    🔍 CHECKS
    ============================================================
    */

    public function hasPhotosSafe($competition): bool
    {
        $path = $this->getPhotosPathIfExists($competition);

        if (!$path) return false;

        foreach (scandir($path) as $f) {
            if (preg_match('/\.(jpg|jpeg)$/i', $f)) {
                return true;
            }
        }

        return false;
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

    public function getPhotosPathIfExists($competition): ?string
    {
        $competition = $this->normalize($competition);

        $folder = $this->findCompetitionFolder($competition);

        if (!$folder) {
            return null;
        }

        // 🔥 support COPAINS
        if (is_dir($folder . 'photos/')) {
            return $folder . 'photos/';
        }

        if (is_dir($folder . 'Images/')) {
            return $folder . 'Images/';
        }

        return null;
    }
}
