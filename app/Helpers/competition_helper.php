<?php

/**
 * ============================================================
 * 📦 HELPER : Competition Helper (COLOC v2)
 * ============================================================
 *
 * 📅 Date        : 2026-04
 * 🧱 Architecture : CodeIgniter 4
 *
 * ============================================================
 * 🎯 OBJECTIFS
 * ============================================================
 *
 * ✔ Gestion chemins / fichiers (legacy sécurisé)
 * ✔ Helpers affichage dashboard
 * ✔ Compatibilité ancien système
 *
 * ⚠️ IMPORTANT
 * - Aucune logique métier FPF ici
 * - Utiliser CompetitionNormalizer pour ça
 *
 * ============================================================
 */


/**
 * ============================================================
 * 📁 DOSSIERS & FICHIERS
 * ============================================================
 */

/**
 * Code dossier compétition
 */
function competition_code($c): string
{
    $c = (array) $c;

    if (empty($c['id']) || empty($c['saison'])) {
        throw new \Exception("Competition invalide");
    }

    $saison = $c['saison'];
    $id     = $c['id'];
    $numero = str_pad((string)($c['numero'] ?? 0), 4, '0', STR_PAD_LEFT);

    if (empty($c['urs_id'])) {
        return "{$saison}_N_{$id}_00_{$numero}";
    }

    $ur = str_pad((string)$c['urs_id'], 2, '0', STR_PAD_LEFT);

    return "{$saison}_R_{$id}_{$ur}_{$numero}";
}


/**
 * Path relatif photos
 */
function competition_photos_path(array $c): string
{
    return 'uploads/competitions/' . competition_code($c) . '/photos';
}


/**
 * URL complète photo
 */
function competition_photo_url(array $c, string $ean): string
{
    return base_url(
        competition_photos_path($c) . '/' . $ean . '.jpg'
    );
}


/**
 * Dossier compétition (legacy)
 */
function competition_folder(array $competition): string
{
    return
        $competition['saison'] . '_' .
        $competition['type'] . '_' .
        $competition['id'] . '_' .
        str_pad((string)($competition['urs_id'] ?? 0), 2, '0', STR_PAD_LEFT) . '_' .
        str_pad((string)($competition['numero'] ?? 0), 4, '0', STR_PAD_LEFT);
}


/**
 * Type label (legacy)
 */
function competition_type_label($type): string
{
    return match ((int)$type) {
        1 => 'R',
        2 => 'N',
        default => 'X'
    };
}


///////////////////////////////////////////////////////////////
/**
 * ============================================================
 * 🎯 NORMALISATION LEGACY (DEBUG UNIQUEMENT)
 * ============================================================
 *
 * ⚠️ NE PAS utiliser pour logique métier
 * Utilisé uniquement pour debug ou fallback
 * ============================================================
 */
///////////////////////////////////////////////////////////////

function competition_normalize(?string $name): ?string
{
    if (!$name) return null;

    $name = mb_strtolower($name);
    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

    // exclusions
    foreach (['defi', 'interne', 'test', 'deverminage'] as $bad) {
        if (str_contains($name, $bad)) {
            return null;
        }
    }

    $type = match (true) {
        str_contains($name, 'national 1'), str_contains($name, 'n1') => 'N1',
        str_contains($name, 'national 2'), str_contains($name, 'n2') => 'N2',
        str_contains($name, 'coupe'), str_contains($name, 'cdf') => 'COUPE',
        default => 'REGIONAL',
    };

    $cat = match (true) {
        str_contains($name, 'couleur') => 'COULEUR',
        str_contains($name, 'mono'), str_contains($name, 'noir') => 'MONOCHROME',
        str_contains($name, 'nature') => 'NATURE',
        str_contains($name, 'auteur') => 'AUTEUR',
        str_contains($name, 'audiovisuel') => 'AUDIOVISUEL',
        str_contains($name, 'quadri') => 'QUADRIMAGE',
        default => null,
    };

    if (!$cat) return null;

    return "{$type} {$cat}";
}


///////////////////////////////////////////////////////////////
/**
 * ============================================================
 * 📊 DASHBOARD HELPERS (NOUVEAU)
 * ============================================================
 */
///////////////////////////////////////////////////////////////

/**
 * Label niveau lisible
 */
function comp_level_label(string $level): string
{
    return match ($level) {
        'COUPE' => 'Coupe de France',
        'N1' => 'National 1',
        'N2' => 'National 2',
        'REGIONAL' => 'Régional',
        default => 'Autre'
    };
}


/**
 * Badge niveau
 */
function comp_level_badge(string $level): string
{
    return match ($level) {
        'COUPE' => '<span class="badge bg-danger">Coupe</span>',
        'N1' => '<span class="badge bg-warning">N1</span>',
        'N2' => '<span class="badge bg-info">N2</span>',
        'REGIONAL' => '<span class="badge bg-secondary">Régional</span>',
        default => '<span class="badge bg-dark">?</span>'
    };
}


/**
 * Badge statut club
 */
function comp_status_badge(string $status): string
{
    return match ($status) {
        'promoted' => '<span class="badge bg-success">↑ Montée</span>',
        'maintained' => '<span class="badge bg-primary">→ Maintien</span>',
        'relegated' => '<span class="badge bg-danger">↓ Descente</span>',
        default => '<span class="badge bg-secondary">-</span>'
    };
}


/**
 * Classe CSS ligne tableau
 */
function comp_row_class(string $status): string
{
    return match ($status) {
        'promoted' => 'table-success',
        'relegated' => 'table-danger',
        default => ''
    };
}


/**
 * Icône rapide
 */
function comp_status_icon(string $status): string
{
    return match ($status) {
        'promoted' => '🚀',
        'maintained' => '➖',
        'relegated' => '⚠️',
        default => ''
    };
}


/**
 * Debug rapide compétition enrichie
 */
function competition_debug_label(object $c): string
{
    return ($c->level ?? '?') . ' ' .
        ($c->discipline ?? '?') . ' (' .
        ($c->support ?? '?') . ')';
}


/**
 * Vérification type
 */
function comp_is_national(string $level): bool
{
    return in_array($level, ['N2', 'N1', 'COUPE']);
}

function comp_is_regional(string $level): bool
{
    return $level === 'REGIONAL';
}

/**
 * ============================================================
 * 🌍 CONTEXTE UR (SOURCE DE VÉRITÉ)
 * ============================================================
 */

/*
============================================================
🌍 CONTEXTE UR / EAN HELPERS
============================================================
*/



if (!function_exists('currentUR')) {

    /**
     * UR utilisateur courante
     * source unique : .env
     */
    function currentUR(): int
    {
        return (int) env(
            'copain.uruser',
            22
        );
    }
}

if (!function_exists('currentURPrefix')) {

    /**
     * Préfixe EAN (2 digits)
     * ex 12 -> "12"
     * ex 3  -> "03"
     */
    function currentURPrefix(): string
    {
        return str_pad(
            (string) currentUR(),
            2,
            '0',
            STR_PAD_LEFT
        );
    }
}



if (!function_exists('eanClubPrefix')) {

    function eanClubPrefix(
        int $clubNumber
    ): string {
        return currentURPrefix()
            .
            str_pad(
                (string)$clubNumber,
                4,
                '0',
                STR_PAD_LEFT
            );
    }
}


if (!function_exists('eanClubNumber')) {

    function eanClubNumber(
        string $ean
    ): int {
        return (int) substr(
            $ean,
            2,
            4
        );
    }
}


if (!function_exists('eanAuthorCode')) {

    function eanAuthorCode(
        string $ean
    ): string {
        return substr(
            $ean,
            6
        );
    }
}


if (!function_exists('detectCompetitionLevel')) {

    function detectCompetitionLevel(
        int $competitionId
    ): string {
        return strlen(
            (string)$competitionId
        ) === 4
            ? 'REGIONAL'
            : 'NATIONAL';
    }
}


if (!function_exists('conversionProfile')) {

    function conversionProfile(
        float $conversion
    ): string {
        return match (true) {

            $conversion >= 104
            => 'Elite',

            $conversion >= 101
            => 'Forte',

            $conversion >= 99
            => 'Equilibrée',

            default
            => 'Sous potentiel'
        };
    }
}
