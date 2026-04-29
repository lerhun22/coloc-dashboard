<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class CompetitionMetaBuilder
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function build(int $season, bool $debug = false): array
    {
        $rows = $this->db->query("
            SELECT id, nom, saison
            FROM competitions
            WHERE saison = ?
        ", [$season])->getResultArray();

        $inserted = 0;
        $updated  = 0;
        $errors   = [];

        foreach ($rows as $r) {

            $nom = strtolower($r['nom']);

            // ============================================
            // EXCLUSION
            // ============================================
            $isOfficial = !(str_contains($nom, 'challenge') || str_contains($nom, 'defi'));

            // ============================================
            // LEVEL
            // ============================================
            $level = match (true) {
                str_contains($nom, 'coupe')       => 'CDF',
                str_contains($nom, 'national 1')  => 'N1',
                str_contains($nom, 'national 2'),
                str_contains($nom, 'national2')   => 'N2',
                default                           => 'REGIONAL',
            };

            // ============================================
            // DISCIPLINE
            // ============================================
            $discipline = match (true) {
                str_contains($nom, 'monochrome')   => 'MONOCHROME',
                str_contains($nom, 'couleur')      => 'COULEUR',
                str_contains($nom, 'nature')       => 'NATURE',
                str_contains($nom, 'auteur')       => 'AUTEUR',
                str_contains($nom, 'quadrimage')   => 'QUADRIMAGE',
                str_contains($nom, 'audiovisuel')  => 'AUDIOVISUEL',
                default                            => 'UNKNOWN',
            };

            // ============================================
            // SUPPORT (AVEC RÈGLES MÉTIER)
            // ============================================

            $support = match (true) {

                // explicite dans le nom
                str_contains($nom, 'papier')         => 'PAPIER',
                str_contains($nom, 'image projet')   => 'IP',

                // règles métier fallback
                $discipline === 'AUTEUR'             => 'PAPIER',
                $discipline === 'QUADRIMAGE'         => 'IP',
                $discipline === 'AUDIOVISUEL'        => 'IP',

                default => 'UNKNOWN',
            };

            // ============================================
            // TYPE PARTICIPANT
            // ============================================
            $participantsType = str_contains($nom, 'auteur') ? 'author' : 'club';

            // ============================================
            // VALIDATION
            // ============================================
            if ($discipline === 'UNKNOWN' || $support === 'UNKNOWN') {
                $errors[] = $r['nom'];
            }

            // ============================================
            // UPSERT
            // ============================================
            $exists = $this->db->table('competition_meta')
                ->where('competition_id', $r['id'])
                ->countAllResults();

            $data = [
                'competition_id'   => $r['id'],
                'saison'           => $r['saison'],
                'level'            => $level,
                'discipline'       => $discipline,
                'support'          => $support,
                'participants_type' => $participantsType,
                'is_official'      => $isOfficial ? 1 : 0,
                'source_label'     => $r['nom'],
                'normalized_at'    => date('Y-m-d H:i:s'),
            ];

            if ($exists) {
                $this->db->table('competition_meta')
                    ->where('competition_id', $r['id'])
                    ->update($data);
                $updated++;
            } else {
                $this->db->table('competition_meta')
                    ->insert($data);
                $inserted++;
            }

            if ($debug) {
                echo $r['nom'] . " => $level / $discipline / $support\n";
            }
        }

        return [
            'inserted' => $inserted,
            'updated'  => $updated,
            'errors'   => $errors
        ];
    }
}
