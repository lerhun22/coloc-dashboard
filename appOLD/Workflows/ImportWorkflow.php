<?php

namespace App\Workflows;

use App\Libraries\CompetitionStorage;
use App\Models\PhotoModel;

class ImportWorkflow
{
    protected int $passage = 1;

    public function start(array $data): array
    {
        // 1. création compétition + dossiers
        $context = $this->stepCreateCompetition($data);

        // 2. import images / photos
        $this->stepImportImages($context, $data);

        return [
            'status' => 'ok',
            'competition_id' => $context['competition_id'],
        ];
    }

    /**
     * STEP 1 : création compétition
     */
    protected function stepCreateCompetition(array $data): array
    {
        $storage = new CompetitionStorage();

        $name = $data['nom'] ?? ('compet_' . $data['compet']['id']);

        // création dossiers
        $basePath = $storage->createFolders($name);

        // enregistrement DB
        log_message('debug', 'IMPORT DATA=' . print_r($data, true));

        $competitionId = $storage->registerCompetition($data['compet']);

        return [
            'competition_id' => $competitionId,
            'base_path' => $basePath,
            'photos_path' => $basePath . 'photos/',
        ];
    }

    /**
     * STEP 2 : import photos (ZIP ou Copain JSON)
     */
    protected function stepImportImages(array $context, array $data): void
    {
        $photosPath = $context['photos_path'];

        /**
         * 🔵 CAS 1 : IMPORT ZIP LOCAL (inchangé)
         */
        if (!empty($data['payload']['images'])) {

            foreach ($data['payload']['images'] as $image) {

                $sourcePath = $image['path'];
                $filename = $image['filename'];

                $dest = $photosPath . $filename;

                // copy fichier
                if (file_exists($sourcePath)) {
                    copy($sourcePath, $dest);
                }

                $this->registerPhoto(
                    $context['competition_id'],
                    $filename,
                    $image
                );
            }

            return;
        }

        /**
         * 🟢 CAS 2 : IMPORT COPAIN (JSON)
         */
        if (!empty($data['payload']['photos_json'])) {

            $photos = $this->normalizePhotos($data['payload']['photos_json']);

            foreach ($photos as $photo) {

                $filename = $this->extractFilename($photo);

                if (!$filename) {
                    log_message('error', 'PHOTO SANS FILENAME: ' . json_encode($photo));
                    continue;
                }

                // ⚠️ pas encore de fichier → juste DB
                $this->registerPhoto(
                    $context['competition_id'],
                    $filename,
                    $photo
                );
            }
        }
    }

    /**
     * 🔧 NORMALISATION JSON (clé critique)
     */
    protected function normalizePhotos(array $json): array
    {
        if (isset($json['photos'])) {
            return $json['photos'];
        }

        if (isset($json['data'])) {
            return $json['data'];
        }

        if (array_is_list($json)) {
            return $json;
        }

        log_message('error', 'STRUCTURE JSON INCONNUE: ' . json_encode(array_keys($json)));

        return [];
    }

    /**
     * 🔧 EXTRACTION FILENAME ROBUSTE
     */
    protected function extractFilename(array $photo): ?string
    {
        return
            $photo['fichier']
            ?? $photo['filename']
            ?? $photo['file']
            ?? null;
    }

    /**
     * REGISTER PHOTO (version améliorée)
     */
    protected function registerPhoto(
        int $competitionId,
        string $filename,
        array $photo = []
    ): void {

        $model = new PhotoModel();

        // 🔥 1. récupérer / créer participant
        $participantId = $this->resolveParticipant($photo);

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $ean = strlen($name) === 12 ? $name : null;

        $titre = $photo['titre']
            ?? $photo['title']
            ?? $filename;

        $model->insert([
            'ean' => $ean,

            'competitions_id' => $competitionId,

            'participants_id' => $participantId,

            'titre' => $titre,

            'statut' => 0,
            'place' => 0,
            'note_totale' => 0,
            'saisie' => 0,
            'retenue' => 0,
            'medailles_id' => null,

            'passage' => $this->passage,
            'disqualifie' => 0
        ]);

        $this->passage++;
    }
    protected function resolveParticipant(array $photo): string
    {
        $db = \Config\Database::connect();

        // 🔥 mapping Copain (souple)
        $nom = $photo['nom'] ?? $photo['auteur_nom'] ?? 'INCONNU';
        $prenom = $photo['prenom'] ?? $photo['auteur_prenom'] ?? '';

        $clubId = $photo['clubs_id'] ?? $photo['club_id'] ?? 0;

        // ⚠️ clé unique simple (important)
        $participantId = substr(md5($nom . $prenom . $clubId), 0, 10);

        // existe déjà ?
        $existing = $db->table('participants')
            ->where('id', $participantId)
            ->get()
            ->getRowArray();

        if ($existing) {
            return $participantId;
        }

        // création
        $db->table('participants')->insert([
            'id' => $participantId,
            'urs_id' => 0,
            'clubs_id' => $clubId,
            'nom' => $nom,
            'prenom' => $prenom,
            'etat_adhesion' => 1,
            'annee_cotisation' => date('Y')
        ]);

        return $participantId;
    }

    /**
     * PARTICIPANT DEFAULT
     */
    protected function getDefaultParticipant(): string
    {
        $db = \Config\Database::connect();

        $id = '0000';

        $row = $db->table('participants')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($row) {
            return $id;
        }

        $db->table('participants')->insert([
            'id' => $id,
            'urs_id' => 0,
            'clubs_id' => 0,
            'nom' => 'IMPORT',
            'prenom' => 'IMPORT',
            'etat_adhesion' => 1,
            'annee_cotisation' => date('Y')
        ]);

        return $id;
    }
}