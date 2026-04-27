<?php

namespace App\Services;

class DataProviderClubs
{
    public function getAnnualData(int $annee): array
    {
        $db = \Config\Database::connect();

        $sql = "
SELECT

cc.total AS points,
cc.nb_photos AS total_images,
cc.place AS rank,

c.id AS club_id,
c.nom,
c.numero,
c.urs_id AS ur,

comp.nom AS competition_nom,

cm.level,
cm.discipline,
cm.support,

COALESCE(a.authors_total,0) authors,
COALESCE(a.motor_authors,0) motor_authors

FROM classementclubs cc

JOIN clubs c
ON c.id=cc.clubs_id

JOIN competitions comp
ON comp.id=cc.competitions_id

LEFT JOIN competition_meta cm
ON cm.competition_id=comp.id


LEFT JOIN (

SELECT

LEFT(p.ean,6) club_code,

COUNT(
DISTINCT participants_id
) authors_total,

COUNT(
DISTINCT CASE
WHEN cm2.level IN('N1','CDF')
THEN participants_id
END
) motor_authors

FROM photos p

JOIN competitions c2
ON c2.id=p.competitions_id

LEFT JOIN competition_meta cm2
ON cm2.competition_id=c2.id

WHERE c2.saison=?

GROUP BY
LEFT(p.ean,6)

) a

ON a.club_code=
CONCAT(
LPAD(c.urs_id,2,'0'),
LPAD(c.numero,4,'0')
)

WHERE comp.saison=?
";

        return $db
            ->query(
                $sql,
                [$annee, $annee]
            )
            ->getResultArray();
    }
}
