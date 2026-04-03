<?php

namespace App\Services;

use ZipArchive;

class CopainLegacyReaderService
{
    public function read(string $zipPath): array
    {
        if (!file_exists($zipPath)) {
            throw new \Exception('ZIP not found');
        }

        $name = basename($zipPath, '.zip');

        // 🔥 extraction ID depuis nom
        $parts = explode('_', $name);
        $id = (int) end($parts);

        $tempDir = WRITEPATH . 'tmp/' . uniqid() . '/';

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // =========================
        // UNZIP
        // =========================

        $zip = new ZipArchive();

        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($tempDir);
            $zip->close();
        } else {
            throw new \Exception('Erreur ouverture ZIP');
        }

        // =========================
        // SCAN IMAGES
        // =========================

        $images = $this->scanImages($tempDir);

        // =========================
        // FORMAT UNIFIÉ (IMPORTANT)
        // =========================

        return [
            'id' => $id,
            'nom' => $name,
            'saison' => date('Y'), // fallback
            'urs_id' => null,      // ZIP = national par défaut

            'images' => array_map(function ($path) {
                return [
                    'path' => $path,
                    'filename' => basename($path),
                ];
            }, $images),
        ];
    }

    private function scanImages(string $dir): array
    {
        $result = [];

        $items = scandir($dir);

        foreach ($items as $item) {

            if (
                $item == '.'
                || $item == '..'
                || $item[0] == '.'
                || str_starts_with($item, '._')
                || $item == 'Thumbs.db'
                || $item == 'desktop.ini'
            ) {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {

                $result = array_merge(
                    $result,
                    $this->scanImages($path)
                );
            } else {

                if (preg_match('/\.(jpg|jpeg|png)$/i', $item)) {
                    $result[] = $path;
                }
            }
        }

        return $result;
    }
}
