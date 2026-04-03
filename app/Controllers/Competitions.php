<?php

namespace App\Controllers;

use App\Models\CompetitionModel;
use App\Models\PhotoModel;
use App\Libraries\CompetitionService;

class Competitions extends BaseController
{
    protected $photoModel;

    public function __construct()
    {
        $this->photoModel = new PhotoModel();
    }


    /*
    ==========================
    LISTE DES COMPÉTITIONS
    ==========================
    */

    public function index()
    {
        $model = new CompetitionModel();

        $this->data['competitions_list'] =
            $model->getCompetitionsWithCount();

        $this->data['page_css'] = 'competitions.css';

        return view(
            'competitions/index',
            $this->data
        );
    }


    /*
    ==========================
    SELECT
    ==========================
    */

    public function select($id)
    {
        $model = new CompetitionModel();

        $competition = $model->find($id);

        if (!$competition) {
            return redirect()->to('/competitions');
        }

        CompetitionService::setActive($id);

        return redirect()->to('/competitions');
    }


    /*
    ==========================
    SHOW
    ==========================
    */

    public function show($id)
    {
        $model = new CompetitionModel();

        $competition = $model->getCompetitionStats($id);

        if (!$competition) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (CompetitionService::getActive() != $id) {
            CompetitionService::setActive($id);
        }

        return redirect()->to(
            site_url('competitions/' . $id . '/photos')
        );
    }


    /*
    ==========================
    PHOTOS
    ==========================
    */

    public function photos($id = null)
    {
        if (!$id) {
            $id = CompetitionService::getActive();
        }

        if (!$id) {
            return redirect()->to('/competitions');
        }

        CompetitionService::setActive($id);

        $competitionModel = new CompetitionModel();

        $competition =
            $competitionModel->getCompetitionStats($id);

        if (!$competition) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $db = \Config\Database::connect();

        $photos = $db->table('photos p')
            ->select("
                p.id,
                p.ean,
                p.titre,
                p.saisie,
                p.passage,
                p.place,
                pa.nom AS auteur,
                cl.nom AS club
            ")
            ->join('participants pa', 'pa.id = p.participants_id', 'left')
            ->join('clubs cl', 'cl.id = pa.clubs_id', 'left')
            ->where('p.competitions_id', $id)
            ->orderBy('p.place', 'ASC')
            ->orderBy('p.saisie', 'ASC')
            ->get()
            ->getResultArray();

        $this->data['competition'] = $competition;
        $this->data['photos'] = $photos;

        return view(
            'competitions/photos',
            $this->data
        );
    }


    /*
    ==========================
    NOTATION
    ==========================
    */

    public function notation($competition_id = null)
    {
        if (!$competition_id) {
            $competition_id = CompetitionService::getActive();
        }

        if (!$competition_id) {
            return redirect()->to('/competitions');
        }

        CompetitionService::setActive($competition_id);

        $competitionModel = new CompetitionModel();

        $competition =
            $competitionModel->find($competition_id);

        $this->data['competition'] = $competition;

        return view(
            'competitions/notation',
            $this->data
        );
    }


    /*
    ==========================
    SCAN
    ==========================
    */

    public function scan($competitionId = null)
    {
        if (!$competitionId) {
            $competitionId = CompetitionService::getActive();
        }

        if (!$competitionId) {
            return redirect()->to('/competitions');
        }

        $ean = $this->request->getPost('ean');

        $photo = $this->photoModel
            ->where('ean', $ean)
            ->where('competitions_id', $competitionId)
            ->first();

        $this->data['photo'] = $photo;
        $this->data['competitionId'] = $competitionId;

        return view(
            'competitions/notation',
            $this->data
        );
    }


    /*
    ==========================
    SAVE NOTES
    ==========================
    */

    public function saveNotes($competitionId = null)
    {
        if (!$competitionId) {
            $competitionId = CompetitionService::getActive();
        }

        if (!$competitionId) {
            return redirect()->to('/competitions');
        }

        return redirect()->back();
    }
    public function delete($id)
    {
        $id = (int)$id;

        $cleaner =
            new \App\Libraries\CompetitionCleaner();

        $cleaner->deleteCompetition($id);

        return redirect()->to(
            base_url('competitions')
        );
    }
}
