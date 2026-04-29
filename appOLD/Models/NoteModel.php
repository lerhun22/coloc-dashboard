<?php

namespace App\Models;

use CodeIgniter\Model;

class NoteModel extends Model
{
    protected $table = 'notes';

    protected $returnType = 'array';

    protected $allowedFields = [
        'juges_id',
        'photos_id',
        'note',
        'competitions_id'
    ];
}