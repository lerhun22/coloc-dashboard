<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\CompetitionModel;
use App\Libraries\CompetitionService;

abstract class BaseController extends Controller
{
    /**
     * ============================================================
     * 📦 DATA PARTAGÉES (VUES)
     * ============================================================
     */
    protected $data = [];

    /**
     * ============================================================
     * 🚀 INIT CONTROLLER
     * ============================================================
     */
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $model = new CompetitionModel();

            $competitionModel = model('CompetitionModel');

  
        helper('competition');

        /*
        ============================================================
        📚 LISTE DES COMPÉTITIONS
        ============================================================
        */
        $this->data['competitions'] =
            $model->getCompetitionsWithCount();

        $this->data['competitions_list'] =
            $this->data['competitions'];

        /*
        ============================================================
        🎯 COMPÉTITION ACTIVE (PRIORITÉ SESSION)
        ============================================================
        */

        $sessionCompetitionId = session()->get('competition_id');

        if ($sessionCompetitionId) {

            // 🔥 priorité session
            $activeCompetition = $model->find($sessionCompetitionId);

        } else {

            // fallback (ancien système)
            $activeCompetition = CompetitionService::getActive();

            if ($activeCompetition && isset($activeCompetition['id'])) {
                // 🔥 synchronisation session
                session()->set('competition_id', $activeCompetition['id']);
            }
        }

        /*
        ============================================================
        📦 INJECTION DATA GLOBALES
        ============================================================
        */

        $this->data['activeCompetition'] = $activeCompetition;
        $this->data['competitionId'] = $activeCompetition['id'] ?? null;

        /*
        ============================================================
        🧠 DEBUG (optionnel)
        ============================================================
        */
        log_message('debug', 'ACTIVE COMPETITION ID = ' . ($this->data['competitionId'] ?? 'NULL'));
    
    }

    protected function isHomeRoute(): bool
    {
        $path = service('uri')->getPath();

        return $path === '' || $path === '/';
    }

    /**
     * ============================================================
     * 🎯 GET COMPÉTITION ACTIVE (SAFE)
     * ============================================================
     */
    protected function getCompetitionId(): ?int
    {
        return $this->data['competitionId'] ?? null;
    }


    /**
     * ============================================================
     * 🚨 REQUIRE COMPÉTITION (BLOQUANT)
     * ============================================================
     */
    protected function requireCompetition(): int
    {
        $competition_id = $this->getCompetitionId();

        if (!$competition_id) {
            throw new \RuntimeException("Aucune compétition active en session");
        }

        return $competition_id;
    }


    /**
     * ============================================================
     * 🔄 SET COMPÉTITION ACTIVE
     * ============================================================
     */
    protected function setCompetition(int $competition_id): void
    {
        session()->set('competition_id', $competition_id);

        // mettre à jour aussi les data locales
        $this->data['competitionId'] = $competition_id;

        log_message('debug', 'SET COMPETITION ID = ' . $competition_id);
    }

    protected function ensureActiveCompetition()
{
    // ⚠️ Adapter au bon Model
    $competitionModel = model('CompetitionModel');

    // 👉 récupération compétition active
    $activeCompetition = $competitionModel->where('active', 1)->first();

    // 🛑 aucune compétition active → redirection accueil
    if (!$activeCompetition) {
        return redirect()->to('/');
    }

    // ✅ sinon on retourne la compétition pour usage éventuel
    return $activeCompetition;
}
}