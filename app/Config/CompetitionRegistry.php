<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * ============================================================
 * CompetitionRegistry
 * ============================================================
 * Source de vérité métier FPF (COLOC)
 *
 * - niveaux
 * - règles de calcul
 * - accès
 * - disciplines
 *
 * Version : 2026
 * ============================================================
 */

class CompetitionRegistry extends BaseConfig
{
    /**
     * ============================================================
     * LEVELS (règles principales)
     * ============================================================
     */
    public array $levels = [

        'REGIONAL' => [
            'label' => 'Régional',
            'access' => 'selection_regional',
            'has_progression' => true,
            'photos_retained' => null,
        ],

        'N2' => [
            'label' => 'National 2',
            'access' => 'selection_regional',
            'has_progression' => true,

            // règles photos
            'photos_retained' => 6,
            'photos_max'      => 6,

            // flux
            'promotion' => [
                'to'  => 'N1',
                'top' => 18
            ],
        ],

        'N1' => [
            'label' => 'National 1',
            'access' => 'selection_national',
            'has_progression' => true,

            'photos_retained' => 15,
            'photos_max'      => 18,

            'promotion' => [
                'to'  => 'COUPE',
                'top' => 12
            ],

            'relegation' => [
                'to'   => 'REGIONAL',
                'from' => 33
            ],
        ],

        'COUPE' => [
            'label' => 'Coupe de France',
            'access' => 'selection_national',
            'has_progression' => true,

            'photos_retained' => 25,
            'photos_max'      => 28,

            'relegation' => [
                'to'   => 'N1',
                'from' => 21
            ],
        ],

        'DIRECT' => [
            'label' => 'Accès direct',
            'access' => 'direct',
            'has_progression' => false,
        ],
    ];

    /**
     * ============================================================
     * DISCIPLINES
     * ============================================================
     */
    public array $disciplines = [

        'MONOCHROME' => [
            'participants' => 'club',
            'supports' => ['PAPIER', 'IP'],
        ],

        'COULEUR' => [
            'participants' => 'club',
            'supports' => ['PAPIER', 'IP'],
        ],

        'NATURE' => [
            'participants' => 'club',
            'supports' => ['PAPIER', 'IP'],
        ],

        'AUTEUR' => [
            'participants' => 'author',
            'supports' => ['PAPIER'],
        ],

        'QUADRIMAGE' => [
            'participants' => 'club',
            'supports' => ['IP'],
            'levels' => ['REGIONAL', 'N2'], // restriction
        ],

        'REPORTAGE' => [
            'participants' => 'author',
            'supports' => ['PAPIER'],
            'levels' => ['DIRECT'],
        ],

        'LIVRE' => [
            'participants' => 'author',
            'supports' => ['PAPIER'],
            'levels' => ['DIRECT'],
        ],

        'SUPER_CHALLENGE' => [
            'participants' => 'author',
            'supports' => ['IP'],
            'levels' => ['DIRECT'],
        ],
    ];

    /**
     * ============================================================
     * GLOBAL RULES
     * ============================================================
     */
    public array $rules = [

        'tie_break' => [
            'criteria' => ['score', 'nb_20', 'nb_19'],
            'max_clubs' => 3
        ],

        'quotas' => [
            'formula' => '(total_photos / total_clubs) * region_clubs'
        ]
    ];
}
