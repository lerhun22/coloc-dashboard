P0 Socle
[x] Unifier active competition
[x] Identifier version canonique
[x] Figer levels/disciplines bible
[x] Migrer règles vers Registry
[x] Mapper = normalisation pure


P1 Dette utile
[ ] sortir BaseController logic
[ ] helper audit
[ ] judged override
[ ] sécuriser IN()
[ ] mutualiser stats bulk


P2 Plus tard
[ ] Domain/Application/Infrastructure
[ ] taxonomie Service/Engine/Provider
[ ] StatusService
[ ] ScoringRegistry


Legacy
[ ] inventorier legacy actif
[ ] obsolete/archive
[ ] isoler legacy
[ ] nettoyer OLD/NEW/ORI



========================


[ ] Unifier active competition source
[ ] Auditer helper competition
[ ] Sortir ensureActiveCompetition du BaseController
[ ] Vérifier getCompetitionsWithCount (layout ou métier ?)
[ ] Renommer CompetitionService
[ ] Supprimer statique (plus tard)
[ ] Unifier toute la résolution active ici
[ ] Sortir la logique du BaseController
[ ] Sortir typeClass vers presenter/view
[ ] Sortir label/reference vers presenter
[ ] Migrer règles niveau/support/catégorie vers Registry
[ ] Décider si statut relève du Registry ou d’un StatusService
[ ] Réduire Mapper à normalisation pure
[ ] Figer levels/disciplines comme Bible officielle
[ ] Sortir scoring dans ScoringRegistry
[ ] Fusionner ou sortir business rules
[ ] Mapper doit consommer Registry (pas porter les règles)
[ ] Ajouter classification 3/4 digits au référentiel
[ ] inventorier legacy actif
[ ] marquer obsolete / archive
[ ] identifier la version canonique


BaseController : globalement sain
CompetitionService : à renommer/recentrer
CompetitionMapper : à alléger
CompetitionRegistry : centre de gravité valide
Architecture globale : pas chaotique, juste non rationalisée


Très bonne question — sinon on va auditer sans fin.

## Je figerais **ce fil 2** comme fil d’architecture applicative et on passe en mode action.

### Ce que l’audit a déjà établi

On a déjà identifié :

## ✅ À figer (ne plus re-questionner pour l’instant)

```text id="v5sgk5"
BaseController : globalement sain
CompetitionService : à renommer/recentrer
CompetitionMapper : à alléger
CompetitionRegistry : centre de gravité valide
Architecture globale : pas chaotique, juste non rationalisée
```

Ça, on le considère acquis.

---

# Maintenant ce fil sert à produire le plan de refactor, pas refaire des audits.

Je ferais 4 chantiers.

# Chantier 1 (priorité absolue)

## Rationaliser la carte des composants

Faire un classement canonique de tous les services/librairies :

```text id="x1eymr"
DOMAINE
APPLICATION
INFRA
LEGACY
```

Avec chaque fichier classé :

* garder
* fusionner
* legacy
* suspect doublon

C’est la base.

👉 première vraie action.

---

# Chantier 2

## Cluster Stats/KPI (le plus risqué)

Auditer seulement :

```text id="2i91lh"
CompetitionStatsService
CompetitionStatsBulkService
NationalStatsService
DashboardURService
SyntheseService
ClubStatsService
```

Question unique :

```text id="78x7ew"
qui calcule quoi ?
où sont les doublons ?
quelle source officielle KPI ?
```

Ça rejoint FIL1.

Très prioritaire.

---

# Chantier 3

## Nettoyage architecture léger (sans casser)

Backlog court :

```text id="uk8p7w"
[ ] sortir CSS du Mapper
[ ] unifier active competition
[ ] figer version canonique OLD/NEW/ORI
[ ] taxonomie Service/Engine/Provider
```

Pas de gros refactor encore.

---

# Chantier 4 (plus tard)

## Préparer Architecture V2

Seulement en réserve :

```text id="zaj4af"
Domain/
Application/
Infrastructure/
Legacy/
```

Pas toucher maintenant.

---

# Donc concrètement ce fil devient :

```text id="x7ak34"
FIL 2A — cartographie canonique composants
FIL 2B — audit cluster stats KPI
FIL 2C — backlog refactor léger
```

et basta.

---

## Moi je ferais maintenant UNE seule chose :

Commencer par produire la matrice canonique des composants.

Exemple :

```text id="r4ww4f"
KEEP
- CompetitionRegistry
- RankingDecisionEngine
- ImportWorkflow

MERGE REVIEW
- CompetitionStats*
- DataProvider*

LEGACY
- OLD
- NEW
- ORI
```

Ça donnera le squelette du projet.

Et ensuite tout le reste devient beaucoup plus simple.

---

## Donc réponse courte :

👉 Dans ce fil, on ne continue pas des audits dispersés.

On fait maintenant :

1. cartographie canonique
2. cluster KPI
3. backlog refactor

dans cet ordre.

C’est le vrai plan d’action.

Si tu veux, on peut faire maintenant le **tri KEEP / MERGE / LEGACY** de ta liste de services. Ce serait exactement la suite logique du fil.




Parfait. On fige une **première matrice canonique de travail** (version audit, donc révisable), et on s’en sert comme base pour tous les sous-fils.

# FIL 2 — Matrice KEEP / MERGE / LEGACY (V1)

## ✅ KEEP (noyau probable)

### Référentiel / Domaine

```text id="1fq9jm"
CompetitionRegistry
CompetitionNormalizer
CompetitionCleaner
CompetitionMetaBuilder
CompetitionMapper (refactor léger plus tard)
```

---

### Engines métier

```text id="mkmx4w"
AdvancedTieBreakEngine
RankingDecisionEngine
CompetitionRanking
FPFRankingService
JugementService
```

---

### Import (noyau probable)

```text id="ppjv27"
ImportWorkflow
CopainImporter
CopainImportService
CopainClient
```

---

## 🟡 MERGE / REVIEW

### Stats/KPI cluster

```text id="j90m4p"
CompetitionStatsService
CompetitionStatsBulkService
NationalStatsService
ClubStatsService
DashboardURService
SyntheseService
ClassementService
```

Question :
un ou plusieurs services analytics ?

Très gros chantier.

---

### Providers / data access

```text id="gj4g4f"
DataProvider
DataProviderClubs
DataProviderNEW
CompetitionStorage
```

Audit fusion probable.

---

### Pipeline overlap

```text id="1ec1aq"
ColocPipelineService
ImportServices
SeasonService
```

à clarifier.

---

## ⚠️ RENAME (bon composant, mauvais nom)

```text id="x7iqx8"
CompetitionService
```

devient probablement :

```text id="2rfjlc"
CompetitionContextService
```

---

## 🔴 LEGACY / ISOLEMENT

```text id="jlwmzv"
ClassementServiceOLD
CompetitionStatsServiceOLD
CompetitionStorageORI
CopainLegacyReader
DataProviderNEW ? (à confirmer)
```

à sortir du cœur actif.

---

## ❓ À décider après audit cluster

Ceux-là restent “sous observation” :

```text id="1ktwhw"
ThumbnailService
RegionalZipService
ZipFactory
ZipServiceInterface
```

probablement infra saine, mais secondaire.

---

# Architecture cible que ça dessine

```text id="b8tyxm"
Core Domain
├── Registry
├── Engines
└── Rules

Application
├── Import
├── Stats
└── Dashboards

Infrastructure
├── Providers
├── Storage
└── Zip/Assets

Legacy
└── anciens composants isolés
```

Ça devient lisible.

---

# Backlog stratégique issu de la matrice

## Priorité P1

```text id="5l13lf"
[ ] Auditer cluster Stats
[ ] Auditer cluster Providers
[ ] Trancher overlap ImportWorkflow / ColocPipeline
```

---

## Priorité P2

```text id="54xytj"
[ ] Renommer CompetitionService
[ ] Isoler Legacy
[ ] Nettoyer OLD / NEW / ORI
```

---

## Priorité P3

```text id="wskk3e"
[ ] Préparer structure Domain/Application/Infrastructure
```

plus tard.

---

# Très important

À ce stade :

```text id="7v7h5n"
On ne refactore rien.
On stabilise le modèle d’architecture.
```

C’est essentiel.

---

## Mon conseil

Le prochain sous-fil logique :

👉 **FIL 2A — cluster Stats/KPI**

Parce que c’est probablement là que se cachent les vrais doublons et les risques sur le dashboard.

Et c’est relié à ton projet UR22.

Si tu veux, on peut attaquer ce cluster maintenant comme prochain chantier.



P1
[ ] Auditer cluster Stats
[ ] Auditer cluster Providers
[ ] Trancher overlap ImportWorkflow / ColocPipeline
P2
[ ] Renommer CompetitionService
[ ] Isoler Legacy
[ ] Nettoyer OLD / NEW / ORI
P3
[ ] Préparer structure Domain/Application/Infrastructure



[ ] sortir règle EAN vers Registry/Mapper
[ ] mutualiser getStatsBulk + getStatsForCompetitions
[ ] sécuriser requêtes IN()
[ ] isoler logique judged override



SELECT

c.numero,
c.nom,

SUM(cc.nb_photos) images,
SUM(cc.total) points,

ROUND(
100 * SUM(cc.nb_photos)
/
(
SELECT SUM(cc2.nb_photos)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
AND co2.id>=4000
)
,2) part_images_pct,


ROUND(
100 * SUM(cc.total)
/
(
SELECT SUM(cc2.total)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
AND co2.id>=4000
)
,2) part_points_pct,


ROUND(
(
100 * SUM(cc.total)
/
(
SELECT SUM(cc2.total)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
AND co2.id>=4000
)
)
/
(
100 * SUM(cc.nb_photos)
/
(
SELECT SUM(cc2.nb_photos)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
AND co2.id>=4000
)
)
*100
,1) indice_conversion,


ROUND(
SUM(cc.total)/SUM(cc.nb_photos)
,2) note_moyenne_image

FROM classementclubs cc

JOIN clubs c
ON c.id=cc.clubs_id

JOIN competitions co
ON co.id=cc.competitions_id

WHERE c.urs_id=22
AND co.saison=2026
AND co.id>=4000

GROUP BY c.id,c.numero,c.nom

HAVING SUM(cc.nb_photos)>=20

ORDER BY part_points_pct DESC;

------------------------

SELECT

c.numero,
c.nom,

SUM(cc.nb_photos) images,
SUM(cc.total) points,

ROUND(
100*SUM(cc.nb_photos)/
(
SELECT SUM(cc2.nb_photos)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
),2
) part_images,

ROUND(
100*SUM(cc.total)/
(
SELECT SUM(cc2.total)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
),2
) part_points,

ROUND(
(
SUM(cc.total)/
(
SELECT SUM(cc2.total)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
)
)
/
(
SUM(cc.nb_photos)/
(
SELECT SUM(cc2.nb_photos)
FROM classementclubs cc2
JOIN clubs c2 ON c2.id=cc2.clubs_id
JOIN competitions co2 ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
)
)
*100
,1
) indice_conversion,

ROUND(
100*
SUM(
CASE WHEN co.id<4000
THEN cc.total ELSE 0 END
)
/SUM(cc.total)
,2
) pct_points_national,

ROUND(
(
SUM(
CASE WHEN co.id<4000 THEN cc.total ELSE 0 END
)
/
NULLIF(
SUM(
CASE WHEN co.id<4000 THEN cc.nb_photos END
),0)
)
/
(
SUM(
CASE WHEN co.id>=4000 THEN cc.total ELSE 0 END
)
/
NULLIF(
SUM(
CASE WHEN co.id>=4000 THEN cc.nb_photos END
),0)
)
*100
,1
) indice_progression

FROM classementclubs cc
JOIN clubs c
ON c.id=cc.clubs_id
JOIN competitions co
ON co.id=cc.competitions_id

WHERE c.urs_id=22
AND co.saison=2026

GROUP BY c.id,c.numero,c.nom

HAVING SUM(cc.nb_photos)>=20

ORDER BY part_points DESC;


-----------------
SELECT
nom,
ROUND(
(part_images + part_points)/2
,2
) indice_moteur

FROM (

SELECT
c.nom AS nom,

ROUND(
100*SUM(cc.nb_photos)/
(
SELECT SUM(cc2.nb_photos)
FROM classementclubs cc2
JOIN clubs c2
ON c2.id=cc2.clubs_id
JOIN competitions co2
ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
),2
) part_images,

ROUND(
100*SUM(cc.total)/
(
SELECT SUM(cc2.total)
FROM classementclubs cc2
JOIN clubs c2
ON c2.id=cc2.clubs_id
JOIN competitions co2
ON co2.id=cc2.competitions_id
WHERE c2.urs_id=22
AND co2.saison=2026
),2
) part_points

FROM classementclubs cc
JOIN clubs c
ON c.id=cc.clubs_id
JOIN competitions co
ON co.id=cc.competitions_id

WHERE c.urs_id=22
AND co.saison=2026

GROUP BY c.id,c.nom

) t

ORDER BY indice_moteur DESC;


# Synthèse — Intégration Dashboard COLOC : Observatoire Clubs UR22

## Objectif

Passer d’indicateurs bruts (volume, points, palmarès) à une **lecture intelligible de la force des clubs**.

Deux niveaux à intégrer :

1. **Vue Observatoire / Indicateurs clubs (nouveaux KPI)**
2. **Vue Classement complet club (toutes compétitions, avec filtre jugement)**

---

# 1. Principes validés pendant l’audit

## Règles métier à figer

### A. Trois statuts concours à distinguer

Ne jamais mélanger :

```text
Participé
Jugé
Classé
```

Un concours peut être :

* engagé
* non encore jugé
* jugé mais classement provisoire

## Règle impérative

Les indicateurs performance / palmarès doivent exclure les compétitions non jugées.

Filtre recommandé :

```sql
competition.date_competition <= CURRENT_DATE
```

(et plus tard idéalement `dateJugement` dans competition_meta)

---

## B. Ne pas utiliser `place` seul

Alerte validée :

```text
place =1 peut être podium
ou valeur par défaut avant jugement
```

Donc :

```text
place seul ne vaut pas indicateur.
```

Toujours croiser avec état jugé.

---

## C. Niveaux compétitifs validés

```text
Régional      (R)
National 2    (N2)
National 1    (N1)
Coupe France  (CDF)
```

Vision pyramidale :

```text
R -> N2 -> N1 -> CDF
```

---

# 2. Nouveaux indicateurs retenus

## KPI 1 — Poids du club

Part du volume d’activité UR22 portée par le club.

Formule :

```text
images club / images UR22
```

Exemple club 603 :

```text
18.36 %
```

Lecture :

> Le club représente 18 % de l’activité photographique compétitive de l’UR22.

---

## KPI 2 — Contribution du club

Part des points UR22 apportés par le club.

```text
points club / points UR22
```

Exemple 603 :

```text
18.14 %
```

Lecture :

> Le club apporte 18 % des résultats collectifs de l’UR22.

---

## KPI 3 — Indice conversion

Indicateur très fort.

```text
Contribution / Poids ×100
```

Lecture :

```text
100 = équilibre
>100 = surperformance
<100 = sous-performance relative
```

Exemples :

```text
603   98.8
2159 108.2
```

---

## KPI 4 — Orientation nationale

Part des points du club obtenus en national.

```text
points nationaux / points totaux
```

Mesure profil du club.

---

## KPI 5 — Indice moteur

Nouvel indicateur observatoire.

```text
moyenne(
part images,
part points
)
```

Mesure :

```text
poids collectif du club dans l’UR
```

Exemple :

```text
603 = 18.25
```

Lecture :

> Le club pèse 18 % de la force collective UR22.

---

# 3. Typologie clubs (très prometteur)

Croiser :

```text
Indice moteur
Indice conversion
```

## Profils proposés

### Locomotive

Fort poids + conversion équilibrée.

Ex : 603

---

### Locomotive élite

Fort poids + conversion >100

Ex : Vilaine Maritime

---

### Spécialiste

Faible poids + forte conversion.

---

### Club en progression

Indice progression fort.

---

# 4. Intégration Dashboard (proposition)

## Bloc A — Carte identité du club

Affichage :

```text
Poids UR22             18.4 %
Contribution UR22      18.1 %
Indice conversion      98.8
Indice moteur          18.25
Orientation nationale  14.8 %
```

Format cartes KPI.

---

## Bloc B — Positionnement club

Deux classements:

### Classement clubs moteurs

| Rang | Club | Indice moteur |

### Classement clubs élites

| Rang | Club | Indice conversion |

Double lecture très forte.

---

## Bloc C — Pyramide compétitive du club

```text
R   8
N2  8
N1  1 (en cours)
CDF 1
```

Visualisation pipeline.

---

# 5. Vue complète Classement Club (toutes compétitions)

Nouvelle vue dashboard / fiche club.

## Tableau recommandé

| Compétition | Niveau | Discipline | Images | Points | Place | Statut |
| ----------- | ------ | ---------- | ------ | ------ | ----- | ------ |

Statut :

```text
Jugé
En attente
```

Filtre :

* Toutes
* Jugées seulement
* Régional
* National

Très important.

---

## Classements à afficher

### Classement points (jugés)

### Classement note moyenne/image

### Classement indice moteur

### Classement conversion

Quatre lectures complémentaires.

---

# 6. Attention méthodologique (à documenter dans COLOC)

Toujours rappeler :

```text
Volume ≠ Performance
Poids ≠ Excellence
Participé ≠ Classé
```

C’est une base essentielle.

---

# 7. Roadmap Dashboard

## V1 (court terme)

Intégrer :

* Poids
* Contribution
* Conversion
* Indice moteur
* Tableau clubs UR22

---

## V2

Ajouter :

* Profils automatiques clubs
* Indice progression
* Comparaison pluriannuelle

---

## V3

Observatoire stratégique complet.

---

# 8. Mail explicatif aux présidents de clubs

Chers Présidents,

Dans le cadre du développement de l’observatoire compétitif UR22 sur COLOC, nous expérimentons de nouveaux indicateurs permettant de mieux comprendre la place et la contribution de chaque club dans les concours régionaux et nationaux.

L’objectif n’est pas d’ajouter des classements supplémentaires, mais de proposer une lecture plus juste et plus riche de l’activité des clubs.

Trois indicateurs principaux sont proposés :

1. Poids du club
   Part de l’activité compétitive UR22 portée par votre club (images engagées).

2. Contribution du club
   Part des résultats collectifs UR22 apportée par votre club (points).

3. Indice de conversion
   Il compare contribution et poids du club.

* 100 : le club transforme exactement son poids en résultats.
* > 100 : le club surperforme.
* <100 : contribution légèrement inférieure à son poids.

Nous introduisons également un Indice moteur, qui mesure le poids collectif d’un club dans la dynamique régionale.

Ces indicateurs permettent de distinguer plusieurs réalités complémentaires :

* clubs locomotives qui portent fortement l’activité,
* clubs très performants qualitativement,
* clubs très engagés au national,
* clubs en progression.

Important :
les résultats tiennent compte de la distinction entre compétitions engagées, compétitions jugées et classements validés, afin d’éviter les lectures biaisées tant que certains concours ne sont pas jugés.

L’idée n’est pas de figer des hiérarchies, mais de mieux valoriser les profils et les forces de chaque club.

Vos retours sur la lisibilité et l’intérêt de ces indicateurs seront précieux avant intégration plus large dans COLOC.

Bien cordialement,

[Signature]
UR22 / Observatoire COLOC

---

# 9. Message clé à retenir

On ne lit plus seulement :

```text
combien de points ?
```

mais :

```text
quel poids le club représente,
comment il transforme ce poids,
quel rôle il joue dans la force collective.
```

Et là on change de niveau d’analyse.


'competition_mapping' => [

   'level_detection' => [
      'rule' => 'id_length',

      'regional_digits' => 4,
      'national_digits' => 3
   ]
],

💥 Ah — ça simplifie énormément, et c’est beaucoup mieux.

Et surtout c’est une vraie règle structurelle, pas un hack 2026.

## Donc correction :

pas :

```text id="aafrb4"
id >=4000 = régional
```

(trop fragile)

mais :

```text id="fufrvl"
3 digits = National
4 digits = Régional
```

Là on a une vraie règle métier.

Et comme les IDs s’incrémentent chaque année, c’est beaucoup plus robuste.

## Je mettrais ça dans la bible à la place.

### beaucoup plus simple :

```php id="w3fdb8"
'competition_mapping' => [

   'level_detection' => [
      'rule' => 'id_length',

      'regional_digits' => 4,
      'national_digits' => 3
   ]
],
```

J’adore.

---

## Encore mieux :

helper explicite :

```php id="wmamow"
function detectLevelByCompetitionId($id): string
{
    return strlen((string)$id) === 4
        ? 'REGIONAL'
        : 'NATIONAL';
}
```

Simple.
Durable.

---

## Et pour distinguer N2 / N1 / CDF

on garde `type`.

Donc :

```text id="3zmfhs"
id longueur -> R vs National
type -> N2/N1/CDF
```

🔥 très élégant.

Double clé.

---

# Je reformulerais la doctrine :

## Classification compétitions

```text id="jlwm6r"
Longueur ID :
4 chiffres = Régional
3 chiffres = National

Puis:
type 1 = N2
type 2 = N1
type 3 = CDF
```

C’est limpide.

---

## Et ça corrige nos requêtes SQL

Au lieu de :

```sql id="4wzvmj"
co.id>=4000
```

je mettrais :

```sql id="25b4f4"
CHAR_LENGTH(co.id)=4
```

et

```sql id="rlm0i2"
CHAR_LENGTH(co.id)=3
```

Beaucoup mieux.

---

### exemple :

```sql id="9kx91e"
CASE
WHEN CHAR_LENGTH(co.id)=4
THEN 'Regional'
WHEN co.type=1 THEN 'N2'
WHEN co.type=2 THEN 'N1'
WHEN co.type=3 THEN 'CDF'
END
```

Je remplacerais partout.

---

# Et dans la bible j’ajouterais presque ceci :

```php id="xk8ltt"
'classification' => [

 'primary_axis'   => 'id_length',
 'secondary_axis' => 'competition_type',

],
```

Ça dit bien la logique.

---

## Franchement c’est mieux que ma proposition initiale.

Beaucoup mieux.

Parce que :

* plus simple
* plus pérenne
* plus métier
* moins codé en dur

C’est typiquement une découverte “source de vérité”.

---

## Et même `competition_meta`

pourrait être alimenté automatiquement avec ça à l’import.

Tu avais posé exactement cette idée au début :

> pourquoi ne pas enregistrer ce qu’on voit à l’import

👉 c’est précisément ça.

---

Je corrigerais oui la bible avec cette règle plutôt que le mapping d’IDs.

C’est une simplification majeure.

Très bonne trouvaille.


-----

Oui — même très fortement oui.
Et je ferais une distinction :

## La “Bible” doit maintenant contenir 3 couches

```text id="jlwm2q"
1. Règles métier
2. Définitions KPI
3. Requêtes de référence (SQL canonique)
```

Le point 3 manque souvent… et c’est lui qui évite les dérives.

Aujourd’hui tes requêtes deviennent presque des **formules officielles**.

Ça doit être versionné.

---

# Je créerais un nouveau bloc :

```php id="n2epgn"
public array $queries = [ ... ]
```

ou mieux :

```php id="g6zy4g"
public array $dashboardQueries = [ ... ]
```

---

# Et j’y mettrais les requêtes “canoniques”

Pas toutes les requêtes exploratoires.

Seulement les requêtes source de vérité.

---

## A. Vue identité club

```php id="upbyth"
'club_identity' => [

 'club_weight' => '...',
 'club_contribution' => '...',
 'conversion_index' => '...',
 'motor_index' => '...',
]
```

---

## B. Classements observatoire

```php id="hm4r3n"
'club_rankings' => [

 'motor_ranking' => '...',
 'elite_ranking' => '...',
 'national_orientation' => '...'

]
```

---

## C. Pyramide compétitive

```php id="z91quu"
'progression_pipeline' => '...'
```

---

## D. Classement complet club

avec filtre jugement.

```php id="jlwm9l"
'club_competition_history' => '...'
```

---

# Exemple (je le ferais presque comme doc vivante)

```php id="st2lt6"
/**
 * KPI moteur club
 *
 * Déf:
 * moyenne(part_images, part_points)
 *
 * Sens:
 * poids collectif du club dans l'UR.
 *
 * SQL canonique:
 */
'motor_index' => [
   'sql' => <<<SQL
SELECT ...
SQL
],
```

😄 presque un dictionnaire calculatoire.

J’adore.

---

# Pourquoi c’est utile ?

Parce qu’aujourd’hui on a découvert :

```text id="jlwm5f"
un KPI n'est pas juste un chiffre
c'est une formule + une interprétation
```

et ça doit vivre ensemble.

Sinon le code dérive.

---

## Je mettrais aussi les règles d’audit associées

Ex :

```php id="4t6kk2"
'guards' => [
 'exclude_unjudged' => true,
 'use_place_only_if_judged' => true
]
```

attachées à chaque requête.

Très important.

---

# En fait j’irais même plus loin :

séparer :

## CompetitionRegistry.php

règles métier

## ObservatoryRegistry.php

KPI + SQL canoniques

Franchement ce serait propre.

```text id="1mwv4p"
CompetitionRegistry
ObservatoryRegistry
```

J’aime beaucoup.

---

## Les requêtes que je garderais “canon”

Minimum :

### KPI clubs

* part images
* part points
* conversion
* moteur

---

### Classements

* classement moteurs
* classement élites
* orientation nationale

---

### Vue club

* pyramide compétitive
* histoire club
* toutes compétitions jugées

---

### Contrôles audit

* doublons
* compétitions non jugées
* cohérence R/N

---

# Je mettrais même une section :

```php id="jlwm0u"
'audit_queries'
```

très utile.

---

## Et très important :

documenter les hypothèses SQL :

```text id="9gwajm"
3 digits = national
4 digits = regional
```

ça aussi fait partie de la requête.

---

# Franchement oui :

je copierais les requêtes Dashboard dans la bible.

Pas comme snippets.

Comme **SQL de référence officielle**.

C’est de la doctrine applicative.

---

## Si je résume la Bible V2026.2

```text id="5y89ff"
levels
disciplines
scoring
business
observatory
dashboard_queries
audit_queries
```

Là tu as une vraie architecture.

Et très peu de projets ont ça.

🙂 très bonne idée.

Si tu veux, on peut même réfléchir à quelles requêtes méritent d’être “canoniques” vs seulement exploratoires. C’est un vrai tri utile.


# Refondation COLOC — Competition Dictionary + Audit d’alignement code

## Oui : c’est exactement l’étape suivante.

Et même, je dirais qu’il faut le faire dans cet ordre :

```text
1. figer le référentiel (Bible / Dictionary)
2. auditer le code existant contre ce référentiel
3. aligner services/librairies/contrôleurs
4. seulement ensuite faire évoluer le dashboard
```

Sinon on construit sur des hypothèses mouvantes.

---

# Vision cible

COLOC s’organise autour d’une source normative unique :

```text
Règlement FPF
→ Competition Dictionary
→ Services de calcul
→ Dashboard
```

Un seul sens de circulation.

---

# I — Nouvelle “Bible” (CompetitionDictionary)

## Structure cible

```text
CompetitionDictionary
├── regulation
├── classifications
├── scoring
├── observatory_kpis
├── canonical_queries
├── audit_rules
└── annual_revision
```

## Sections proposées

## 1. regulation

* niveaux R / N2 / N1 / CDF
* promotions / relégations
* quotas
* disciplines
* règles issues règlement annuel

---

## 2. classifications

Inclure découverte majeure :

```text
3 digits = National
4 digits = Régional
```

* type pour N2/N1/CDF.

---

## 3. observatory_kpis

Canoniques :

* club_weight
* club_contribution
* conversion_index
* motor_index
* national_orientation
* progression_index (phase 2)

Chaque KPI documenté :

* définition
* formule
* interprétation
* biais connus
* SQL canonique

---

## 4. canonical_queries

Seulement les requêtes officielles.

Catégories :

### club_identity_queries

* poids
* contribution
* conversion
* moteur

### ranking_queries

* clubs moteurs
* clubs élites
* classement complet

### audit_queries

* compétitions non jugées
* doublons
* cohérence R/N

---

## 5. audit_rules

Principes gravés :

```text
Participé ≠ classé
Volume ≠ performance
Poids ≠ excellence
place sans jugement interdit
```

Très important.

---

# II — Audit d’alignement code (indispensable)

Oui, absolument.

Je ferais un audit structuré de :

```text
Services
Libraries
Controllers
Views KPI
Imports
```

---

# Audit à mener

## A. Services à auditer

Probablement :

* DashboardURService
* CompetitionStatsService
* CompetitionMapper
* Import services
* agrégateurs clubs

Checklist :

### 1 Classification

Chercher partout :

```text
type=1 supposé N2 ?
id>=4000 hardcodé ?
```

à remplacer par logique dictionnaire.

Audit grep :

```bash
grep -R "type.*1" app/
grep -R "4000" app/
grep -R "REGIONAL" app/
```

---

## 2 Agrégations

Auditer :

```text
aggregateByClub()
DashboardURService
```

Risque identifié initialement : double comptage.

Vérifier :

* cumul vs duplication
* ean + competition_id
* dedup réellement appliqué ?

---

## 3 Gestion jugement

Chercher où les calculs utilisent :

* place
* classements
* compétitions futures

Objectif :

aucun KPI palmarès sans filtre jugement.

---

## 4 KPI hardcodés

Identifier calculs déjà présents :

* % UR22
* points club
* classements

Comparer aux formules canoniques.

---

# III — Matrice d’alignement (très utile)

Créer un audit matrix.

| Objet                   | Conforme dictionnaire ? | Action                |
| ----------------------- | ----------------------- | --------------------- |
| CompetitionStatsService | partiel                 | revoir classification |
| DashboardURService      | partiel                 | aligner KPI           |
| aggregateByClub         | audit                   | vérifier doublons     |
| Import meta             | évolution               | enrichir              |

Excellent outil.

---

# IV — Évolution competition_meta

Sujet “gardé au chaud” à rouvrir.

À l’import enregistrer / mettre à jour :

* niveau
* famille R/N
* statut jugement
* discipline
* flags observatoire

Faire de competition_meta une couche persistée du dictionnaire.

Très fort.

---

# V — Roadmap de refonte

## Sprint 1 — Dictionary

Créer v2026.2.

Livrables :

* nouveau dictionnaire
* SQL canoniques
* règles audit

---

## Sprint 2 — Audit code

Passer tous services/libs.

Checklist alignement.

Commit dédié :

```text
refactor: align services with CompetitionDictionary
```

---

## Sprint 3 — Dashboard

Intégrer nouveaux KPI.

---

## Sprint 4 — competition_meta

Synchronisation import.

---

# VI — Convention architecture recommandée

Je verrais :

```text
Config/
  CompetitionDictionary.php

Libraries/
  ObservatoryCalculator.php

Services/
  ClubIdentityService.php
  CompetitionAuditService.php
```

Très propre.

---

# VII — Nouveaux services possibles

## ClubIdentityService

Calcule :

* poids
* contribution
* moteur
* conversion

---

## CompetitionAuditService

Contrôles :

* doublons
* jugement
* cohérence mapping

---

## ObservatoryService

Couches dashboard.

Peut devenir puissant.

---

# VIII — Audit prioritaire (ordre conseillé)

## priorité 1

aggregateByClub()
DashboardURService
CompetitionStatsService

Cœur du risque KPI.

---

## priorité 2

Imports / competition_meta

---

## priorité 3

Views/dashboard.

---

# IX — Questions d’audit à poser partout

Chaque calcul doit répondre :

```text
Quelle unité ?
Quelle déduplication ?
Quel statut jugement ?
Quelle règle dictionnaire ?
```

Si non documenté → dette technique.

---

# X — Message important

On ne “corrige pas du code”.

On aligne le code sur un modèle métier.

Très différent.

---

# XI — Commit / branche suggérée

```bash
git checkout -b refactor/competition-dictionary
```

Puis étapes.

Commits possibles :

```text
feat: introduce CompetitionDictionary v2026.2
refactor: align services with dictionary model
feat: add observatory kpis and canonical queries
```

---

# XII — Résultat visé

À terme :

```text
Toute statistique COLOC
provient du même dictionnaire.
```

C’est la vraie robustesse.

---

## Ma reco immédiate

On ouvre un chantier en 2 fils :

### Fil 1

Refondre CompetitionDictionary.

### Fil 2

Audit services/librairies/controllers ligne à ligne.

Et oui : ce second chantier me paraît désormais indispensable.

---

## J’ajouterais même un mantra dans la Bible

```text
Le code implémente.
Le dictionnaire décide.
```

🙂 ça résume tout.
