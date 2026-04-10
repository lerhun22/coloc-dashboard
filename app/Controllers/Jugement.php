<?php

namespace App\Controllers;

use App\Models\PhotoModel;
use App\Models\JugementModel;
use App\Models\CompetitionModel;

class Jugement extends BaseController
{
    protected $photoModel;
    protected $jugementModel;
    protected $competitionModel;


    protected $db;

    public function __construct()
    {
        $this->photoModel = new PhotoModel();
        $this->jugementModel = new JugementModel();
        $this->competitionModel = new CompetitionModel();

        $this->db = \Config\Database::connect();
    }


    /* =====================================================
       PAGE PRINCIPALE
    ===================================================== */

    public function index()
    {
        // 🔥 récupération via session (BaseController)
        $competition_id = $this->requireCompetition();

        $competition = $this->data['activeCompetition'];

        if (!$competition) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        /*
        =====================
        DOSSIER COMPETITION
        =====================
        */

        $competitionFolder =
            $competition['saison'] . '_' .
            competition_type_label($competition['type']) . '_' .
            $competition['id'] . '_' .
            str_pad((string)$competition['urs_id'], 2, '0', STR_PAD_LEFT) . '_' .
            str_pad((string)$competition['numero'], 4, '0', STR_PAD_LEFT);
        /*
        =====================
        JUGES
        =====================
        */

        $juges = $this->db->table('juges')
            ->where('competitions_id', $competition_id)
            ->get()
            ->getResultArray();

        $nb_juges = count($juges);

        /*
        =====================
        PHOTOS + NB NOTES
        =====================
        */

        $photos = $this->db->table('photos p')

            ->select('
                p.id as photo_id,
                p.ean,
                p.passage,
                p.titre,
                p.statut,
                p.place,
                p.note_totale,
                p.saisie,
                p.retenue,
                p.medailles_id,
                p.disqualifie,
                pa.nom,
                pa.prenom,
                c.nom as club,
                COUNT(n.photos_id) as nb_notes
            ')

            ->join('participants pa', 'pa.id = p.participants_id', 'left')
            ->join('clubs c', 'c.id = pa.clubs_id', 'left')

            ->join(
                'notes n',
                'n.photos_id = p.id AND n.competitions_id = ' . (int)$competition_id,
                'left'
            )

            ->where('p.competitions_id', $competition_id)

            ->groupBy('p.id')

            ->orderBy('p.passage', 'ASC')

            ->get()

            ->getResultArray();

        //log_message('debug', json_encode($photos[0]));
        //log_message('debug', print_r($photos,true));


        if (empty($photos)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        /*
        =====================
        PHOTO COURANTE
        =====================
        */

        $photo = $photos[0];

        $photo['auteur'] =
            ($photo['prenom'] ?? '') . ' ' .
            ($photo['nom'] ?? '');

        /*
        =====================
        PATH PHOTOS (URL, PAS FILESYSTEM)
        =====================
        */

        $photosPath =
            'uploads/competitions/' .
            $competitionFolder .
            '/photos';

        /*
        =====================
        VIEW
        =====================
        */

        return view('jugement/index', array_merge($this->data, [

            'competition' => $competition,

            'photos' => $photos,
            'photo' => $photo,

            'juges' => $juges,
            'nb_juges' => $nb_juges,

            'competitionFolder' => $competitionFolder,
            'photosPath' => $photosPath,

            'position' => 1,
            'total' => count($photos)

        ]));
    }


    /* =====================================================
       PHOTO + NOTES (AJAX)
    ===================================================== */

    public function photo($photo_id = null)
    {
        if (!$photo_id) {
            return $this->response->setStatusCode(404);
        }

        $competition_id = $this->requireCompetition();

        log_message('debug', 'PHOTO ID = ' . $photo_id);

        $photo = $this->db->table('photos p')

            ->select('
            p.id as photo_id,
            p.ean,
            p.passage,
            p.titre,
            p.disqualifie,
            pa.nom,
            pa.prenom,
            c.nom as club
        ')

            ->join('participants pa', 'pa.id = p.participants_id', 'left')
            ->join('clubs c', 'c.id = pa.clubs_id', 'left')

            ->where('p.id', (int)$photo_id) // 🔥 IMPORTANT cast int

            ->get()
            ->getRowArray();

        if (!$photo) {
            return $this->response->setJSON([
                'error' => 'PHOTO NOT FOUND !!!!!!!!!',
                'photo_id' => $photo_id
            ]);
        }

        $notes = $this->db->table('notes')
            ->select('juges_id, note')
            ->where('photos_id', (int)$photo_id)
            ->where('competitions_id', $competition_id)
            ->get()
            ->getResultArray();

        $notesFormatted = [];

        foreach ($notes as $n) {
            $notesFormatted[$n['juges_id']] = $n['note'];
        }

        return $this->response->setJSON([
            'photo' => $photo,
            'notes' => $notesFormatted
        ]);
    }


    /* =====================================================
       SAVE NOTE
    ===================================================== */

    public function saveNote()
    {
        $competition_id = $this->requireCompetition();

        $photo_id = $this->request->getPost('photo_id');
        $juge_id  = $this->request->getPost('juge');
        $note     = $this->request->getPost('note');

        if (!$photo_id || !$juge_id) {
            return $this->response->setJSON(['error' => true]);
        }

        $exists = $this->db->table('notes')

            ->where('photos_id', $photo_id)
            ->where('juges_id', $juge_id)
            ->where('competitions_id', $competition_id)

            ->countAllResults();

        if ($exists) {

            $this->db->table('notes')

                ->where('photos_id', $photo_id)
                ->where('juges_id', $juge_id)
                ->where('competitions_id', $competition_id)

                ->update([
                    'note' => $note
                ]);
        } else {

            $this->db->table('notes')

                ->insert([
                    'photos_id' => $photo_id,
                    'juges_id' => $juge_id,
                    'competitions_id' => $competition_id,
                    'note' => $note
                ]);
        }

        /*
        =====================
        RECALCUL TOTAL
        =====================
        */

        $total = $this->db->table('notes')

            ->selectSum('note')

            ->where('photos_id', $photo_id)
            ->where('competitions_id', $competition_id)

            ->get()

            ->getRow()

            ->note ?? 0;

        $this->db->table('photos')

            ->where('id', $photo_id)

            ->update([
                'note_totale' => $total
            ]);

        return $this->response->setJSON([
            'ok' => true,
            'total' => $total
        ]);
    }


    /* =====================================================
       DISQUALIFY
    ===================================================== */

    public function disqualify($photo_id = null)
    {
        $competition_id = $this->requireCompetition();

        if (!$photo_id) {
            return $this->response->setStatusCode(404);
        }

        $photo = $this->db->table('photos')

            ->where('id', $photo_id)
            ->where('competitions_id', $competition_id)

            ->get()
            ->getRowArray();

        if (!$photo) {
            return $this->response->setStatusCode(404);
        }

        $new = ($photo['disqualifie'] ?? 0) ? 0 : 1;

        $this->db->table('photos')

            ->where('id', $photo_id)

            ->update([
                'disqualifie' => $new
            ]);

        return $this->response->setJSON([
            'ok' => true,
            'state' => $new ? 'disq' : 'pending'
        ]);
    }
}
