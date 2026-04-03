<?php

namespace App\Controllers;

use App\Models\ColocSuiviModel;

class Suivi extends BaseController
{
    protected $suiviModel;

    public function __construct()
    {
        $this->suiviModel = new ColocSuiviModel();
    }


    // LISTE

    public function index()
    {
        $suivi = $this->suiviModel
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('suivi/index', [
            'suivi' => $suivi,
        ]);
    }


    // CREATE

    public function create()
    {
        return view('suivi/form', [
            'item' => null
        ]);
    }


    // EDIT

    public function edit($id)
    {
        $item = $this->suiviModel->find($id);

        return view('suivi/form', [
            'item' => $item
        ]);
    }


    // SAVE

    public function save()
    {
        $data = [

            'categorie' => $this->request->getPost('categorie'),
            'acteur' => $this->request->getPost('acteur'),

            'quoi' => $this->request->getPost('quoi'),
            'details' => $this->request->getPost('details'),

            'analyse' => $this->request->getPost('analyse'),
            'benefice' => $this->request->getPost('benefice'),
            'risque' => $this->request->getPost('risque'),
            'contrainte' => $this->request->getPost('contrainte'),

            'impact_systeme' => $this->request->getPost('impact_systeme'),
            'cout' => $this->request->getPost('cout'),

            'statut' => $this->request->getPost('statut'),
            'priorite' => $this->request->getPost('priorite'),

            'decision' => $this->request->getPost('decision'),
            'version' => $this->request->getPost('version'),
            'reunion' => $this->request->getPost('reunion'),
            'saison' => $this->request->getPost('saison'),

        ];


        $id = $this->request->getPost('id');

        if ($id) {

            $this->suiviModel->update($id, $data);
        } else {

            $data['created_by'] = 'didier';
            $data['created_at'] = date('Y-m-d');

            $this->suiviModel->insert($data);
        }


        return redirect()->to('/suivi');
    }
}
