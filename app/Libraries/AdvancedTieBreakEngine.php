<?php

namespace App\Libraries;

class AdvancedTieBreakEngine
{
    public function resolve(array $clubs, object $competition): array
    {
        $groups = [];

        foreach ($clubs as $club) {
            $groups[$club['score']][] = $club;
        }

        krsort($groups);

        $result = [];

        foreach ($groups as $group) {

            if (count($group) === 1) {
                $result[] = $group[0];
                continue;
            }

            usort($group, function ($a, $b) {
                return [$b['nb_20'], $b['nb_19']] <=> [$a['nb_20'], $a['nb_19']];
            });

            foreach ($group as $club) {
                $result[] = $club;
            }
        }

        $rank = 1;
        foreach ($result as &$club) {
            $club['rank'] = $rank++;
        }

        return $result;
    }
}
