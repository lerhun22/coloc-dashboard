<?php

namespace App\Libraries;

use App\Models\CompetitionModel;

class CompetitionStorage
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = rtrim(FCPATH, '/') . '/uploads/competitions/';
    }

    /*
    =========================
    NORMALIZATION
    =========================
    */

    private function normalize($competition): object
    {
        return is_array($competition)
            ? (object) $competition
            : $competition;
    }

    /*
    =========================
    TYPE LOGIC
    =========================
    */

    public function isNational($competition): bool
    {
        $competition = $this->normalize($competition);

        return empty($competition->urs_id)
            || $competition->type === 'N'
            || $competition->type === 2;
    }

    /*
    =========================
    CORE PATH LOGIC
    =========================
    */

    public function getCode($competition): string
    {
        $competition = $this->normalize($competition);

        $saison = $competition->saison ?? '0000';
        $id     = $competition->id ?? 0;
        $numero = str_pad($competition->numero ?? 0, 4, '0', STR_PAD_LEFT);

        if ($this->isNational($competition)) {
            return "{$saison}_N_{$id}_00_{$numero}";
        }

        $ur = str_pad((string)($competition->urs_id ?? 0), 2, '0', STR_PAD_LEFT);

        return "{$saison}_R_{$id}_{$ur}_{$numero}";
    }

    public function getBasePath($competition): string
    {
        $competition = $this->normalize($competition);

        $code = $this->getCode($competition);

        return rtrim($this->basePath, '/') . '/' . $code . '/';
    }

    /*
    =========================
    RESOLVE (LEGACY SAFE)
    =========================
    */

    public function resolvePath($competition): string
    {
        $competition = $this->normalize($competition);

        if (!empty($competition->folder)) {

            $folder = rtrim($competition->folder, '/') . '/';

            if (is_dir($folder)) {
                return $folder;
            }
        }

        return $this->getBasePath($competition);
    }

    /*
    =========================
    SUB PATHS
    =========================
    */

    public function getPhotosPath($competition): string
    {
        return $this->resolvePath($competition) . 'photos/';
    }

    public function getThumbsPath($competition): string
    {
        return $this->resolvePath($competition) . 'thumbs/';
    }

    public function getPdfPath($competition): string
    {
        return $this->resolvePath($competition) . 'pdf/';
    }

    public function getPtePath($competition): string
    {
        return $this->resolvePath($competition) . 'pte/';
    }

    public function getExportPath($competition): string
    {
        return $this->resolvePath($competition) . 'export/';
    }

    public function getTempPath($competition): string
    {
        return $this->resolvePath($competition) . 'temp/';
    }

    /*
    =========================
    CREATE STRUCTURE (FIXED)
    =========================
    */

    public function create($competition): string
    {
        $competition = $this->normalize($competition);

        // 🔥 IMPORTANT : utiliser resolvePath (corrige le bug)
        $base = $this->resolvePath($competition);

        $folders = [
            '',
            'photos',
            'thumbs',
            'pdf',
            'pte',
            'export',
            'temp',
            'csv'
        ];

        foreach ($folders as $folder) {

            $dir = rtrim($base, '/') . '/' . $folder;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return $base;
    }

    /*
    =========================
    ENSURE STRUCTURE
    =========================
    */

    public function ensureStructure($competition): array
    {
        $this->create($competition);

        return [
            'base'   => $this->resolvePath($competition),
            'photos' => $this->getPhotosPath($competition),
            'thumbs' => $this->getThumbsPath($competition),
        ];
    }

    /*
    =========================
    UTIL FILESYSTEM
    =========================
    */

    public function hasPhotos($competition): bool
    {
        $path = $this->getPhotosPath($competition);

        if (!is_dir($path)) return false;

        foreach (scandir($path) as $file) {
            if ($file !== '.' && $file !== '..') {
                return true;
            }
        }

        return false;
    }

    public function hasThumbs($competition): bool
    {
        return is_dir($this->getThumbsPath($competition));
    }

    public function isJudged($competition): bool
    {
        return file_exists(
            $this->resolvePath($competition) . 'csv/jugement.csv'
        );
    }

    /*
    =========================
    DB REGISTER
    =========================
    */

    public function registerCompetition(array $compet)
    {
        $model = new CompetitionModel();

        $existing = $model
            ->where('id', $compet['id'])
            ->first();

        if ($existing) {
            return $existing['id'];
        }

        $data = [

            'id'     => $compet['id'],
            'numero' => $compet['numero'],
            'urs_id' => $compet['urs_id'] ?? null,

            'saison' => $compet['saison'],
            'type'   => $compet['type'] ?? 0,

            'nom' => $compet['nom'],

            'date_competition' => $compet['date_competition'] ?? date('Y-m-d'),

            'max_photos_club' => $compet['max_photos_club'] ?? 999,
            'max_photos_auteur' => $compet['max_photos_auteur'] ?? 99,

            'param_photos_club' => $compet['param_photos_club'] ?? 0,
            'param_photos_auteur' => $compet['param_photos_auteur'] ?? 0,

            'quota' => $compet['quota'] ?? 0,

            'note_min' => $compet['note_min'] ?? 6,
            'note_max' => $compet['note_max'] ?? 20,

            'nb_auteurs_ur_n2' => $compet['nb_auteurs_ur_n2'] ?? 3,
            'nb_clubs_ur_n2' => $compet['nb_clubs_ur_n2'] ?? 7,

            'pte' => 0,
            'nature' => $compet['nature'] ?? 0
        ];

        $model->insert($data);

        return $compet['id'];
    }
}