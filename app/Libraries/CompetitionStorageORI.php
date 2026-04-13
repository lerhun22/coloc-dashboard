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

    public function findCompetitionFolder(array $competition): ?string
    {
        $saison = $competition['saison'];

        // 🔥 FIX : PAS DE PADDING ID
        $id = $competition['id'];

        // ✔ padding uniquement ici
        $numero = str_pad($competition['numero'], 4, '0', STR_PAD_LEFT);

        $base = rtrim($this->basePath, '/') . '/';

        $ursId = $competition['urs_id'] ?? null;

        $isNational = ($ursId === null || $ursId == 0);

        $type = $isNational ? 'N' : 'R';

        $ur = $isNational
            ? '00'
            : str_pad($ursId, 2, '0', STR_PAD_LEFT);

        $path = $base . "{$saison}_{$type}_{$id}_{$ur}_{$numero}/";

        log_message('debug', 'PATH: ' . $path);

        if (is_dir($path)) {
            return $path;
        }

        log_message('error', 'Folder NOT FOUND: ' . json_encode($competition));

        return null;
    }


    public function getPhotosPath($competition): string
    {
        $folder = $this->findCompetitionFolder($competition);

        if (!$folder) {
            return '';
        }

        return $folder . 'photos/';
    }

    public function getPhotosUrl($competition): string
    {
        return 'uploads/competitions/' . $this->getCode($competition) . '/photos/';
    }

    public function getThumbsUrl($competition): string
    {
        return 'uploads/competitions/' . $this->getCode($competition) . '/thumbs/';
    }

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

        // 🔥 priorité 1 : type explicite
        if (isset($competition->type)) {
            if (in_array((int)$competition->type, [2, 3])) {
                return true;
            }
        }

        // 🔥 fallback intelligent (TRÈS IMPORTANT pour ton import)
        if (!empty($competition->nom)) {
            if (stripos($competition->nom, 'nat') !== false) {
                return true;
            }
        }

        return false;
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

        // ✅ ID brut (IMPORTANT)
        $id = $competition->id ?? 0;

        // ✅ numero pad sur 4 (OK)
        $numero = str_pad($competition->numero ?? 0, 4, '0', STR_PAD_LEFT);

        // règle métier
        $type = empty($competition->urs_id) ? 'N' : 'R';

        return "{$saison}_{$type}_{$id}_00_{$numero}";
    }

    public function getBaseUrl($competition): string
    {
        return base_url('uploads/competitions/' . $this->getCode($competition) . '/');
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
        $saison = $competition['saison'] ?? '0000';
        $id     = str_pad($competition['id'] ?? 0, 4, '0', STR_PAD_LEFT);
        $numero = str_pad($competition['numero'] ?? 0, 4, '0', STR_PAD_LEFT);

        $type = $this->getType($competition);

        return "{$this->basePath}{$saison}_{$type}_{$id}_00_{$numero}/";
    }

    /*
    =========================
    SUB PATHS
    =========================
    */

    public function getThumbsPath($competition): string
    {
        return $this->resolvePath($competition) . 'thumbs/';
    }

    private function getType(array $competition): string
    {
        /*
    ============================================================
    TYPE COMPÉTITION
    ============================================================
    - urs_id NULL → NATIONAL (N)
    - urs_id != NULL → REGIONAL (R)
    ============================================================
    */
        if (!array_key_exists('urs_id', $competition)) {
            log_message('warning', 'urs_id manquant dans competition');
            return 'N';
        }
        return (array_key_exists('urs_id', $competition) && $competition['urs_id'] !== null)
            ? 'R'
            : 'N';
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

    private function resolveType(array $compet): int
    {
        // 🔥 si déjà défini correctement
        if (isset($compet['type']) && in_array((int)$compet['type'], [1, 2, 3])) {
            return (int)$compet['type'];
        }

        // 🔥 fallback basé sur le nom
        if (!empty($compet['nom'])) {
            if (stripos($compet['nom'], 'nat') !== false) {
                return 2; // nationale
            }
        }

        return 1; // régional par défaut
    }

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

            'nom' => $compet['nom'],

            'type' => $this->resolveType($compet),

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
