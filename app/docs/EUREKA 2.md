Parfait 👍 je te le mets **proprement formaté en Markdown**, prêt à être copié dans un fichier :

👉 `docs/import/eureka-2-zip-national.md`

---

# 🚀 EUREKA 2 — ZIP National (Copain)

**Date : 31 mars 2026**
**Contexte : Import compétitions nationales (Base 253)**

---

## 🎯 📦 Principe général

👉 Contrairement au régional :

```text
Régional → synchrone (ZIP immédiat)
National → asynchrone (ZIP généré en arrière-plan)
```

---

## 🧠 ⚠️ Principe clé

Le ZIP **n’est pas disponible immédiatement** après l’appel.

👉 Il faut :

```text
1. déclencher
2. attendre
3. récupérer
```

---

# 🧩 🔁 PROCÉDURE COMPLÈTE

---

## 1️⃣ Lancer la génération (ASYNC)

```php
$client->generateZipAsync($ref, 'N');
```

👉 Important :

- ne pas attendre la réponse
- timeout court (`CURLOPT_TIMEOUT = 1`)
- rôle = déclencher le traitement côté Copain

---

## 2️⃣ Attendre que le ZIP soit prêt (polling)

URL du ZIP :

```text
https://copain.federation-photo.fr/webroot/json/zip_photos_{ref}.zip
```

### Exemple :

```php
$url = "...zip_photos_$ref.zip";

while (...) {

    $headers = @get_headers($url);

    if ($headers && str_contains($headers[0], '200')) {
        break;
    }

    sleep(5);
}
```

👉 rôle :

- vérifier l’existence du fichier
- simuler le comportement du legacy

---

## 3️⃣ Télécharger le ZIP

```php
$client->downloadFile($url, $localPath);
```

---

## 4️⃣ Dézipper (optionnel)

```php
$zip->extractTo($destination);
```

---

# 🎯 🧩 FLOW GLOBAL

```text
generateZipAsync()
        ↓
waitForZip()
        ↓
downloadFile()
        ↓
unzip
```

---

# ⚠️ POINTS CRITIQUES

## ❌ NE PAS FAIRE

```php
$client->generateZip($ref, 'N'); // ❌ bloque / timeout
```

---

## ✅ TOUJOURS FAIRE

```php
generateZipAsync + waitForZip
```

---

## ⏱️ Temps typique

| Type     | Temps     |
| -------- | --------- |
| Régional | immédiat  |
| National | 10s → 60s |

---

# 🧠 INTERPRÉTATION

Le serveur Copain :

```text
generate_zip.php
→ lance un traitement long
→ ne renvoie pas le ZIP immédiatement
```

👉 donc :

> 💥 le ZIP existe **après**, pas pendant l’appel

---

# 🛠️ IMPLÉMENTATION

---

## 🔹 1. generateZipAsync()

```php
public function generateZipAsync($ref, $type)
{
    $url = $this->url_generate_zip;

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query([
            'ref' => $ref,
            'type' => $type
        ]),
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_TIMEOUT => 1,
    ]);

    curl_exec($ch);
    curl_close($ch);

    return true;
}
```

---

## 🔹 2. waitForZip()

```php
public function waitForZip($ref, $timeout = 120)
{
    $url = "https://copain.federation-photo.fr/webroot/json/zip_photos_" . $ref . ".zip";

    $start = time();

    while ((time() - $start) < $timeout) {

        $headers = @get_headers($url);

        if ($headers && isset($headers[0]) && str_contains($headers[0], '200')) {
            return $url;
        }

        sleep(5);
    }

    return false;
}
```

---

## 🔹 3. downloadFile()

```php
public function downloadFile($url, $destination)
{
    $fp = fopen($destination, 'w');

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $success = curl_exec($ch);

    curl_close($ch);
    fclose($fp);

    return $success;
}
```

---

## 🔹 4. UTILISATION COMPLÈTE

```php
$ref = 714;
$type = 'N';

$client = new \App\Libraries\CopainClient();

/*
1. Déclencher
*/
$client->generateZipAsync($ref, $type);

/*
2. Attendre
*/
$zipUrl = $client->waitForZip($ref);

if (!$zipUrl) {
    throw new \Exception("ZIP timeout");
}

/*
3. Télécharger
*/
$tmpZip = WRITEPATH . "zip_$ref.zip";

$client->downloadFile($zipUrl, $tmpZip);

/*
4. Dézipper
*/
$zip = new \ZipArchive();

if ($zip->open($tmpZip) === TRUE) {
    $zip->extractTo(FCPATH . "uploads/competitions/$ref");
    $zip->close();
}
```

---

# 🧠 BONUS — VERSION UNIFIÉE

```php
public function getZip($ref, $type)
{
    if ($type === 'N') {

        $this->generateZipAsync($ref, 'N');

        return $this->waitForZip($ref);

    } else {

        $zip = $this->generateZip($ref, 'R');

        return $zip['zip_photos'] ?? false;
    }
}
```

---

# 🏁 CONCLUSION

Avec cette approche :

- ✔ plus de timeout
- ✔ comportement maîtrisé
- ✔ compatible legacy
- ✔ prêt pour UI async

---

# 🎯 RÉUSSITE

👉 Passage de :

```text
comportement implicite
```

➡️ à :

```text
process maîtrisé, robuste et industrialisable
```

---

# 📁 À FAIRE

```bash
mkdir -p docs/import
touch docs/import/eureka-2-zip-national.md
```

👉 colle le contenu

---

# 🚀 COMMIT

```bash
git add docs/import/eureka-2-zip-national.md
git commit -m "docs(import): ajout procédure ZIP national async (EUREKA 2)"
git push origin main
```

---
