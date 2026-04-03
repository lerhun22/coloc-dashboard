<?php

namespace App\Models;

use CodeIgniter\Model;

class ColocSuiviModel extends Model
{
    protected $table = 'coloc_suivi';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [

        'categorie',
        'acteur',

        'quoi',
        'details',

        'analyse',
        'benefice',
        'risque',
        'contrainte',

        'impact_systeme',
        'cout',

        'statut',
        'priorite',
        'decision',
        'version',
        'reunion',
        'saison',
        'created_by',
        'created_at'

    ];
}
