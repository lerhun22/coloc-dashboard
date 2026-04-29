<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\JudgeModel;

class Juges extends BaseController
{
    public function index()
    {
        $model = new JudgeModel();

        $data['judges'] = $model->findAll();

        return view('juges/index', $data);
    }

    public function create()
    {
        return view('juges/create');
    }

    public function store()
    {
        $model = new JudgeModel();

        $model->insert([
            'nom' => trim($this->request->getPost('nom')),
            'competitions_id' => 0 // libre par défaut
        ]);

        return redirect()->to('/juges')
            ->with('success', 'Juge créé.');
    }

    public function delete($id)
    {
        $model = new JudgeModel();

        $judge = $model->find($id);

        if ($judge['competitions_id'] != 0) {
            return redirect()->to('/juges')
                ->with('error', 'Impossible : juge affecté.');
        }

        $model->delete($id);

        return redirect()->to('/juges')
            ->with('success', 'Juge supprimé.');
    }
}
