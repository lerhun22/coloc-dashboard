<?php

namespace App\Controllers;

use App\Models\CompetitionModel;
use App\Models\PhotoModel;
use App\Libraries\CompetitionService;
use App\Libraries\CompetitionStatsBulkService;

class Competitions extends BaseController
{
    protected $photoModel;
    protected $storage;

    public function __construct()
    {
        $this->photoModel = new PhotoModel();
        $this->storage = new \App\Libraries\CompetitionStorage();
    }

    /*
    ==========================
    LISTE DES COMPÉTITIONS
    ==========================
    */

    public function index()
    {
        /*
    =========================================================
    📍 INIT
    =========================================================
    */
        $model           = new \App\Models\CompetitionModel();
        $statsService    = new \App\Libraries\CompetitionStatsBulkService;
        $nationalService = new \App\Services\NationalStatsService();

        $this->data['page_css'] = 'competitions.css';

        /*
    =========================================================
    📦 COMPÉTITIONS
    =========================================================
    */
        $competitions_list = $model->getCompetitionsWithCount();

        if (empty($competitions_list)) {
            $this->data['competitions_list'] = [];
            return view('competitions/index', $this->data);
        }

        /*
    =========================================================
    📊 STATS GLOBAL (SOURCE UNIQUE)
    =========================================================
    */
        $ids      = array_column($competitions_list, 'id');
        $allStats = $statsService->getStatsBulk($ids);

        /*
    =========================================================
    🇫🇷 PARAMÈTRE UR
    =========================================================
    */
        $userUr = (int)(env('copain.uruser') ?: 22);

        /*
    =========================================================
    🔄 ENRICHISSEMENT
    =========================================================
    */
        foreach ($competitions_list as &$competition) {

            $id    = $competition['id'];
            $stats = $allStats[$id] ?? [];

            /*
        =====================================================
        📊 STATS DE BASE
        =====================================================
        */
            $competition['photo_count']  = $stats['photo_count']  ?? 0;
            $competition['club_count']   = $stats['club_count']   ?? 0;
            $competition['author_count'] = $stats['author_count'] ?? 0;

            /*
        =====================================================
        🔥 NATIONAL → SERVICE DÉDIÉ
        =====================================================
        */
            if (empty($competition['urs_id'])) {

                $natStats = $nationalService->getStats($id, $userUr);

                $competition['author_count'] = $natStats['participants'];
                $competition['clubs_nat']    = $natStats['clubs_nat'];
                $competition['clubs_ur']     = $natStats['clubs_ur'];
            } else {

                /*
            =====================================================
            🔹 REGIONAL
            =====================================================
            */
                $competition['clubs_nat'] = $competition['club_count'];
                $competition['clubs_ur']  = $competition['club_count'];
            }

            /*
        =====================================================
        🎯 PERFORMANCE
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
        /*
        =========================================================
        🎯 FILTRE PAR UR UTILISATEUR
        Nationales + UR utilisateur uniquement
        =========================================================
        */
        $competitions_list = array_values(
            array_filter(
                $competitions_list,
                function ($c) use ($userUr) {

                    // conserver les compétitions nationales
                    if (empty($c['urs_id'])) {
                        return true;
                    }

                    // conserver uniquement l'UR utilisateur (.env)
                    return (int)$c['urs_id'] === $userUr;
                }
            )
        );

        /*
=========================================================
🎯 FILTRE UR
=========================================================
*/
        $competitions_list = array_values(
            array_filter($competitions_list, function ($c) use ($userUr) {

                if (empty($c['urs_id'])) return true;

                return (int)$c['urs_id'] === $userUr;
            })
        );

        /*
=========================================================
📁 CHECK FILESYSTEM + STATUS (ICI 🔥)
=========================================================
*/

        $storage = new \App\Libraries\CompetitionStorage();

        foreach ($competitions_list as &$competition) {

            $photoCountFs = 0;

            $photosPath = $storage->getPhotosPathIfExists($competition);

            if ($photosPath && is_dir($photosPath)) {

                foreach (scandir($photosPath) as $f) {
                    if ($f === '.' || $f === '..') continue;

                    if (preg_match('/\.(jpg|jpeg)$/i', $f)) {
                        $photoCountFs++;
                    }
                }
            }

            // 🔥 TOUJOURS définir
            if ($photoCountFs > 0) {
                $competition['status'] = 'ready';
            } elseif (!empty($competition['photo_count'])) {
                $competition['status'] = 'missing_files';
            } else {
                $competition['status'] = 'empty';
            }

            $competition['photo_count_fs'] = $photoCountFs;
        }

        /*
=========================================================
🔽 TRI : REGIONAL → NATIONAL
=========================================================
*/

        usort($competitions_list, function ($a, $b) {

            $aRegional = !empty($a['urs_id']);
            $bRegional = !empty($b['urs_id']);

            if ($aRegional !== $bRegional) {
                return $aRegional ? -1 : 1;
            }

            return strcmp(
                $b['date_competition'] ?? '',
                $a['date_competition'] ?? ''
            );
        });

        /*
=========================================================
🎨 DATA VIEW
=========================================================
*/

        $this->data['competitions_list']   = $competitions_list;
        $this->data['userUr']              = $userUr;
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

            // 🔥 JOIN basé sur EAN (clé de la solution)
            ->join('clubs cl', "cl.numero = SUBSTRING(p.ean, 3, 4)", 'left')

            ->where('p.competitions_id', $id)
            ->orderBy('p.place', 'ASC')
            ->orderBy('p.saisie', 'ASC')
            ->get()
            ->getResultArray();

        $photosOLD = $db->table('photos p')
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
