Parfait 👍 on va formaliser ça en **EUREKA 1 propre, structuré et prêt à être versionné**.
👉 C’est une découverte **fondamentale** (session Copain), donc on la documente bien.

---

# 🚀 EUREKA 1 — Session Copain & Import National / Régional

**Date : 31 mars 2026**
**Base : 253**

---

## 🎯 Objectif

Comprendre et stabiliser :

- l’import des compétitions Copain
- la distinction **National / Régional**
- le rôle de la session serveur

---

# 💥 PROBLÈME INITIAL

- Impossible de distinguer correctement **National / Régional**
- Le paramètre `"N"` ou `"R"` semblait ignoré
- Résultats incohérents selon les appels

---

# 🔍 CAUSE RÉELLE (CRITIQUE)

👉 Copain fonctionne avec une **session serveur obligatoire**

```text
login → session PHP → cookie → importCompetition
```

---

## ❌ Mauvaise approche

```php
CopainClient->login()
```

➡️ ne suffit PAS
➡️ session incomplète
➡️ comportement instable

---

## ✅ Bonne approche (découverte clé)

```php
$legacy->getCompetitions($email, $password);
```

👉 Ce call :

- initialise la session Copain
- crée le cookie valide
- prépare les appels suivants

---

# 🎯 WORKFLOW CORRECT

```text
1. LOGIN via CopainLegacyReader
        ↓
2. Session + cookie actifs
        ↓
3. importCompetition(id, "N" ou "R")
        ↓
4. récupération JSON
        ↓
5. traitement / DB
```

---

# 🧪 CODE DE TEST VALIDÉ

```php
public function copain($id)
{
    $email = "xx";
    $password = "xx";

    $cookie = WRITEPATH . 'copain_cookie.txt';

    if (file_exists($cookie)) {
        unlink($cookie);
    }

    $legacy = new \App\Libraries\CopainLegacyReader();

    $login = $legacy->getCompetitions($email, $password);

    if ($login['code'] != 0) {
        dd($login);
    }

    $client = new \App\Libraries\CopainImporter();

    $import = $client->importCompetition(
        723,
        "R", // ou "N"
        'oui'
    );

    dd($import);
}
```

---

# 📦 RÉPONSE API (COMMUNE)

```text
file_compet
file_juge
file_club
file_user
file_photos
file_note
file_medaille
code = 0
```

👉 URLs JSON correctement récupérées ✔

---

# 🔁 DIFFÉRENCE R / N

## Régional

```json
"urs_id": "12"
"saison": "2015"
```

## National

```json
"urs_id": null
"prefixe": "PMN1"
"type": "2"
```

👉 distinction réelle dans les données ✔

---

# ❌ CAS ERREUR

### Mauvais login

```json
code: "13"
competitions: null
rcompetitions: null
```

👉 comportement OK ✔

---

# ⚠️ RÈGLES IMPORTANTES

## ❌ À NE PAS FAIRE

- utiliser uniquement `CopainClient->login()`
- multiplier les instances client
- appeler import sans session active

---

## ✅ À FAIRE

- login UNE fois via `CopainLegacyReader`
- réutiliser le cookie
- enchaîner les appels
- passer explicitement `"N"` ou `"R"`

---

# 🧱 ARCHITECTURE VALIDÉE

```text
Controller
   ↓
CopainImportService
   ↓
[LOGIN via CopainLegacyReader]  ← 🔥 clé
   ↓
CopainClient / CopainImporter
   ↓
importCompetition()
   ↓
JSON
   ↓
normalize
   ↓
DB
```

---

# 🧪 TESTS VALIDÉS

| Cas           | Résultat   |
| ------------- | ---------- |
| "R"           | OK         |
| "N"           | OK         |
| mauvais login | code 13 OK |

---

# 🧠 INTERPRÉTATION

👉 Copain :

```text
- ne fonctionne pas en API stateless
- dépend d’une session PHP interne
```

👉 Donc :

> 💥 le login n’est pas un simple appel → c’est une initialisation de session

---

# 🏁 CONCLUSION

👉 Pour que l’import fonctionne :

```text
1. login via Legacy (obligatoire)
2. session active
3. importCompetition fiable
```

---

# 🚀 IMPACT

👉 Passage de :

```text
comportement instable
```

➡️ à :

```text
process fiable et reproductible
```

---

# 📁 À AJOUTER

```bash
docs/import/eureka-1-session-copain.md
```

---

# 🚀 COMMIT

```bash
git add docs/import/eureka-1-session-copain.md
git commit -m "docs(import): EUREKA 1 - gestion session Copain + import fiable N/R"
git push origin main
```

---

# 🔥 CE QUE TU AS FAIT (important)

👉 Là tu as identifié :

💥 **un comportement implicite serveur (session cachée)**
➡️ et tu l’as rendu explicite

👉 C’est exactement ce qui transforme un code fragile en système fiable.

---
