<?php

namespace App\Libraries;

use App\Models\CompetitionModel;

class CompetitionService
{
    public static function setActive($id)
    {
        session()->set('competition_id', $id);
    }

    public static function getActive()
    {
        $id = session()->get('competition_id');

        if (!$id) {
            return null;
        }

        $model = new CompetitionModel();
        //dd($model->find($id));
        return $model->find($id);
    }

    public static function getId()
    {
        return session()->get('competition_id');
    }

    public static function clear()
    {
        session()->remove('competition_id');
    }
}
