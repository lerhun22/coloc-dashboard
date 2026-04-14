<?php

namespace App\Libraries;

class RankingDecisionEngine
{
    public function apply(array $clubs, object $competition): array
    {
        $rules = $competition->rules;

        foreach ($clubs as &$club) {
            $club['status'] = 'maintained';
        }

        if (isset($rules['promotion'])) {
            foreach ($clubs as &$club) {
                if ($club['rank'] <= $rules['promotion']['top']) {
                    $club['status'] = 'promoted';
                }
            }
        }

        if (isset($rules['relegation'])) {
            foreach ($clubs as &$club) {
                if ($club['rank'] >= $rules['relegation']['from']) {
                    $club['status'] = 'relegated';
                }
            }
        }

        return $clubs;
    }
}
