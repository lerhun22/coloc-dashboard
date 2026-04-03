# 📦 Module Import Copain — État des fonctionnalités (Base 253)

**Date : 31 mars 2026**

---

## 🎯 Objectif

Ce module permet :

- de récupérer les compétitions depuis Copain
- de lancer leur import (JSON + ZIP)
- de suivre la progression en temps réel
- de préparer les données pour le jugement

---

## 🧱 Architecture

### 1. Controller — `ImportFromCopain`

#### Fonction principale

`index()`

**Rôle :**

- charge la configuration Copain (`.env`)
- appelle le service Copain
- prépare les données pour la vue

**Fonctionnement :**

1. Vérification email / password
2. Appel `CopainLegacyReader`
3. Normalisation :
   - `competitions` (national)
   - `rcompetitions` (régional)

4. Gestion utilisateur (profil + UR)
5. Construction liste UR
6. Envoi à la vue

**État :**

- ✅ stable
- ✅ sécurisé (pas de null)
- ✅ logs exploitables

---

### 2. Service — `CopainLegacyReader`

#### Fonction principale

`getCompetitions(email, password)`

**Rôle :**

- authentification Copain (cURL + cookie)
- récupération des données
- parsing JSON

**Points sensibles :**

- gestion session (cookie)
- dépendance externe Copain
- format de réponse (HTML vs JSON)

**État :**

- ✅ fonctionnel avec `.env`
- ⚠️ dépend du service Copain

---

### 3. Vue — `copain.php`

**Rôle :**

- afficher les compétitions
- gérer les filtres
- structurer les cartes

**Fonctionnalités :**

- filtre type (national / régional)
- filtre UR
- affichage en grille

**JavaScript intégré :**

- `startImport(id)` → lance l’import
- `tickCard(id)` → met à jour la progression
- `applyFilters()` → filtre visuel

**État :**

- ✅ stable
- ✅ plus d’erreur `foreach`
- ✅ UX fonctionnelle

---

### 4. Composant — `card.php`

**Rôle :**

- afficher une compétition

**Contenu :**

- nom
- saison / UR / numéro / ID
- badge (national / régional)
- bouton **Importer**
- progression :
  - barre
  - texte
  - taille

**Interaction :**

- déclenche `startImport(id)`

**État :**

- ✅ propre
- ✅ sans doublon
- ✅ découplé du layout

---

### 5. Pipeline d’import

#### Route : `import/start/{id}`

Lance :

- téléchargement JSON
- téléchargement ZIP
- extraction
- organisation fichiers
- génération miniatures

---

#### Route : `import/step/{id}`

Retourne l’état :

```json
{
  "status": "...",
  "step": "...",
  "progress": 42
}
```

---

#### Étapes connues

- `download_json`
- `download_zip`
- `extract_zip`
- `move_files`
- `thumbs`
- `done`

**État :**

- ✅ structure OK
- ⚠️ fiabilité ZIP à améliorer

---

## 🎨 UX / Front

- affichage par cartes
- filtres dynamiques
- bouton importer
- progression en temps réel
- redirection automatique vers `/jugement`

---

## 🧾 Git / Projet

- suppression des ZIP du repository
- `.gitignore` configuré
- commit structuré

---

## 🧠 Synthèse

### ✅ Stable

- affichage compétitions
- filtres
- lancement import
- UI
- structure MVC

---

### ⚠️ À fiabiliser

- login Copain
- pipeline ZIP
- gestion erreurs

---

## 🚀 Prochaines étapes

### 1. Priorité haute

- sécuriser téléchargement ZIP
- vérifier intégrité
- extraction fiable

### 2. Priorité moyenne

- gestion erreurs (timeout, retry)

### 3. Priorité basse

- organisation stockage
- nettoyage automatique

---

## 🏁 Conclusion

Le module est désormais :

- fonctionnel
- structuré
- prêt pour industrialisation

La prochaine étape clé est la fiabilisation du pipeline d’import (ZIP).

---

# 🎯 CONSEIL

👉 Mets ça dans :

```
README.md
```

ou :

```
docs/import-copain.md
```

---

# 🚀 SI TU VEUX ALLER PLUS LOIN

Je peux te faire ensuite :

👉 un **schéma visuel du pipeline import (ultra clair pour ton club / équipe)**
👉 ou une **doc technique spéciale "debug ZIP"** (très utile)

On est vraiment sur une base solide maintenant 💪
