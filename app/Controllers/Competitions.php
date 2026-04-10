<?php

namespace App\Controllers;

use App\Models\CompetitionModel;
use App\Models\PhotoModel;
use App\Libraries\CompetitionService;
use App\Libraries\CompetitionStatsBulkService;

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

        $competitions_list = $this->data['competitions_list'];

        $service = new CompetitionStatsBulkService();

        $ids = array_column($competitions_list, 'id');

        $allStats = $service->getStatsForCompetitions($ids);

        $userUr = (int)(env('copain.uruser') ?: 22);

        foreach ($competitions_list as &$competition) {

            $id = $competition['id'];
            $stats = $allStats[$id] ?? [];

            // =========================
            // 📊 STATS BASE
            // =========================
            $competition['photo_count'] = $stats['photo_count'] ?? 0;
            $competition['club_count']  = $stats['club_count'] ?? 0;

            /*
        =====================================================
        🔥 NATIONAL → recalcul EAN
        =====================================================
        */
            if (empty($competition['urs_id'])) {

                $photos = $this->photoModel
                    ->select('ean')
                    ->where('competitions_id', $id)
                    ->findAll();

                $eanStats = $service->computeFromEAN($photos, $userUr);

                $competition['author_count'] = $eanStats['participants'];
                $competition['clubs_nat']    = $eanStats['clubs_nat'];
                $competition['clubs_ur']     = $eanStats['clubs_ur'];
            } else {

                /*
            =====================================================
            🔹 REGIONAL
            =====================================================
            */
                $competition['author_count'] = $stats['author_count'] ?? 0;
                $competition['clubs_nat']    = $competition['club_count'];
                $competition['clubs_ur']     = $competition['club_count'];
            }

            /*
        =====================================================
        🔥 PERFORMANCE (TOUJOURS DEFINIE)
        =====================================================
        */
            $photos  = $competition['photo_count'];
            $authors = $competition['author_count'];

            $score = ($photos * 0.7) + ($authors * 0.3);

            $competition['performance_score'] = round($score);

            if ($score > 300) {
                $competition['performance_level'] = 'high';
            } elseif ($score > 100) {
                $competition['performance_level'] = 'medium';
            } else {
                $competition['performance_level'] = 'low';
            }
        }

        $this->data['competitions_list'] = $competitions_list;
        $this->data['userUr'] = $userUr;
        $this->data['activeCompetitionId'] = session('activeCompetitionId');

        return view('competitions/index', $this->data);
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

        $ur = getenv('copain.uruser') ?? '01';
        $ur = str_pad((string)$ur, 2, '0', STR_PAD_LEFT);

        $photos = $db->table('photos p')
            ->select("
        p.id,
        p.ean,
        p.titre,
        p.saisie,
        p.passage,
        p.place,

        CASE 
            WHEN SUBSTRING(p.ean,1,2) = '{$ur}'
                 AND pa.nom IS NOT NULL
            THEN CONCAT(pa.prenom, ' ', pa.nom)
            ELSE ''
        END AS auteur,

        CASE 
            WHEN SUBSTRING(p.ean,1,2) = '{$ur}'
                 AND cl.nom IS NOT NULL
            THEN cl.nom
            ELSE ''
        END AS club
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
