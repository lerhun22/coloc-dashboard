<?php

/**
 * ============================================================
 * 📦 HELPER : Competition Helper
 * ============================================================
 *
 * 📅 Date        : 2026-04
 * 👤 Auteur      : (à compléter)
 * 📍 Localisation : app/Helpers/competition_helper.php
 * 🧱 Architecture : CodeIgniter 4
 *
 * ============================================================
 * 🎯 OBJECTIFS
 * ============================================================
 *
 * Centraliser la génération des chemins et URLs des compétitions
 *
 * ✔ Génère les paths relatifs (uploads/...)
 * ✔ Génère les URLs complètes (base_url)
 * ✔ Évite les erreurs type /public/public
 *
 * ============================================================
 */


/**
 * Retourne le code dossier d'une compétition
 */
function competition_code($c): string
{
    $c = (array) $c; // 🔥 AJOUT CRITIQUE

    if (empty($c['id']) || empty($c['saison'])) {
        throw new \Exception("Competition invalide");
    }

    $saison = $c['saison'];
    $id     = $c['id'];
    $numero = str_pad($c['numero'] ?? 0, 4, '0', STR_PAD_LEFT);

    if (empty($c['urs_id'])) {
        return "{$saison}_N_{$id}_00_{$numero}";
    }

    $ur = str_pad($c['urs_id'], 2, '0', STR_PAD_LEFT);

    return "{$saison}_R_{$id}_{$ur}_{$numero}";
}


/**
 * Retourne le path relatif (uploads/...)
 */
function competition_photos_path(array $c): string
{
    return 'uploads/competitions/' . competition_code($c) . '/photos';
}


/**
 * Retourne l'URL complète d'une photo
 */
function competition_photo_url(array $c, string $ean): string
{
    return base_url(
        competition_photos_path($c) . '/' . $ean . '.jpg'
    );
}

function competition_folder(array $competition): string
{
    return
        $competition['saison'] . '_' .
        $competition['type'] . '_' .
        $competition['id'] . '_' .
        str_pad((string)$competition['urs_id'], 2, '0', STR_PAD_LEFT) . '_' .
        str_pad((string)$competition['numero'], 4, '0', STR_PAD_LEFT);
}

function competition_type_label($type): string
{
    return match ((int)$type) {
        1 => 'R',
        2 => 'N',
        default => 'X'
    };
}