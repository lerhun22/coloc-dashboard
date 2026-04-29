<?php

namespace App\Libraries;

use Config\Services;

class ThumbnailService
{
    /*
    ============================================================
    🎯 GÉNÉRATION THUMB DEPUIS UNE PHOTO EXISTANTE
    ============================================================
    */

    public function getThumbUrlFromPath(
        string $photoFullPath,
        array $competition,
        string $ean // 🔥 IMPORTANT : on passe EAN et non ID
    ): string {

        $storage = new CompetitionStorage();

        /*
        ============================================================
        📁 DOSSIER COMPÉTITION (SOURCE UNIQUE)
        ============================================================
        */
        $folder = $storage->findCompetitionFolder($competition);

        if (!$folder) {
            log_message('error', 'THUMB: folder introuvable');
            return base_url('assets/img/no-image.jpg');
        }

        /*
        ============================================================
        📁 CHEMINS
        ============================================================
        */
        $thumbDir  = $folder . 'thumbs/';
        $thumbFile = $thumbDir . $ean . '.jpg';

        // URL publique
        $relative = str_replace(FCPATH, '', $thumbFile);
        $thumbUrl = base_url($relative);

        /*
        ============================================================
        ✅ THUMB EXISTE
        ============================================================
        */
        if (file_exists($thumbFile)) {
            return $thumbUrl;
        }

        /*
        ============================================================
        🔧 CRÉATION DOSSIER
        ============================================================
        */
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0775, true);
        }

        /*
        ============================================================
        🔥 GÉNÉRATION
        ============================================================
        */
        try {

            Services::image()
                ->withFile($photoFullPath)
                ->fit(300, 300, 'center')
                ->save($thumbFile);

            log_message('debug', 'THUMB GENERATED: ' . $thumbFile);
        } catch (\Throwable $e) {

            log_message('error', 'THUMB ERROR: ' . $e->getMessage());

            return base_url('assets/img/no-image.jpg');
        }

        return $thumbUrl;
    }

    /*
    ============================================================
    🧪 DEBUG
    ============================================================
    */

    public function debugPaths(array $competition, string $ean): array
    {
        $storage = new CompetitionStorage();

        $folder = $storage->findCompetitionFolder($competition);

        return [
            'folder'    => $folder,
            'thumbPath' => $folder ? $folder . 'thumbs/' . $ean . '.jpg' : null,
        ];
    }
}
