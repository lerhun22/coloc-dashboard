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

    /**
     * ============================================================
     * SCORING RULES (COLOC ENGINE)
     * ============================================================
     */
    public array $scoring = [

        // 🔹 granularité des données
        'unit' => 'participation', // participation = image + competition

        // 🔹 clé unique métier
        'dedup_key' => ['ean', 'competition_id'],

        // 🔹 gestion des doublons
        'dedup_strategy' => 'max_points', // ou 'first', 'sum'

        // 🔹 calcul des points
        'points' => [
            'source' => 'note_totale',
            'normalization' => 'divide_by_judges',
            'judges_default' => 3,
        ],

        // 🔹 stratégie de cumul
        'aggregation' => [
            'mode' => 'cumulative', // 🔥 VALIDÉ
            'group_by' => 'club',
        ],

        // 🔹 source UR fiable
        'ur_source' => 'participant',

        // 🔹 exclusions
        'filters' => [
            'exclude_disqualified' => true,
            'exclude_zero' => false,
        ],
    ];

    public array $business = [

        /*
    ============================================================
    UNITÉ DE CALCUL
    ============================================================
    */
        'unit' => 'participation', // image + compétition

        /*
    ============================================================
    PARTICIPANTS
    ============================================================
    */
        'participants' => [

            // 🔥 clé métier que tu viens de découvrir
            'club_only' => true,

            // règle explicite
            'exclude_individual' => true,

            // définition technique
            'individual_condition' => [
                'club_id' => 0
            ],
        ],

        /*
    ============================================================
    IDENTITÉ CLUB
    ============================================================
    */
        'club' => [
            'source' => 'clubs_table',
            'id_field' => 'club_id',
            'label_field' => 'club_nom',

            // sécurité
            'exclude_invalid' => true,
        ],

        /*
    ============================================================
    POINTS
    ============================================================
    */
        'points' => [
            'source' => 'note_totale',
            'normalize' => true,
            'judges' => 3,
        ],

        /*
    ============================================================
    DÉDUPLICATION
    ============================================================
    */
        'dedup' => [
            'key' => ['ean', 'competition_id'],
            'strategy' => 'max',
        ],

        /*
    ============================================================
    AGRÉGATION
    ============================================================
    */
        'aggregation' => [
            'mode' => 'cumulative',
            'group_by' => 'club',
        ],

        /*
    ============================================================
    UR
    ============================================================
    */
        'ur' => [
            'source_priority' => ['club', 'participant'],
            'field' => 'ur',
        ],

        /*
    ============================================================
    FILTRES
    ============================================================
    */
        'filters' => [
            'exclude_disqualified' => true,
            'require_club' => true,
        ],
    ];
}
