Parfait 👍 on va faire un **résumé global du projet + cartographie claire**.
👉 Objectif : vision **architecture + rôles + fonctions**, utilisable dans ta doc.

---

# 📦 Coloc V3 — Synthèse du projet & architecture

**Date : 31 mars 2026**
**Base : 253**

---

# 🎯 OBJECTIF DU PROJET

Application web permettant :

- import de compétitions depuis Copain
- gestion des données (participants, photos, notes)
- traitement des images (ZIP → extraction → miniatures)
- interface de jugement

---

# 🧱 STRUCTURE GLOBALE

```text
coloc_v3/
│
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Libraries/
│   ├── Services/
│   ├── Views/
│   └── Helpers / Tools
│
├── public/
├── writable/
├── docs/
└── README.md
```

---

# 🧠 RÔLE DES DOSSIERS

---

## 🎮 Controllers

👉 **Orchestration / point d’entrée HTTP**

### Rôle

- reçoit les requêtes utilisateur
- appelle les services
- prépare les données pour les vues

### Exemples

#### `ImportFromCopain`

- `index()` → affiche les compétitions
- prépare `$competitions`, `$rcompetitions`

#### `Import`

- `start($id)` → lance import
- `step($id)` → retourne progression

---

## 🗃 Models

👉 **Accès base de données**

### Rôle

- CRUD
- mapping DB

### Exemples

- `CompetitionModel`
- `PhotoModel`
- `AuteurModel`

---

## 🧩 Libraries

👉 **Accès externe / technique bas niveau**

### Rôle

- communication Copain
- cURL
- parsing brut

### Exemples

#### `CopainLegacyReader`

- login + session (🔥 critique)

#### `CopainClient`

- generateZip
- downloadFile
- waitForZip

#### `CopainImporter`

- importCompetition()
- récupération URLs JSON

---

## ⚙️ Services

👉 **Logique métier (cœur du système)**

### Rôle

- orchestrer les opérations
- enchaîner les étapes

### Exemple

#### `CopainImportService`

```text
login → importCompetition → JSON → DB → ZIP → images
```

#### `ImportWorkflow`

```text
download → extract → move → thumbs
```

---

## 🎨 Views

👉 **Affichage**

### Rôle

- UI
- interaction utilisateur

### Exemples

#### `import/copain.php`

- liste des compétitions
- filtres
- JS interaction

#### `import/card.php`

- affichage d’une compétition
- bouton importer
- progression

---

## 🛠 Tools / Helpers

👉 **Fonctions utilitaires**

### Rôle

- helpers globaux
- fonctions communes

### Exemples

- formatage
- logs
- manipulation fichiers

---

## 📁 public/

👉 **Point d’entrée web**

- index.php
- assets publics

---

## 💾 writable/

👉 **Stockage runtime**

### Contenu

- ZIP téléchargés
- images extraites
- fichiers temporaires
- progression JSON

### ⚠️ Important

- non versionné
- nettoyage nécessaire

---

## 📚 docs/

👉 **Documentation projet**

- architecture
- import
- eureka
- installation

---

# 🔄 PIPELINE PRINCIPAL

```text
Utilisateur
   ↓
Controller (Import)
   ↓
Service (CopainImportService)
   ↓
Libraries (Copain)
   ↓
JSON
   ↓
DB (Models)
   ↓
ZIP → images
   ↓
Frontend (Views + JS)
```

---

# ⚙️ ORGANISATION FONCTIONNELLE

---

## 🔹 Import Copain

### Étapes

```text
1. login (Legacy)
2. importCompetition
3. récupération JSON
4. stockage DB
5. récupération ZIP
6. extraction
7. traitement images
```

---

## 🔹 Progression

- `/import/start`
- `/import/step`
- polling JS

---

# 🎨 ORGANISATION CSS

👉 fichier : `public/css/import.css`

### Contenu

- `.card-import`
- `.badge`
- `.progress-bar`
- `.filter-bar`

### Rôle

- layout grid
- style cards
- feedback visuel

---

# ⚡ ORGANISATION JS

👉 intégré dans `copain.php`

### Fonctions clés

#### `startImport(id)`

- déclenche import

#### `tickCard(id)`

- polling backend

#### `applyFilters()`

- filtre affichage

---

# 🧠 RÉPARTITION DES RESPONSABILITÉS

| Couche     | Rôle               |
| ---------- | ------------------ |
| Controller | orchestration HTTP |
| Service    | logique métier     |
| Library    | accès externe      |
| Model      | base de données    |
| View       | affichage          |
| JS         | interaction UI     |
| CSS        | présentation       |

---

# ⚠️ POINTS SENSIBLES

- session Copain (EUREKA 1)
- ZIP async (EUREKA 2)
- robustesse (EUREKA 3)
- progression réelle (EUREKA 4)

---

# 🚀 ÉTAT ACTUEL

✔ architecture propre
✔ import fonctionnel
✔ UI stable
✔ pipeline défini

---

# 🏁 CONCLUSION

Le projet est structuré en :

```text
Architecture MVC + Services + Libraries
```

👉 avec séparation claire :

- technique (Libraries)
- métier (Services)
- présentation (Views)

---

# 💡 VISION

👉 Tu es passé de :

```text
scripts imbriqués
```

➡️ à :

```text
système modulaire, maintenable et industrialisable
```

---

# 📁 À AJOUTER

```bash
docs/architecture-overview.md
```

---

# 🚀 COMMIT

```bash
git add docs/architecture-overview.md
git commit -m "docs: synthèse architecture projet + rôles dossiers et composants"
git push origin main
```

---
