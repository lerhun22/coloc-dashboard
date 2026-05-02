<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ============================================================
 * ExportDashboardExcelService
 * ============================================================
 * Date       : 2026-05-01
 * Auteur     : UR22 Dashboard
 * Objectif   : Export structuré du dashboard en Excel multi-onglets
 *
 * CONTENU :
 * - NATIONAL
 * - UR (classement)
 * - UR22
 * - Clubs UR22 (1 onglet / club)
 * - Auteurs UR22
 *
 * RISQUES :
 * - Trop d’onglets si beaucoup de clubs (>100)
 * - Données non tabulaires → erreurs Excel
 * ============================================================
 */

class ExportDashboardExcelService
{
    public function generate(array $data): string
    {
        $spreadsheet = new Spreadsheet();

        // Supprime feuille vide par défaut
        $spreadsheet->removeSheetByIndex(0);

        /*
        ============================================================
        1. NATIONAL
        ============================================================
        */
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('NATIONAL');

        $sheet->fromArray([
            ['Metric', 'Value'],
            ['Nb Clubs', $data['globalFPF']['nb_clubs'] ?? 0],
            ['Nb Points', $data['globalFPF']['nb_points'] ?? 0],
            ['Nb Images', $data['globalFPF']['nb_images'] ?? 0],
            ['Nb Auteurs', $data['globalFPF']['nb_authors'] ?? 0],
        ]);

        /*
        ============================================================
        2. UR CLASSEMENT
        ============================================================
        */
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('UR Classement');

        $rows = $data['urRanking'] ?? [];

        if (!empty($rows)) {
            $sheet->fromArray(array_keys($rows[0]), null, 'A1');
            $sheet->fromArray($rows, null, 'A2');
        }

        /*
        ============================================================
        3. UR22
        ============================================================
        */
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('UR22');

        $sheet->fromArray([
            ['Metric', 'Value'],
            ['Nb Clubs', $data['globalUR']['nb_clubs'] ?? 0],
            ['Nb Points', $data['globalUR']['nb_points'] ?? 0],
            ['Nb Images', $data['globalUR']['nb_images'] ?? 0],
            ['Nb Auteurs', $data['globalUR']['nb_authors'] ?? 0],
        ]);

        /*
        ============================================================
        4. CLUBS UR22 (1 onglet / club)
        ============================================================
        */
        foreach ($data['urClubs'] ?? [] as $club) {

            $sheet = $spreadsheet->createSheet();

            $title = $this->sanitizeSheetTitle($club['nom'] ?? 'Club');
            $sheet->setTitle($title);

            $sheet->fromArray([
                ['Club', $club['nom']],
                ['Numero', $club['numero']],
                ['Points', $club['points']],
                ['Images', $club['total_images']],
                ['Auteurs', $club['authors']],
                ['Conversion', $club['conversion']],
                ['Profil', $club['profile']],
                ['Global Index', $club['global_index']],
            ]);

            /*
            ---------------------------
            TOP AUTEURS DU CLUB
            ---------------------------
            */
            $authors = $this->extractClubAuthors($club['numero']);

            if (!empty($authors)) {
                $sheet->fromArray(
                    [['Auteur', 'Points', 'Images']],
                    null,
                    'A10'
                );

                $sheet->fromArray(
                    $authors,
                    null,
                    'A11'
                );
            }
        }

        /*
        ============================================================
        5. AUTEURS UR22
        ============================================================
        */
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Auteurs UR22');

        $authors = $this->buildAuthorsFromClubs($data['urClubs'] ?? []);

        if (!empty($authors)) {
            $sheet->fromArray(array_keys($authors[0]), null, 'A1');
            $sheet->fromArray($authors, null, 'A2');
        }

        /*
        ============================================================
        EXPORT
        ============================================================
        */
        $path = WRITEPATH . 'exports/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $file = $path . 'dashboard_UR22_' . date('Ymd_His') . '.xlsx';

        (new Xlsx($spreadsheet))->save($file);

        return $file;
    }

    /*
    ============================================================
    HELPERS
    ============================================================
    */

    private function sanitizeSheetTitle(string $title): string
    {
        $title = preg_replace('/[\\\/*?:\[\]]/', '', $title);
        return substr($title, 0, 31);
    }

    private function extractClubAuthors(string $clubNumero): array
    {
        $db = \Config\Database::connect();

        return $db->query("
            SELECT 
                CONCAT(pa.prenom, ' ', pa.nom) as Auteur,
                SUM(p.note_totale) as Points,
                COUNT(*) as Images
            FROM photos p
            JOIN participants pa ON pa.id = p.participants_id
            JOIN clubs cl ON cl.id = pa.clubs_id
            WHERE cl.numero = ?
            GROUP BY pa.id
            ORDER BY Points DESC
            LIMIT 10
        ", [$clubNumero])->getResultArray();
    }

    private function buildAuthorsFromClubs(array $clubs): array
    {
        $authors = [];

        foreach ($clubs as $c) {

            if (empty($c['nom'])) continue;

            $authors[] = [
                'club' => $c['nom'],
                'points' => $c['points'],
                'images' => $c['total_images'],
                'auteurs' => $c['authors']
            ];
        }

        usort($authors, fn($a, $b) => $b['points'] <=> $a['points']);

        return $authors;
    }
}
