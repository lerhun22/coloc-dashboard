<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompetitionModel;

class Preparation extends BaseController
{
    public function index()
    {
        $competitionModel = new CompetitionModel();
        $db = \Config\Database::connect();

        $competitions = $competitionModel
            ->orderBy('date_competition', 'DESC')
            ->findAll();

        $counts = $db->table('photos')
            ->select('competitions_id, COUNT(id) as total')
            ->groupBy('competitions_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($counts as $row) {
            $map[$row['competitions_id']] = $row['total'];
        }

        foreach ($competitions as &$competition) {
            $competition['nb_oeuvres'] = $map[$competition['id']] ?? 0;
        }

        $db->close();

        return view('preparation/index', [
            'competitions' => $competitions,
        ]);
    }
}
