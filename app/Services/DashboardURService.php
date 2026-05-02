<?php

namespace App\Services;

use App\Services\SeasonService;
use App\DTO\ClubRow;

class DashboardURService
{

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function build(array $rows, ?int $ur = null): array
    {
        $ur ??= currentUR();
        $userUR = $ur;

        /*
    ============================================================
    [1] DATA FOUNDATION
    - agrégation brute des clubs
    ============================================================
    */
        $classement = $this->aggregateByClub($rows);

        /*
    ============================================================
    [2] RANKING GLOBAL
    - tri national
    - calcul rang
    ============================================================
    */
        usort($classement, fn($a, $b) => $b['points'] <=> $a['points']);

        foreach ($classement as $i => &$c) {
            $c['rang'] = $i + 1;
            $c['N1_CDF'] = $c['N1'] + $c['CDF']; // shortcut UI
        }
        unset($c);

        /*
    ============================================================
    [3] SCOPE UR
    - filtrage clubs UR cible
    ============================================================
    */
        $urClubs = array_values(
            array_filter($classement, fn($c) => (int)$c['ur'] === $ur)
        );

        /*
    ============================================================
    [4] TOP NATIONAL
    ============================================================
    */
        $topNational = array_slice($classement, 0, 10);

        /*
    ============================================================
    [5] RANKING UR
    ============================================================
    */
        $urRanking = $this->buildURRanking($classement);
        $urRankingNational = $this->buildURRanking($classement, true);

        $rankUR = $this->extractURRank($urRanking, $userUR);
        $rankURNational = $this->extractURRank($urRankingNational, $userUR);

        /*
    ============================================================
    [6] KPIs (FPF + UR)
    ⚠️ candidat extraction future → KPIService
    ============================================================
    */
        [$globalFPF, $globalUR, $comparison] =
            $this->buildKpis($classement, $urClubs, $userUR, $rankUR, $rankURNational);

        /*
    ============================================================
    [7] MATRICES COMPETITIONS
    ============================================================
    */
        $matrices = $this->buildCompetitionMatrices($rows);

        /*
    ============================================================
    [8] INSIGHTS
    ⚠️ candidat extraction → InsightService
    ============================================================
    */
        $insights = $this->buildInsights($classement, $urClubs, $ur);

        /*
    ============================================================
    [9] LABELS CLUBS (fallback mapping)
    ============================================================
    */
        $clubLabels = $this->buildClubLabels($urClubs, $matrices);

        /*
    ============================================================
    [10] OBSERVATOIRE CLUBS
    ⚠️ candidat extraction → ObservatoryService
    ============================================================
    */
        $clubObservatory = $this->buildClubObservatory($urClubs);
        $obsSummary = $this->buildObservatorySummary($clubObservatory);

        /*
    ============================================================
    [11] CAPITAL EXCELLENCE
    ⚠️ candidat extraction → LaureateService
    ============================================================
    */
        $laureatePodiums = $this->buildLaureatePodiums();

        /*
    ============================================================
    [12] OUTPUT STRUCTURE
    ============================================================
    */
        return [
            'classementClubs' => $classement,
            'topNational' => $topNational,
            'urClubs' => $urClubs,
            'urRanking' => $urRanking,

            'globalFPF' => $globalFPF,
            'globalUR' => $globalUR,
            'comparison' => $comparison,

            'insights' => $insights,

            'competitionMatrixNational' => $matrices['national'],
            'competitionMatrixRegional' => $matrices['regional'],

            'clubColumns' => array_column($urClubs, 'numero'),
            'clubLabels' => $clubLabels,

            'clubObservatory' => $clubObservatory,
            'obsSummary' => $obsSummary,

            'dashboardLaureates' => $laureatePodiums,
        ];
    }

    /*
    ============================================================
    CORE BUSINESS (moteur principal)
    Agrégation clubs

    Classement UR
    ============================================================
    */

    private function aggregateByClub(
        array $rows
    ): array {
        $clubs = [];

        /*
    ==========================
    agrégation brute
    ==========================
    */

        $rows = array_filter($rows, function ($r) {
            return
                !empty($r['club_id']) &&
                !empty($r['ur']);
        });

        foreach ($rows as $r) {

            $r = ClubRow::normalize($r);

            $id = (int)$r['club_id'];

            if (!isset($clubs[$id])) {

                $clubs[$id] = [
                    'club_id' => $id,
                    'nom' => $r['nom'],
                    'numero' => $r['numero'],
                    'ur' => (int)$r['ur'],

                    'points' => 0,

                    'R' => 0,
                    'N2' => 0,
                    'N1' => 0,
                    'CDF' => 0,

                    'competR'   => 0,
                    'competN2'  => 0,
                    'competN1'  => 0,
                    'competCDF' => 0,

                    'total_images' => 0,

                    'authors' =>
                    (int)($r['authors'] ?? 0),

                    'motor_authors' =>
                    (int)($r['motor_authors'] ?? 0),
                ];
            }

            $points = (float)$r['points'];

            $clubs[$id]['points'] += $points;

            $clubs[$id]['total_images']
                += (int)($r['total_images'] ?? 0);

            $level = strtoupper(
                trim(
                    (string)($r['level'] ?? '')
                )
            );

            switch ($level) {

                case 'REGIONAL':
                    $clubs[$id]['R'] += $points;
                    $clubs[$id]['competR']++;   // ✅ ajout
                    break;

                case 'N2':
                    $clubs[$id]['N2'] += $points;
                    $clubs[$id]['competN2']++;   // ✅ ajout
                    break;

                case 'N1':
                    $clubs[$id]['N1'] += $points;
                    $clubs[$id]['competN1']++;   // ✅ ajout
                    break;

                case 'CDF':
                case 'COUPE':
                    $clubs[$id]['CDF'] += $points;
                    $clubs[$id]['competCDF']++;   // ✅ ajout
                    break;
            }
        }

        /*
        ==========================
        métriques observatoire
        ==========================
        */

        $totalImages =
            array_sum(
                array_column(
                    $clubs,
                    'total_images'
                )
            );

        $totalPoints =
            array_sum(
                array_column(
                    $clubs,
                    'points'
                )
            );


        foreach ($clubs as &$c) {

            $c['weight_pct'] =
                $totalImages
                ? round(
                    100 *
                        $c['total_images'] /
                        $totalImages,
                    2
                )
                : 0;


            $pointsPct =
                $totalPoints
                ? (
                    100 *
                    $c['points'] /
                    $totalPoints
                )
                : 0;


            $c['conversion'] =
                $c['weight_pct'] > 0
                ? round(
                    (
                        $pointsPct
                        /
                        $c['weight_pct']
                    ) * 100,
                    1
                )
                : 100;


            $authors = max(
                1,
                (int)$c['authors']
            );

            $c['intensity'] = round(
                $c['total_images'] /
                    $authors,
                1
            );


            /*
        nouveau depth index
        */
            $c['depth_pct'] =
                $c['authors'] > 0
                ? round(
                    100 *
                        $c['motor_authors']
                        /
                        $c['authors'],
                    1
                )
                : 0;


            /*
        bonus élite
        */
            $bonus = 0;

            if ($c['N1'] > 0) {
                $bonus += 3;
            }

            if ($c['CDF'] > 0) {
                $bonus += 5;
            }

            $c['elite_bonus'] = $bonus;


            $depthBonus = 0;

            if ($c['depth_pct'] >= 70) {
                $depthBonus = 5;
            } elseif ($c['depth_pct'] >= 50) {
                $depthBonus = 3;
            } elseif ($c['depth_pct'] >= 30) {
                $depthBonus = 1;
            }

            $c['global_index'] = round(
                $c['conversion']
                    +
                    $bonus
                    +
                    $depthBonus,
                1
            );


            /*
            profils enrichis
            */

            /*
            ============================================================
            Typologie finale V1 (stable)
            ============================================================
            */

            /* faibles conversions -> toujours avant tout */
            if (
                $c['conversion'] < 99
            ) {

                $c['profile'] = 'Sous potentiel';
            }

            /* gros clubs + élite distribuée */ elseif (
                $c['weight_pct'] >= 10 &&
                $c['conversion'] >= 102 &&
                $c['depth_pct'] >= 35
            ) {

                $c['profile'] = 'Locomotive élite';
            }

            /* excellence largement répartie */ elseif (
                $c['depth_pct'] >= 60 &&
                $c['conversion'] >= 100
            ) {

                $c['profile'] = 'Elite diffuse';
            }

            /* un ou peu de moteurs tirent le club */ elseif (
                $c['motor_authors'] >= 1 &&
                $c['depth_pct'] < 20 &&
                $c['conversion'] >= 100
            ) {

                $c['profile'] = 'Elite concentrée';
            }

            /* gros collectif structurant */ elseif (
                $c['weight_pct'] >= 10 &&
                $c['conversion'] >= 100
            ) {

                $c['profile'] = 'Moteur collectif';
            }

            /* pas de relais élite : pas convertisseur premium */ elseif (
                $c['motor_authors'] == 0
            ) {

                $c['profile'] = 'Equilibré';
            }

            /* petits clubs très efficients */ elseif (
                $c['conversion'] >= 103 &&
                $c['weight_pct'] < 5
            ) {

                $c['profile'] = 'Convertisseur';
            } else {

                $c['profile'] = 'Equilibré';
            }

            $c['N1_CDF'] =
                $c['N1']
                +
                $c['CDF'];
        }

        unset($c);

        usort(
            $clubs,
            fn($a, $b) =>
            $b['global_index']
                <=>
                $a['global_index']
        );

        foreach ($clubs as $i => &$c) {
            $c['rang_obs'] = $i + 1;
        }

        unset($c);

        return array_values($clubs);
    }

    private function buildURRanking(array $classement, bool $nationalOnly = false): array
    {
        $urs = [];

        foreach ($classement as $c) {

            $ur = (int)($c['ur'] ?? 0);
            if (!$ur) continue;

            if (!isset($urs[$ur])) {
                $urs[$ur] = [
                    'ur'     => $ur,
                    'points' => 0,
                    'clubs'  => 0,
                    'images' => 0
                ];
            }

            // 🎯 choix du scope
            $points = $nationalOnly
                ? ($c['N2'] + $c['N1'] + $c['CDF'])
                : $c['points'];

            $urs[$ur]['points'] += $points;

            $urs[$ur]['clubs']++;

            // tu peux aussi filtrer les images si besoin plus tard
            $urs[$ur]['images'] += (int)($c['total_images'] ?? 0);
        }

        $urs = array_values($urs);

        usort(
            $urs,
            fn($a, $b) =>
            $b['points'] <=> $a['points']
        );

        foreach ($urs as $i => &$row) {
            $row['rank'] = $i + 1;
        }

        unset($row);

        return $urs;
    }

    private function buildCompetitionMatrices(array $rows): array
    {
        $national = [];
        $regional = [];

        foreach ($rows as $r) {

            $comp   = trim((string)$r['competition_nom']);
            $club = (string)($r['numero'] ?? $r['club_id'] ?? '0');
            $points = (float)$r['points'];

            $level = strtoupper(
                trim((string)($r['level'] ?? ''))
            );

            /*
        référence correcte
        */
            if ($level === 'REGIONAL') {
                $target = &$regional;
            } else {
                $target = &$national;
            }

            if (!isset($target[$comp])) {

                $target[$comp] = [
                    'winner_club'   => null,
                    'winner_author' => null,
                    'winner_points' => 0,
                    'scores'        => []
                ];
            }

            /*
        cumul points club
        */
            if (
                !isset(
                    $target[$comp]['scores'][$club]
                )
            ) {
                $target[$comp]['scores'][$club] = 0;
            }

            $target[$comp]['scores'][$club]
                += $points;


            /*
        club gagnant
        */
            if (
                $target[$comp]['scores'][$club]
                >
                $target[$comp]['winner_points']
            ) {

                $target[$comp]['winner_points'] =
                    $target[$comp]['scores'][$club];

                $target[$comp]['winner_club'] =
                    $club;

                /*
            essayer plusieurs champs auteur
            */
                $target[$comp]['winner_author'] =
                    $r['participant_nom']
                    ?? $r['participant_name']
                    ?? $r['author_name']
                    ?? $r['auteur_nom']
                    ?? null;
            }


            /*
        fallback nom club
        */
            if (
                empty($target[$comp]['winner_author'])
            ) {
                $target[$comp]['winner_author'] =
                    $r['nom'] ?? null;
            }
        }

        ksort($national);
        ksort($regional);

        return [
            'national' => $national,
            'regional' => $regional
        ];
    }

    /*
    ============================================================
    KPI & METRICS
        INSIGHTS & ANALYTICS
    ============================================================
    */

    private function buildInsights(
        array $classement,
        array $urClubs,
        int $ur
    ): array {
        $topUR =
            array_filter(
                $classement,
                fn($c) =>
                $c['ur'] == $ur
                    &&
                    $c['rang'] <= 50
            );

        $underperforming =
            array_filter(
                $urClubs,
                function ($c) {

                    if (
                        $c['total_images'] < 20
                    ) {
                        return false;
                    }

                    return (
                        $c['points']
                        /
                        max(
                            1,
                            $c['total_images']
                        )
                    ) < 8;
                }
            );

        foreach ($urClubs as &$c) {

            $c['efficiency'] =
                $c['total_images']
                ?
                round(
                    $c['points']
                        /
                        $c['total_images'],
                    2
                )
                :
                0;
        }
        unset($c);

        usort(
            $urClubs,
            fn($a, $b) =>
            $b['efficiency']
                <=>
                $a['efficiency']
        );

        return [

            'top_ur_national'
            =>
            array_values(
                $topUR
            ),

            'underperforming'
            =>
            array_values(
                $underperforming
            ),

            'top_efficiency'
            =>
            array_slice(
                $urClubs,
                0,
                10
            )
        ];
    }

    /*
    ============================================================
    OBSERVATOIRE
    ============================================================
    */
    private function buildClubObservatory(
        array $urClubs
    ): array {
        $totalImages =
            array_sum(
                array_column(
                    $urClubs,
                    'total_images'
                )
            );

        $totalPoints =
            array_sum(
                array_column(
                    $urClubs,
                    'points'
                )
            );

        foreach ($urClubs as &$c) {

            $partImages =
                $totalImages
                ? ($c['total_images'] / $totalImages) * 100
                : 0;

            $partPoints =
                $totalPoints
                ? ($c['points'] / $totalPoints) * 100
                : 0;

            $conversion =
                $partImages > 0
                ? ($partPoints / $partImages) * 100
                : 100;

            $c['part_images'] =
                round($partImages, 2);

            $c['part_points'] =
                round($partPoints, 2);

            $c['conversion'] =
                round($conversion, 1);

            $c['motor'] =
                round(
                    ($partImages + $partPoints) / 2,
                    2
                );

            /*
        🔥 manquait ici
        */
            $c['observatory_score'] =
                round(
                    $c['conversion']
                        +
                        ($c['elite_bonus'] ?? 0),
                    1
                );
        }

        unset($c);

        usort(
            $urClubs,
            fn($a, $b) =>
            $b['observatory_score']
                <=>
                $a['observatory_score']
        );

        foreach ($urClubs as $i => &$c) {
            $c['rang_obs'] = $i + 1;
        }

        unset($c);

        return $urClubs;
    }

    private function buildObservatorySummary(
        array $clubs
    ): array {

        return [

            'weight' => $clubs[0]['part_images'] ?? 0,

            'conversion' => round(
                array_sum(
                    array_column($clubs, 'conversion')
                ) / max(1, count($clubs)),
                1
            ),

            'motor' => $clubs[0]['motor'] ?? 0

        ];
    }

    /*
    ============================================================
    EXCELLENCE / PODIUMS
    ============================================================
    */
    private function buildLaureatePodiums(): array
    {
        $db = \Config\Database::connect();

        $rows = $db->query("
        SELECT
            c.nom AS competition,
            p.place,
            pa.nom,
            pa.prenom,
            cl.numero AS club,
            p.note_totale AS total,
            1 AS nb_photos,

            x.field_size

        FROM photos p

        JOIN competitions c
            ON c.id = p.competitions_id

        JOIN participants pa
            ON pa.id = p.participants_id

        JOIN clubs cl
            ON cl.id = pa.clubs_id

        JOIN (
            SELECT competitions_id, COUNT(*) AS field_size
            FROM photos
            GROUP BY competitions_id
        ) x
            ON x.competitions_id = p.competitions_id

        WHERE p.place <= 5
            AND p.note_totale > 0
            AND p.disqualifie = 0

        ORDER BY c.nom, p.place ASC
    ")->getResultArray();


        $out = [];

        foreach ($rows as $r) {

            $comp = $r['competition'];

            /*
        =====================================================
        INIT COMPETITION
        =====================================================
        */
            if (!isset($out[$comp])) {

                $field = (int)$r['field_size'];

                if ($field >= 900) {
                    $label = '🔥 Elite';
                    $class = 'perf-good';
                } elseif ($field >= 700) {
                    $label = '▲ Haute';
                    $class = 'perf-good';
                } elseif ($field >= 300) {
                    $label = '● Dense';
                    $class = 'perf-mid';
                } else {
                    $label = '○ Spécial';
                    $class = 'perf-low';
                }

                $out[$comp] = [
                    'competition'    => $comp,
                    'field_size'     => $field,
                    'density_label'  => $label,
                    'density_class'  => $class,

                    'gold'   => null,
                    'silver' => null,
                    'bronze' => null,

                    'top5'   => [],
                    'strikes' => [],
                ];
            }

            $author = trim($r['prenom'] . ' ' . $r['nom']);
            $place  = (int)$r['place'];

            /*
        =====================================================
        TOP 5 (image réel)
        =====================================================
        */
            $out[$comp]['top5'][] = [
                'author' => $author,
                'place'  => $place
            ];

            /*
        =====================================================
        PODIUM (place réel)
        =====================================================
        */
            $slot = null;

            if ($place === 1) {
                $slot = 'gold';
            } elseif ($place === 2) {
                $slot = 'silver';
            } elseif ($place === 3) {
                $slot = 'bronze';
            }

            if ($slot) {
                $out[$comp][$slot] = [
                    'author'    => $author,
                    'club'      => $r['club'],
                    'total'     => (int)$r['total'],
                    'nb_photos' => 1 // image unique
                ];
            }
        }

        /*
    =====================================================
    DETECTION STRIKES (≥2 dans top5)
    =====================================================
    */
        foreach ($out as &$comp) {

            $counts = [];

            foreach ($comp['top5'] as $a) {
                $name = $a['author'];
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }

            foreach ($counts as $name => $count) {
                if ($count >= 2) {
                    $comp['strikes'][] = $name;
                }
            }
        }
        unset($comp);

        return array_values($out);
    }

    /*

    =====================================================
    HELPERS
    =====================================================
    */
    private function extractURRank(array $ranking, int $ur): ?int
    {
        foreach ($ranking as $r) {
            if ((int)$r['ur'] === $ur) {
                return $r['rank'];
            }
        }
        return null;
    }

    private function buildKpis(array $classement, array $urClubs, int $ur, ?int $rankUR, ?int $rankURNational): array
    {

        /*
        ------------------------------------------------------------
        NATIONAL
        ------------------------------------------------------------
        */

        $annee = 2026;
        $globalFPF = [

            'nb_clubs' =>
            count($classement),

            'nb_points' =>
            array_sum(
                array_column(
                    $classement,
                    'points'
                )
            ),

            'nb_images' =>
            array_sum(
                array_column(
                    $classement,
                    'total_images'
                )
            ),


            // si disponible dans dataset clubs
            'nb_authors' => (int)(
                $this->db->query("
        SELECT COUNT(DISTINCT LEFT(ean,10)) total
        FROM photos
        WHERE competitions_id IN (
            SELECT competition_id
            FROM competition_meta
            WHERE saison = ?
              AND level IN ('N2','N1','CDF')
                AND is_official = 1
        )
    ", [$annee])->getRowArray()['total'] ?? 0
            ),
        ];


        /*
        ------------------------------------------------------------
        UR22
        ------------------------------------------------------------
        */

        $clubIds = array_column($urClubs, 'numero');

        $clubList = !empty($clubIds)
            ? implode(',', array_map('intval', $clubIds))
            : '0';

        $globalUR = [

            'nb_clubs' =>
            count($urClubs),

            'nb_points' =>
            array_sum(
                array_column(
                    $urClubs,
                    'points'
                )
            ),

            'nb_images' =>
            array_sum(
                array_column(
                    $urClubs,
                    'total_images'
                )
            ),
            'nb_authors' => (int)(
                $this->db->query("
    SELECT COUNT(DISTINCT LEFT(ean,10)) AS total
    FROM photos
    WHERE SUBSTRING(ean,3,4) IN ($clubList)
      AND competitions_id IN (
          SELECT competition_id
          FROM competition_meta
          WHERE saison = ?
            AND level IN ('N2','N1','CDF')
            AND is_official = 1
      )
", [$annee])->getRowArray()['total'] ?? 0
            ),
        ];


        /*
        ------------------------------------------------------------
        Clubs engagés en compétitions nationales
        (au moins 1 point ou 1 image en national)
        ------------------------------------------------------------
        */

        $clubsEngaged = count(
            array_filter(
                $urClubs,
                fn($club) => (
                    ($club['competN2'] ?? 0) +
                    ($club['competN1'] ?? 0) +
                    ($club['competCDF'] ?? 0)
                ) > 0
                    ||
                    (
                        ($club['N2'] ?? 0) +
                        ($club['N1'] ?? 0) +
                        ($club['CDF'] ?? 0)
                    ) > 0
            )
        );

        $engagementRate =
            $globalUR['nb_clubs']
            ? round(
                $clubsEngaged
                    /
                    $globalUR['nb_clubs']
                    * 100,
                0
            )
            : 0;

        /*
        ------------------------------------------------------------
        COMPARAISON / KPI CARDS
        ------------------------------------------------------------
        */

        $comparison = [

            // ancien poids UR conservé
            'ratio_points' =>
            $globalFPF['nb_points']
                ? round(
                    $globalUR['nb_points']
                        /
                        $globalFPF['nb_points']
                        * 100,
                    1
                )
                : 0,

            'ratio_images' =>
            $globalFPF['nb_images']
                ? round(
                    $globalUR['nb_images']
                        /
                        $globalFPF['nb_images']
                        * 100,
                    1
                )
                : 0,

            /*
    TOP UR
    */
            'rank_ur_national' => $rankURNational,

            'rank_ur' =>
            $rankUR ?? null,

            'nb_authors_ranked' =>
            $globalUR['nb_authors'],


            /*
    FOCUS UR22
    */
            'clubs_engaged' =>
            $clubsEngaged,

            'engagement_rate' =>
            $engagementRate,
        ];


        $comparison['delta'] =
            $comparison['ratio_points']
            -
            $comparison['ratio_images'];

        return [$globalFPF, $globalUR, $comparison];
    }

    private function buildClubLabels(array $urClubs, array $matrices): array
    {
        $labels = [];

        foreach ($urClubs as $club) {
            $key = $club['numero'] ?? $club['club_id'];
            $labels[$key] = $club['nom'];
        }

        foreach ([$matrices['national'], $matrices['regional']] as $matrix) {
            foreach ($matrix as $data) {

                $winner = $data['winner_club'] ?? null;

                if ($winner && !isset($labels[$winner])) {
                    $labels[$winner] = 'Club ' . $winner;
                }

                foreach ($data['scores'] ?? [] as $club => $pts) {
                    if (!isset($labels[$club])) {
                        $labels[$club] = 'Club ' . $club;
                    }
                }
            }
        }

        return $labels;
    }
}
