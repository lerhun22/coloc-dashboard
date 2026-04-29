Parfait 👍 on va te faire un **`installation.md` clair, complet et directement exploitable** pour toi… et pour d’autres clubs ou devs.

---

# ⚙️ Installation — Coloc V3

**Version : Base 253**
**Date : 31 mars 2026**

---

## 🎯 Objectif

Ce document explique comment installer et lancer le projet **Coloc V3** en local.

---

## 🧰 Prérequis

### 🖥 Environnement

- PHP ≥ 8.1
- MySQL / MariaDB
- Serveur local :
  - MAMP (Mac) recommandé
  - ou XAMPP / Laragon

---

### 📦 Outils

- Composer
- Git

---

## 📥 1. Récupération du projet

```bash id="1cb2he"
git clone https://github.com/TON-USER/coloc_v3.git
cd coloc_v3
```

---

## 📦 2. Installation des dépendances

```bash id="e4c8eh"
composer install
```

---

## ⚙️ 3. Configuration

### 📄 Copier le fichier env

```bash id="l3z2px"
cp env .env
```

---

### ✏️ Modifier `.env`

#### 🔹 URL du projet

```env id="5p6zj2"
app.baseURL = 'http://localhost:8888/coloc_v3/public/'
```

---

#### 🔹 Base de données

```env id="6i9rpk"
database.default.hostname = localhost
database.default.database = coloc_v3
database.default.username = root
database.default.password = root
database.default.DBDriver = MySQLi
```

---

#### 🔹 Copain (IMPORTANT)

```env id="g9q7dp"
copain.email=VOTRE_EMAIL
copain.password=VOTRE_PASSWORD
```

---

## 🗄 4. Base de données

### Créer la base

```sql id="a4v7bx"
CREATE DATABASE coloc_v3;
```

---

### Importer les tables

👉 selon ton projet :

- fichier SQL fourni
- ou migrations CodeIgniter

```bash id="3p8r2m"
php spark migrate
```

---

## 📁 5. Dossier writable

👉 Vérifier les droits :

```bash id="r8k2pz"
chmod -R 777 writable/
```

---

## 🚫 6. Git (important)

Les fichiers suivants ne doivent PAS être versionnés :

```bash id="m2p7rd"
.env
writable/*
*.zip
```

---

## 🚀 7. Lancement

### Option 1 — MAMP / Apache

👉 Accéder :

```id="6n5qyx"
http://localhost:8888/coloc_v3/public
```

---

### Option 2 — Serveur interne CodeIgniter

```bash id="b7f2qp"
php spark serve
```

👉 puis :

```id="n3c7pt"
http://localhost:8080
```

---

## 🧪 8. Test du module import

1. Aller sur :

```id="t5r9mj"
/competitions/import
```

2. Vérifier :

- affichage compétitions
- bouton **Importer**
- progression

---

## 🐞 Problèmes courants

### ❌ Aucune compétition affichée

👉 Vérifier :

- `.env` → email / password Copain
- logs CodeIgniter

---

### ❌ Erreur foreach null

👉 Corrigé dans base 253
➡️ vérifier données envoyées à la vue

---

### ❌ Import bloqué

👉 Vérifier :

- console navigateur (JS)
- route `/import/step`

---

### ❌ ZIP non téléchargé

👉 vérifier :

- droits `writable/`
- espace disque

---

## 🧹 Nettoyage (optionnel)

Supprimer fichiers temporaires :

```bash id="x9p2fk"
rm -rf writable/*
```

⚠️ garder `index.html`

---

## 🏁 Conclusion

Une fois installé, le projet permet :

- d’importer des compétitions depuis Copain
- de gérer les images
- de préparer les jugements

---

## 📚 Documentation

- Architecture : `docs/architecture.md`
- Import Copain : `docs/import/import-copain.md`

---

# 🎯 📁 OÙ LE METTRE

```bash
docs/installation.md
```

---

# 🚀 COMMIT

```bash
git add docs/installation.md
git commit -m "docs: ajout guide installation Coloc V3"
git push origin main
```
