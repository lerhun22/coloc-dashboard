<?php

namespace App\Helpers;

class CompetitionMapper
{
    /**
     * ================================
     * 🎯 NIVEAU (REG / N1 / N2 / CdF / GP)
     * ================================
     */
    public static function niveau(string $nom): string
    {
        $nom = strtoupper($nom);

        if (str_contains($nom, 'COUPE DE FRANCE')) return 'CdF';
        if (str_contains($nom, 'NATIONAL 1')) return 'N1';
        if (str_contains($nom, 'NATIONAL 2')) return 'N2';
        if (str_contains($nom, 'NATIONAL2')) return 'N2';
        if (str_contains($nom, 'NATIONAL1')) return 'N1';
        if (str_contains($nom, 'GRAND PRIX')) return 'GRAND PRIX';

        return '';
    }

    /**
     * ================================
     * 📦 SUPPORT (PAP / IP)
     * ================================
     */
    public static function support(string $nom): string
    {
        $nom = strtoupper($nom);

        if (str_contains($nom, 'PAPIER')) return 'PAPIER';
        if (str_contains($nom, 'IMAGE PROJETEE')) return 'IMAGE PROJETEE';
        if (str_contains($nom, 'IMAGE')) return 'IMAGE PROJETEE';

        return ''; // volontairement vide si non pertinent
    }

    /**
     * ================================
     * 🧩 CATEGORIE (MONO / COULEUR / ...)
     * ================================
     */
    public static function categorie(string $nom): string
    {
        $nom = strtoupper($nom);

        if (str_contains($nom, 'MONOCHROME')) return 'MONO';
        if (str_contains($nom, 'COULEUR')) return 'COULEUR';
        if (str_contains($nom, 'NATURE')) return 'NATURE';
        if (str_contains($nom, 'AUTEUR')) return 'AUTEUR';
        if (str_contains($nom, 'QUADRIMAGE')) return 'QUADRIMAGE';
        if (str_contains($nom, 'AUDIOVISUEL')) return 'AUDIOVISUEL';
        if (str_contains($nom, 'REPORTAGE')) return 'REPORTAGE';

        return $nom;
    }

    /**
     * ================================
     * 🏷 LABEL FINAL (ligne 1)
     * ================================
     */
    public static function label(array $competition): string
    {
        $nom = $competition['nom'] ?? '';

        return trim(implode(' ', array_filter([
            self::niveau($nom),
            self::support($nom),
            self::categorie($nom)
        ])));
    }

    /**
     * ================================
     * 📅 STATUT (ligne 2)
     * ================================
     */
    public static function statut(array $competition): string
    {
        // ❌ pas importé
        if (empty($competition['is_imported'])) {
            return 'NON IMPORTÉE';
        }

        $raw = $competition['date_competition'] ?? null;

        if (!$raw || $raw === '0000-00-00') {
            return 'À VENIR';
        }

        $date = date('Y-m-d', strtotime($raw));
        $today = date('Y-m-d');

        // 🟡 futur
        if ($date > $today) {
            return 'À VENIR : ' . date('d/m/Y', strtotime($raw));
        }

        // 🟢 passé
        return 'JUGÉE ' . date('d/m/Y', strtotime($raw));
    }

    /**
     * ================================
     * 🆔 IDENTIFIANT (ligne 3)
     * ================================
     */
    public static function reference(array $competition): string
    {
        return 'ID ' . ($competition['id'] ?? '-');
    }

    /**
     * ================================
     * 🎨 CLASSE CSS
     * ================================
     */
    public static function typeClass(array $competition): string
    {
        $niveau = self::niveau($competition['nom'] ?? '');

        return match ($niveau) {
            'CdF' => 'card-cdf',
            'N1', 'N2' => 'card-national',
            default => 'card-regional'
        };
    }
}
