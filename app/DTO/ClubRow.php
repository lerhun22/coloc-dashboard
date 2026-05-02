<?php

namespace App\DTO;

class ClubRow
{
    public static function normalize(array $r): array
    {
        return [
            'club_id' => (int)($r['club_id'] ?? 0),

            // 🔥 clé unifiée
            'nom' =>
            !empty(trim((string)($r['club_nom'] ?? '')))
                ? $r['club_nom']
                : ($r['nom'] ?? '[club inconnu]'),

            'numero' =>
            $r['numero']
                ?? $r['club_numero']
                ?? null,

            'ur' => (int)($r['ur'] ?? 0),

            'points' => (float)($r['points'] ?? 0),

            'level' => strtoupper(trim((string)($r['level'] ?? ''))),

            'total_images' =>
            (int)(
                $r['total_images']
                ?? $r['nb_photos']
                ?? 0
            ),

            'authors' =>
            (int)($r['authors'] ?? 0),

            'motor_authors' =>
            (int)($r['motor_authors'] ?? 0),
        ];
    }
}
