<?php

namespace App\Models;

use CodeIgniter\Model;

class PhotoModel extends Model
{
    protected $table = 'photos';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [

        'id',

        'ean',

        'competitions_id',

        'participants_id',

        'titre',

        'statut',

        'place',

        'note_totale',

        'saisie',

        'retenue',

        'medailles_id',

        'passage',

        'disqualifie'

    ];
}
