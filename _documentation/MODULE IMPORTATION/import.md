Parfait 👍 on va compléter la documentation avec :

- contraintes spécifiques **COPAINS (login / cookie / session / timeout)**
- explications pour **développeur novice CI4**
- diagramme UML (texte + schéma logique)
- doc structurée prête à mettre dans `/docs`
- orientée maintenance future

Je te fournis une **version complète de doc dev**, que tu peux copier dans :

```text
/docs/IMPORT_COPAINS.md
```

ou

```text
/app/Docs/ImportCopains.md
```

---

# 📘 MODULE IMPORT COPAINS — Documentation développeur (CI4)

## 1 — Objectif du module

Importer une compétition depuis COPAINS vers COLOC avec :

- données JSON
- base SQL
- ZIP photos (gros fichiers)
- extraction
- génération vignettes
- workflow async
- barre de progression

Contraintes :

- gros ZIP (>100MB)
- timeout PHP
- session COPAINS
- cookies obligatoires
- CI4 AutoRoute OFF
- environnement MAMP / local / serveur

---

## 2 — Contraintes spécifiques COPAINS

### 2.1 Login obligatoire

COPAINS nécessite :

```text
login → cookie → session → import → download zip
```

Sans cookie :

```
download ZIP impossible
generateZip retourne vide
```

Le login est fait dans :

```
CopainClient::autoLogin()
```

---

### 2.2 Cookie obligatoire

Cookie stocké dans :

```text
writable/copain_cookie.txt
```

Utilisé par :

```
curlPost
downloadRange
getRemoteFileSize
```

Options curl :

```
CURLOPT_COOKIEJAR
CURLOPT_COOKIEFILE
```

Sans cookie :

```
ZIP vide
download fail
timeout
```

---

### 2.3 Session COPAINS courte

La session COPAINS expire vite.

Donc on doit :

```
login avant import
login avant download
```

Ne pas supposer session active.

---

### 2.4 Timeout serveur COPAINS

ZIP peut être long à générer.

Donc :

```
download chunk obligatoire
```

Sinon :

```
timeout
500
zip incomplet
```

---

### 2.5 ZIP très volumineux

ZIP peut être :

```
10 MB
50 MB
200 MB
500 MB
```

Donc on utilise :

```
downloadChunk()
```

et pas download complet.

---

## 3 — Architecture générale

```
UI
 ↓
ImportFromCopain
 ↓
ImportWorkflow
 ↓
CopainImporter
 ↓
CopainClient
 ↓
Filesystem
 ↓
Uploads
```

---

## 4 — Workflow complet

```
click importer
 ↓
start()
 ↓
progress.php
 ↓
step()
 ↓
download_json
download_zip
extract_zip
move_files
thumbs
done
```

---

## 5 — Structure des dossiers

### writable/imports

```
writable/imports/

    4452.zip
    info.json
    state.json
    tmp_4452/
```

| fichier    | rôle           |
| ---------- | -------------- |
| zip        | téléchargement |
| info.json  | url + size     |
| state.json | progression    |
| tmp        | extract        |

---

### uploads/competitions

```
public/uploads/competitions/

    2026_22_5_4452/

        photos/
        thumbs/
        pdf/
        pte/
```

Destination finale.

---

## 6 — Fichiers du module

### Controller

```
App/Controllers/ImportFromCopain.php
```

Fonctions :

```
index
start
progress
step
```

---

### Librairies

```
App/Libraries/

CopainClient.php
CopainImporter.php
ImportWorkflow.php
CompetitionCleaner.php
```

---

### Views

```
app/Views/import/

copain.php
card.php
progress.php
```

---

### Tools

```
App/Controllers/Tools/GenererVignettes.php
```

---

### Routes

```
import/start
import/progress
import/step
```

AutoRoute OFF obligatoire.

---

## 7 — ImportWorkflow

Stocke l’état dans :

```
writable/imports/{id}/state.json
```

Contenu :

```
step
progress
status
size
downloaded
```

Permet :

```
async
resume
progress
```

---

## 8 — CopainClient

Responsable de :

```
login
importCompetition
generateZip
downloadRange
getRemoteFileSize
```

Important :

```
cookie obligatoire
```

---

## 9 — CopainImporter

Responsable de :

```
importCompetition
downloadChunk
extractZipOnly
moveChunk
thumbChunk
cleanup
```

Chaque fonction doit être courte.

Car :

```
step() est appelé souvent
```

---

## 10 — ImportFromCopain

Controller async.

### start

Initialise workflow.

### progress

Affiche barre.

### step

Exécute une étape.

Jamais faire tout dans start.

---

## 11 — progress.php

Boucle async :

```
tick()
fetch step
update bar
repeat
```

Ne jamais lancer import ici.

---

## 12 — card.php

UI import.

```
startImport(id)
```

Doit utiliser :

```
window.location
```

pas fetch.

---

## 13 — Sécurité gros ZIP

Download par morceaux :

```
5 MB
```

Evite timeout.

---

## 14 — Sécurité CI4

Toujours :

```
base_url()
(int)$id
routes définies
```

Sinon :

```
[object Promise]
404
BadRequest
```

---

## 15 — Nettoyage

cleanup supprime :

```
zip
tmp
info
```

Evite disque plein.

---

## 16 — Diagramme UML (simplifié)

```
+-------------------+
| ImportFromCopain  |
+-------------------+
| start()           |
| progress()        |
| step()            |
+---------+---------+
          |
          v
+-------------------+
| ImportWorkflow    |
+-------------------+
| state.json        |
| setStep()         |
| progress()        |
| done()            |
+---------+---------+
          |
          v
+-------------------+
| CopainImporter    |
+-------------------+
| importCompetition |
| downloadChunk     |
| extractZipOnly    |
| moveChunk         |
| thumbChunk        |
| cleanup           |
+---------+---------+
          |
          v
+-------------------+
| CopainClient      |
+-------------------+
| login             |
| generateZip       |
| downloadRange     |
| getSize           |
+---------+---------+
          |
          v
+-------------------+
| Filesystem        |
+-------------------+
| imports           |
| tmp               |
| uploads           |
+-------------------+
```

---

## 17 — Diagramme workflow

```
UI
 ↓
start
 ↓
progress
 ↓
step
 ↓
workflow
 ↓
importer
 ↓
client
 ↓
filesystem
 ↓
uploads
```

---

## 18 — Conseils pour dev novice CI4

Ne pas :

```
tout faire dans controller
tout faire en 1 script
download direct
extract direct
```

Toujours :

```
workflow
step
state.json
chunk
```

Toujours utiliser :

```
base_url
routes
(int)$id
```

---

## 19 — Points critiques

| point               | risque         |
| ------------------- | -------------- |
| cookie              | zip impossible |
| timeout             | import cassé   |
| zip gros            | crash          |
| fetch/redirect      | bug            |
| base_url            | 404            |
| imports non nettoyé | disque plein   |

---

## 20 — Etat actuel

✔ async
✔ stable
✔ gros ZIP
✔ cookie COPAINS
✔ progress
✔ cleanup
✔ thumbs batch
✔ CI4 strict

Module prêt production.

---

## 21 — Si tu veux

- diagramme UML graphique
- PDF doc
- schéma architecture image
- doc utilisateur
- doc admin
- doc install serveur
