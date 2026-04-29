<?php
/*253*/

namespace App\Services;

use App\Libraries\CopainLegacyReader;
use App\Models\CompetitionModel;

class CopainImportService
{
    protected $reader;
    protected $competitionModel;

    public function __construct()
    {
        $this->reader = new CopainLegacyReader();
        $this->competitionModel = new CompetitionModel();
    }

    public function importCompetitions($email, $password)
    {
        $data = $this->reader->getCompetitions($email, $password);

        if ($data['code'] != 0) {
            return false;
        }

        $count = 0;

        if (!empty($data['competitions'])) {

            foreach ($data['competitions'] as $c) {

                $this->competitionModel->save([
                    'id'     => $c['id'],
                    'nom'    => $c['nom'],
                    'saison' => $c['saison'],
                    'numero' => $c['numero'] ?? 0, // 🔥 AJOUT ICI
                    'urs_id' => $c['urs_id'] ?? null,
                    'type' => empty($c['urs_id']) ? 'N' : 'R'
                ]);

                $count++;
            }
        }

        if (!empty($data['rcompetitions'])) {

            foreach ($data['rcompetitions'] as $c) {

                $this->competitionModel->save([
                    'id'     => $c['id'],
                    'nom'    => $c['nom'],
                    'saison' => $c['saison'],
                    'urs_id' => $c['urs_id'],
                    'numero' => $c['numero'] ?? 0, // 🔥 AJOUT ICI
                    'type' => empty($c['urs_id']) ? 'N' : 'R'
                ]);

                $count++;
            }
        }

        return $count;
    }
}