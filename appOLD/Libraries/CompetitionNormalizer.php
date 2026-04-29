<?php

namespace App\Libraries;

use Config\CompetitionRegistry;

class CompetitionNormalizer
{
    protected CompetitionRegistry $registry;

    public function __construct()
    {
        $this->registry = config('CompetitionRegistry');
    }

    public function normalize(object $competition): object
    {
        $competition->level      = $this->detectLevel($competition);
        $competition->discipline = $this->detectDiscipline($competition);
        $competition->support    = $this->detectSupport($competition);

        $competition->participants =
            $this->registry->disciplines[$competition->discipline]['participants'] ?? 'club';

        $competition->access_type =
            $this->registry->levels[$competition->level]['access'] ?? 'unknown';

        $competition->has_progression =

            $this->registry->levels[$competition->level]['has_progression'] ?? false;

        $competition->rules =
            $this->registry->levels[$competition->level] ?? [];

        return $competition;
    }

    protected function detectLevel(object $competition): string
    {
        $label = strtolower($competition->nom ?? $competition->libelle ?? '');

        return match (true) {
            str_contains($label, 'coupe') => 'COUPE',

            str_contains($label, 'national 1'),
            str_contains($label, 'n1'),
            str_contains($label, 'national1') => 'N1',

            str_contains($label, 'national 2'),
            str_contains($label, 'n2'),
            str_contains($label, 'national2') => 'N2',

            str_contains($label, 'regional') => 'REGIONAL',

            default => 'DIRECT',
        };
    }

    protected function detectDiscipline(object $competition): string
    {
        $label = strtolower($competition->libelle ?? $competition->nom ?? '');

        return match (true) {
            str_contains($label, 'mono') => 'MONOCHROME',
            str_contains($label, 'couleur') => 'COULEUR',
            str_contains($label, 'nature') => 'NATURE',
            str_contains($label, 'auteur') => 'AUTEUR',
            default => 'UNKNOWN',
        };
    }

    protected function detectSupport(object $competition): string
    {
        $label = strtolower($competition->libelle ?? $competition->nom ?? '');

        return match (true) {
            str_contains($label, 'papier') => 'PAPIER',
            str_contains($label, 'ip'),
            str_contains($label, 'image projetee'),
            str_contains($label, 'image projetée') => 'IP',
            default => 'UNKNOWN',
        };
    }
}
