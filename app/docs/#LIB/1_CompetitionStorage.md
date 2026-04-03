je souhaite en fait pour cette nouvelle lib/service/service - une documentation
1: Général : Date - NOM - localisation architecture CI
2: Objectifs - Risques
3: Fonctions sous la forme ex  
/**
 * Retourne le chemin final (compatibilité ancien système)
 * - utilise folder si existant
 * - sinon utilise nouveau système
 */
public function resolvePath($competition): string


----------------------------------------------

/**
 * ============================================================
 * 📦 SERVICE : CompetitionStorage
 * ============================================================
 *
 * 📅 Date        : 2026-04
 * 👤 Auteur      : (à compléter)
 * 📍 Localisation : app/Libraries/CompetitionStorage.php
 * 🧱 Architecture : CodeIgniter 4 (CI4)
 *
 * ============================================================
 * 🎯 OBJECTIFS
 * ============================================================
 *
 * Centraliser la gestion du système de fichiers des compétitions.
 *
 * Cette classe est la SEULE responsable de :
 *
 * ✔ Génération des chemins (photos, thumbs, pdf…)
 * ✔ Création de la structure de dossiers
 * ✔ Compatibilité avec l’ancien système (champ "folder")
 * ✔ Uniformisation des accès filesystem (N / R)
 *
 * 👉 Elle remplace :
 * ❌ $competitionFolder
 * ❌ $photoPath
 * ❌ mkdir dispersés
 * ❌ chemins codés en dur
 *
 *
 * ============================================================
 * 🧠 CONCEPT
 * ============================================================
 *
 * Le filesystem devient la source de vérité pour les fichiers.
 *
 * La base de données :
 * ✔ n’est pas modifiée
 * ✔ reste une source de données métier uniquement
 *
 * Tous les états sont calculés dynamiquement :
 * - présence photos
 * - présence thumbs
 * - état jugement
 *
 *
 * ============================================================
 * ⚠️ RISQUES / POINTS DE VIGILANCE
 * ============================================================
 *
 * ⚠️ Ne JAMAIS :
 *
 * ❌ utiliser FCPATH directement ailleurs
 * ❌ créer des dossiers avec mkdir hors de cette classe
 * ❌ utiliser $competition->folder directement
 * ❌ construire un chemin à la main
 *
 * ⚠️ Toujours passer par CompetitionStorage
 *
 *
 * ============================================================
 * 🏗️ STRUCTURE DES DOSSIERS
 * ============================================================
 *
 * uploads/competitions/
 *   ├── 2026_N_0001/
 *   │     ├── photos/
 *   │     ├── thumbs/
 *   │     ├── pdf/
 *   │     ├── pte/
 *   │     ├── export/
 *   │     └── temp/
 *   │
 *   └── 2026_R_UR22_0045/
 *         ├── photos/
 *         ├── thumbs/
 *         ├── pdf/
 *         ├── pte/
 *         ├── export/
 *         └── temp/
 *
 *
 * ============================================================
 * 🧩 RÈGLE DE NOMMAGE
 * ============================================================
 *
 * National :
 *   {SAISON}_N_{NUMERO}
 *
 * Régional :
 *   {SAISON}_R_UR{XX}_{NUMERO}
 *
 *
 * ============================================================
 * 🔄 COMPATIBILITÉ (IMPORTANT)
 * ============================================================
 *
 * La méthode resolvePath() permet de :
 *
 * ✔ utiliser l’ancien champ "folder" si présent
 * ✔ basculer automatiquement vers le nouveau système sinon
 *
 * 👉 Permet une migration progressive sans casser l’existant
 *
 *
 * ============================================================
 * 🚀 UTILISATION TYPE
 * ============================================================
 *
 * $storage = new CompetitionStorage();
 *
 * $basePath   = $storage->create($competition);
 * $photosPath = $storage->getPhotosPath($competition);
 *
 *
 * ============================================================
 * 🧠 ARCHITECTURE CIBLE
 * ============================================================
 *
 * Controllers / Services
 *        ↓
 * CompetitionStorage
 *        ↓
 * Filesystem
 *
 * ============================================================
 */

---

# 📦 Documentation — CompetitionStorage

```php
/**
 * ============================================================
 * 📦 SERVICE : CompetitionStorage
 * ============================================================
 *
 * 📅 Date        : 2026-04
 * 👤 Auteur      : (à compléter)
 * 📍 Localisation : app/Libraries/CompetitionStorage.php
 * 🧱 Architecture : CodeIgniter 4 (CI4)
 *
 * ============================================================
 * 🎯 OBJECTIFS
 * ============================================================
 *
 * Centraliser la gestion du système de fichiers des compétitions.
 *
 * Cette classe est la SEULE responsable de :
 *
 * ✔ Génération des chemins (photos, thumbs, pdf…)
 * ✔ Création de la structure de dossiers
 * ✔ Compatibilité avec l’ancien système (champ "folder")
 * ✔ Uniformisation des accès filesystem (N / R)
 *
 * 👉 Elle remplace :
 * ❌ $competitionFolder
 * ❌ $photoPath
 * ❌ mkdir dispersés
 * ❌ chemins codés en dur
 *
 *
 * ============================================================
 * 🧠 CONCEPT
 * ============================================================
 *
 * Le filesystem devient la source de vérité pour les fichiers.
 *
 * La base de données :
 * ✔ n’est pas modifiée
 * ✔ reste une source de données métier uniquement
 *
 * Tous les états sont calculés dynamiquement :
 * - présence photos
 * - présence thumbs
 * - état jugement
 *
 *
 * ============================================================
 * ⚠️ RISQUES / POINTS DE VIGILANCE
 * ============================================================
 *
 * ⚠️ Ne JAMAIS :
 *
 * ❌ utiliser FCPATH directement ailleurs
 * ❌ créer des dossiers avec mkdir hors de cette classe
 * ❌ utiliser $competition->folder directement
 * ❌ construire un chemin à la main
 *
 * ⚠️ Toujours passer par CompetitionStorage
 *
 *
 * ============================================================
 * 🏗️ STRUCTURE DES DOSSIERS
 * ============================================================
 *
 * uploads/competitions/
 *   ├── 2026_N_0001/
 *   │     ├── photos/
 *   │     ├── thumbs/
 *   │     ├── pdf/
 *   │     ├── pte/
 *   │     ├── export/
 *   │     └── temp/
 *   │
 *   └── 2026_R_UR22_0045/
 *         ├── photos/
 *         ├── thumbs/
 *         ├── pdf/
 *         ├── pte/
 *         ├── export/
 *         └── temp/
 *
 *
 * ============================================================
 * 🧩 RÈGLE DE NOMMAGE
 * ============================================================
 *
 * National :
 *   {SAISON}_N_{NUMERO}
 *
 * Régional :
 *   {SAISON}_R_UR{XX}_{NUMERO}
 *
 *
 * ============================================================
 * 🔄 COMPATIBILITÉ (IMPORTANT)
 * ============================================================
 *
 * La méthode resolvePath() permet de :
 *
 * ✔ utiliser l’ancien champ "folder" si présent
 * ✔ basculer automatiquement vers le nouveau système sinon
 *
 * 👉 Permet une migration progressive sans casser l’existant
 *
 *
 * ============================================================
 * 🚀 UTILISATION TYPE
 * ============================================================
 *
 * $storage = new CompetitionStorage();
 *
 * $basePath   = $storage->create($competition);
 * $photosPath = $storage->getPhotosPath($competition);
 *
 *
 * ============================================================
 * 🧠 ARCHITECTURE CIBLE
 * ============================================================
 *
 * Controllers / Services
 *        ↓
 * CompetitionStorage
 *        ↓
 * Filesystem
 *
 * ============================================================
 */
```

---

# 🧩 DOCUMENTATION DES FONCTIONS

👉 format propre, homogène, prêt à intégrer dans ton code

---

## 🔑 getCode

```php
/**
 * Génère le code dossier unique de la compétition
 *
 * Format :
 * - National : 2026_N_0001
 * - Régional : 2026_R_UR22_0045
 *
 * @param object $competition
 * @return string
 */
public function getCode($competition): string
```

---

## 📁 getBasePath

```php
/**
 * Retourne le chemin racine de la compétition
 *
 * @param object $competition
 * @return string
 */
public function getBasePath($competition): string
```

---

## 🔁 resolvePath

```php
/**
 * Retourne le chemin final (compatibilité ancien système)
 *
 * - utilise $competition->folder si existant
 * - sinon utilise le nouveau système basé sur getCode()
 *
 * @param object $competition
 * @return string
 */
public function resolvePath($competition): string
```

---

## 📸 getPhotosPath

```php
/**
 * Retourne le chemin du dossier photos
 *
 * @param object $competition
 * @return string
 */
public function getPhotosPath($competition): string
```

---

## 🖼 getThumbsPath

```php
/**
 * Retourne le chemin du dossier thumbnails
 *
 * @param object $competition
 * @return string
 */
public function getThumbsPath($competition): string
```

---

## 📄 getPdfPath

```php
/**
 * Retourne le chemin du dossier PDF
 *
 * @param object $competition
 * @return string
 */
public function getPdfPath($competition): string
```

---

## 🏆 getPtePath

```php
/**
 * Retourne le chemin du dossier PTE
 *
 * @param object $competition
 * @return string
 */
public function getPtePath($competition): string
```

---

## 📤 getExportPath

```php
/**
 * Retourne le chemin du dossier export
 *
 * @param object $competition
 * @return string
 */
public function getExportPath($competition): string
```

---

## 🧪 getTempPath

```php
/**
 * Retourne le chemin du dossier temporaire
 *
 * @param object $competition
 * @return string
 */
public function getTempPath($competition): string
```

---

## 🏗 create

```php
/**
 * Crée la structure complète des dossiers de la compétition
 *
 * - photos/
 * - thumbs/
 * - pdf/
 * - pte/
 * - export/
 * - temp/
 *
 * Fonction idempotente (peut être appelée plusieurs fois sans effet secondaire)
 *
 * @param object $competition
 * @return string chemin de base
 */
public function create($competition): string
```

---

## 🔍 hasPhotos

```php
/**
 * Vérifie si des photos existent dans la compétition
 *
 * @param object $competition
 * @return bool
 */
public function hasPhotos($competition): bool
```

---

## 🖼 hasThumbs

```php
/**
 * Vérifie si des thumbnails existent
 *
 * @param object $competition
 * @return bool
 */
public function hasThumbs($competition): bool
```

---

## 🧾 isJudged

```php
/**
 * Vérifie si la compétition a été jugée
 * (présence du fichier csv/jugement.csv)
 *
 * @param object $competition
 * @return bool
 */
public function isJudged($competition): bool
```

---

## 🗃 registerCompetition

```php
/**
 * Enregistre une compétition en base si elle n'existe pas
 *
 * ⚠️ Ne modifie pas la structure DB existante
 *
 * @param array $compet
 * @return int id de la compétition
 */
public function registerCompetition(array $compet)
```

---





Voici une **documentation claire, structurée et directement réutilisable** pour ta lib `CompetitionStorage`.
Tu peux l’intégrer telle quelle dans ton projet (README, doc interne ou commentaires PHP).

---

# 📦 CompetitionStorage — Documentation

## 🎯 Rôle de la classe

`CompetitionStorage` est le **point central de gestion du système de fichiers** pour les compétitions.

Elle permet de :

```text
✔ Générer une structure de dossiers homogène (N / R)
✔ Centraliser tous les chemins (photos, thumbs, pdf, etc.)
✔ Assurer la compatibilité avec l’ancien système (folder)
✔ Éviter les chemins codés en dur dans le projet
✔ Simplifier la maintenance et le debug
```

---

# 🧠 Concept clé

> 🔥 **Le filesystem devient la source de vérité pour les fichiers**

La base de données n’est **pas modifiée**.
Tous les états (ZIP, thumbs, etc.) sont **déduits dynamiquement**.

---

# 🏗️ Structure des dossiers

Chaque compétition possède une structure standardisée :

```text
uploads/competitions/
    2026_N_0001/
        photos/
        thumbs/
        pdf/
        pte/
        export/
        temp/

    2026_R_UR22_0045/
        photos/
        thumbs/
        pdf/
        pte/
        export/
        temp/
```

---

# 🔑 Logique de nommage

## 📌 National

```text
{SAISON}_N_{NUMERO}
```

Exemple :

```text
2026_N_0001
```

---

## 📌 Régional

```text
{SAISON}_R_UR{XX}_{NUMERO}
```

Exemple :

```text
2026_R_UR22_0045
```

---

# ⚙️ Méthodes principales

---

## 🧩 `getCode($competition)`

### 🎯 Rôle

Génère le nom du dossier à partir des données de la compétition.

### 🔧 Entrée

Objet compétition (DB)

### 📤 Sortie

String (code dossier)

---

## 📁 `getBasePath($competition)`

### 🎯 Rôle

Retourne le chemin racine de la compétition.

### 📤 Exemple

```php
/storage/competitions/2026_R_UR22_0045/
```

---

## 🔁 `resolvePath($competition)`

### 🎯 Rôle

Assure la compatibilité avec l’ancien système.

### 🧠 Fonctionnement

```php
if (ancien folder existe) → utilisé
sinon → nouveau chemin généré
```

---

👉 🔥 méthode clé pour la transition 523 → nouveau système

---

# 📂 Sous-dossiers

---

## 📸 `getPhotosPath()`

```php
/photos/
```

---

## 🖼 `getThumbsPath()`

```php
/thumbs/
```

---

## 📄 `getPdfPath()`

```php
/pdf/
```

---

## 🏆 `getPtePath()`

```php
/pte/
```

---

## 📤 `getExportPath()`

```php
/export/
```

---

## 🧪 `getTempPath()`

```php
/temp/
```

---

👉 Tous ces chemins sont :

✔ dynamiques
✔ cohérents
✔ centralisés

---

# 🏗️ Création des dossiers

---

## 🛠 `create($competition)`

### 🎯 Rôle

Crée automatiquement toute la structure :

```text
photos/
thumbs/
pdf/
pte/
export/
temp/
```

### ✅ Garanties

```text
✔ aucun mkdir dispersé dans le code
✔ structure toujours valide
✔ idempotent (peut être appelé plusieurs fois)
```

---

# 🔍 Méthodes utilitaires

---

## 📸 `hasPhotos()`

```php
true si photos présentes
```

---

## 🖼 `hasThumbs()`

```php
true si thumbs générés
```

---

## 🧾 `isJudged()`

```php
true si fichier jugement présent (csv/jugement.csv)
```

---

👉 Ces méthodes remplacent les flags :

```text
❌ isZIP
❌ isJudged en DB
```

---

# 🗃️ Enregistrement DB

---

## 🧾 `registerCompetition()`

### 🎯 Rôle

Insère une compétition en base si elle n’existe pas.

### ⚠️ Important

* ne modifie pas la structure DB
* conserve compatibilité Copain

---

# ⚠️ Règles d’utilisation (CRITIQUES)

---

## ❌ À ne jamais faire

```php
mkdir(...)
FCPATH . 'uploads/...'
$competition->folder
$photoPath
```

---

## ✅ Toujours faire

```php
$storage->getPhotosPath($competition)
$storage->create($competition)
```

---

# 🧠 Architecture cible

---

```text
CompetitionStorage
    ↓
Filesystem (source vérité)

CompetitionService (optionnel)
    ↓
État métier (hasZip, judged…)

Controllers
    ↓
utilisent storage

Views
    ↓
reçoivent des paths propres
```

---

# 🚀 Bénéfices

---

```text
✔ code centralisé
✔ suppression des bugs de chemins (ex 313)
✔ support N / R natif
✔ suppression des variables parasites
✔ maintenance simplifiée
✔ évolutif (COLOC, nouvelles features)
```

---

# 💬 Conclusion

`CompetitionStorage` devient :

> 🔥 **le pilier du système de fichiers des compétitions**

C’est **la seule classe autorisée** à :

```text
✔ créer des dossiers
✔ construire des chemins
✔ accéder aux fichiers
```

---

Si tu veux, prochaine étape 👇

👉 je peux te générer :

* un **CompetitionService (états métier propres)**
* ou un **check automatique qui détecte les anciens usages (anti-patterns)**

pour sécuriser complètement ton refactor 👍
