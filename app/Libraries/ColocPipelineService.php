<?php

namespace App\Libraries;

use App\Libraries\CompetitionNormalizer;
use App\Libraries\FPFRankingService;

class ColocPipelineService
{
    protected CompetitionNormalizer $normalizer;
    protected FPFRankingService $rankingService;

    public function __construct()
    {
        $this->normalizer     = new CompetitionNormalizer();
        $this->rankingService = new FPFRankingService();
    }

    /*
    ======================================================
    MAIN PIPELINE
    ======================================================
    */

    public function processCompetition(int $competitionId, ?int $urId = null, bool $debug = false): array
    {
        $db = \Config\Database::connect();

        /*
        =========================
        LOAD COMPETITION
        =========================
        */

        $competition = $db->table('competitions')
            ->where('id', $competitionId)
            ->get()
            ->getRow();

        if (!$competition) {
            return [
                'status' => 'error',
                'message' => 'Competition introuvable'
            ];
        }

        $competition = $this->normalizer->normalize($competition);

        /*
        =========================
        CHECK JUGÉE
        =========================
        */

        if (!$this->isCompetitionJudged($competitionId)) {
            return [
                'status'      => 'empty',
                'message'     => 'Competition non jugée',
                'competition' => $competition,
                'clubs'       => []
            ];
        }

        /*
        =========================
        LOAD PHOTOS (EAN FIRST)
        =========================
        */

        $photosResult = $this->loadPhotos($competitionId, $urId, $debug);

        if ($photosResult['status'] !== 'ok') {
            return [
                'status'      => $photosResult['status'],
                'message'     => $photosResult['message'],
                'competition' => $competition,
                'clubs'       => []
            ];
        }

        $photos = $photosResult['photos'];

        /*
        =========================
        RANKING
        =========================
        */

        $clubs = $this->rankingService->compute($photos, $competition);

        /*
        =========================
        POST PROCESS
        =========================
        */

        foreach ($clubs as &$club) {

            $club['nb_photos'] = count($club['photos']);

            $club['nb_disqualifiees'] = count(
                array_filter($club['photos'], fn($p) => $p['disqualifie'] == 1)
            );

            $club['status'] = $this->computeStatus($club, $competition);
        }

        return [
            'status'      => 'ok',
            'competition' => $competition,
            'clubs'       => $clubs
        ];
    }

    /*
    ======================================================
    CHECK COMPETITION JUGÉE
    ======================================================
    */

    protected function isCompetitionJudged(int $competitionId): bool
    {
        $db = \Config\Database::connect();

        $row = $db->table('classements')
            ->where('competitions_id', $competitionId)
            ->get()
            ->getRow();

        if (!$row) {
            return false;
        }

        return (
            (int)($row->photos ?? 0) === 1 ||
            (int)($row->clubs ?? 0) === 1 ||
            (int)($row->auteurs ?? 0) === 1 ||
            (int)($row->graphe ?? 0) === 1
        );
    }

    /*
    ======================================================
    LOAD PHOTOS (EAN FIRST)
    ======================================================
    */

    protected function loadPhotos(int $competitionId, ?int $urId = null, bool $debug = false): array
    {
        $db = \Config\Database::connect();

        /*
    =========================
    UR CONTEXT
    =========================
    */

        if ($urId === null && env('copain.uruser')) {
            $urId = (int) env('copain.uruser');
        }

        if ($urId === 0) {
            $urId = null; // mode national
        }

        /*
    =========================
    MAP CLUBS (FIABILISATION NOMS)
    =========================
    */
        $clubMap = [];

        $clubsDb = $db->table('clubs')
            ->select('urs_id, numero, nom')
            ->get()
            ->getResultArray();

        foreach ($clubsDb as $c) {

            // ⚠️ numero peut être 603 → on pad
            $numero = str_pad($c['numero'], 4, '0', STR_PAD_LEFT);

            $key = $c['urs_id'] . $numero;

            $clubMap[$key] = $c['nom'];
        }

        /*
    =========================
    QUERY PHOTOS
    =========================
    */

        $builder = $db->table('photos p');

        $builder->select([
            'p.id',
            'p.ean',
            'p.participants_id',
            'p.note_totale',
            'p.retenue',
            'p.disqualifie',
            'c.id AS club_id',
            'c.nom AS club_name'
        ]);

        // LEFT JOIN volontaire (CdF parfois incomplet)
        $builder->join('participants pa', 'pa.id = p.participants_id', 'left');
        $builder->join('clubs c', 'c.id = pa.clubs_id', 'left');

        $builder->where('p.competitions_id', $competitionId);

        /*
    =========================
    FILTRE UR
    =========================
    */

        if ($urId !== null) {
            $builder->like('p.participants_id', (string)$urId, 'after');
        }

        $rows = $builder->get()->getResultArray();

        /*
    =========================
    DEBUG
    =========================
    */

        if ($debug) {
            log_message('info', 'NB ROWS RAW: ' . count($rows));
        }

        if (empty($rows)) {
            return [
                'status' => 'empty',
                'message' => 'Aucune photo pour ce filtre',
                'photos' => []
            ];
        }

        /*
    =========================
    NORMALISATION
    =========================
    */

        $photos = [];

        foreach ($rows as $r) {

            $ean = $r['ean'];

            /*
        =========================
        CLUB (clé fiable)
        =========================
        */

            if (!empty($r['participants_id'])) {
                $clubId = (int) substr($r['participants_id'], 0, 6);
            } else {
                $clubId = (int) substr($ean, 0, 6); // fallback
            }

            // 🔥 récupération nom réel si possible
            $clubName = $clubMap[$clubId]
                ?? $r['club_name']
                ?? ('Club ' . $clubId);

            /*
        =========================
        AUTEUR
        =========================
        */

            $auteurId = !empty($r['participants_id'])
                ? substr($r['participants_id'], 0, 10)
                : substr($ean, 0, 10);

            /*
        =========================
        SCORE
        =========================
        */

            $noteTotale = (int) $r['note_totale'];
            $score      = $noteTotale > 0 ? round($noteTotale / 3, 2) : 0;

            if ((int)$r['disqualifie'] === 1) {
                $score = 0;
            }

            /*
        =========================
        BUILD PHOTO
        =========================
        */

            $photos[] = [
                'id'            => (int) $r['id'],
                'ean'           => $ean,
                'ur'            => (int) substr($ean, 0, 2),
                'club_id'       => $clubId,
                'club_name'     => $clubName,
                'auteur_id'     => $auteurId,
                'note_totale'   => $noteTotale,
                'retenue'       => (int) $r['retenue'],
                'disqualifie'   => (int) $r['disqualifie'],
                'uid'           => $ean . '_' . $competitionId,
                'score'         => $score
            ];
        }

        /*
    =========================
    DEBUG FINAL
    =========================
    */

        if ($debug) {
            log_message('info', 'Photos normalisées: ' . count($photos));
        }

        return [
            'status' => 'ok',
            'photos' => $photos
        ];
    }

    /*http://localhost:8888/coloc_v3/coloc/debug/735
    ======================================================
    STATUS CLUB
    ======================================================
    */

    protected function computeStatus(array $club, object $competition): string
    {
        $rank = $club['rank'];

        if (!empty($competition->rules['promotion']['top'])) {
            if ($rank <= $competition->rules['promotion']['top']) {
                return 'promoted';
            }
        }

        if (!empty($competition->rules['relegation']['from'])) {
            if ($rank >= $competition->rules['relegation']['from']) {
                return 'relegated';
            }
        }

        return 'maintained';
    }
}
