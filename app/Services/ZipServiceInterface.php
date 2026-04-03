<?php

namespace App\Services;

interface ZipServiceInterface
{
    /**
     * Traite entièrement le ZIP d'une compétition
     *
     * Pipeline attendu :
     * - génération (si nécessaire)
     * - téléchargement
     * - extraction
     * - déplacement fichiers
     * - génération vignettes
     * - nettoyage
     *
     * @param object|array $competition
     * @return bool
     *
     * @throws \Throwable en cas d'erreur bloquante
     */
    public function process($competition): bool;
}