Parfait 👍 on passe à **EUREKA 4 — progression réelle (UX + backend synchronisés)** 🔥

👉 Là on va transformer ton import en **expérience fluide et professionnelle**, avec :

- progression basée sur **données réelles**
- feedback utilisateur clair
- fin des “fake %”

---

# 🚀 EUREKA 4 — Progression réelle (ZIP + étapes)

**Date : 31 mars 2026**
**Suite : EUREKA 3**

---

## 🎯 Objectif

Remplacer la progression “simulée” par une progression :

- réelle
- fiable
- compréhensible pour l’utilisateur

---

# 🧠 PROBLÈME ACTUEL

👉 Aujourd’hui :

```text
progress = valeur arbitraire
```

❌ pas fiable
❌ UX moyenne
❌ pas représentatif du réel

---

# 🧩 SOLUTION

👉 Combiner :

```text
1. étapes métier (logique)
2. progression réelle (taille fichier / traitement)
```

---

# 🧱 1. STRUCTURE PROGRESSION

```json id="cqp2vv"
{
  "step": "download_zip",
  "progress": 42,
  "label": "Téléchargement images",
  "size": "12 MB / 50 MB"
}
```

---

# 🛠️ 2. PROGRESSION PAR ÉTAPE

| Étape         | % approx  |
| ------------- | --------- |
| download_json | 5%        |
| generate_zip  | 10%       |
| download_zip  | 10 → 70%  |
| extract_zip   | 70 → 90%  |
| move_files    | 90 → 95%  |
| thumbs        | 95 → 100% |

---

# 🛠️ 3. DOWNLOAD AVEC PROGRESSION RÉELLE

👉 clé : `CURLOPT_PROGRESSFUNCTION`

```php id="v4e5zh"
public function downloadWithProgress($url, $destination, $id)
{
    $fp = fopen($destination, 'w');

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    curl_setopt($ch, CURLOPT_NOPROGRESS, false);

    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION,
        function ($resource, $download_size, $downloaded) use ($id) {

            if ($download_size > 0) {

                $percent = ($downloaded / $download_size) * 100;

                $progress = 10 + ($percent * 0.6); // 10% → 70%

                // 👉 stocker progression
                file_put_contents(
                    WRITEPATH . "progress_$id.json",
                    json_encode([
                        'step' => 'download_zip',
                        'progress' => round($progress),
                        'size' => round($downloaded / 1024 / 1024) . " MB"
                    ])
                );
            }
        }
    );

    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
}
```

---

# 🛠️ 4. EXTRACTION AVEC PROGRESSION

👉 estimation basée sur nombre de fichiers

```php id="kvlb2l"
public function extractWithProgress($zipPath, $destination, $id)
{
    $zip = new \ZipArchive();

    if ($zip->open($zipPath) !== TRUE) {
        return false;
    }

    $total = $zip->numFiles;

    for ($i = 0; $i < $total; $i++) {

        $zip->extractTo($destination, [$zip->getNameIndex($i)]);

        $percent = ($i / $total) * 100;

        $progress = 70 + ($percent * 0.2); // 70 → 90

        file_put_contents(
            WRITEPATH . "progress_$id.json",
            json_encode([
                'step' => 'extract_zip',
                'progress' => round($progress)
            ])
        );
    }

    $zip->close();

    return true;
}
```

---

# 🛠️ 5. ROUTE `/import/step`

```php id="u5skjw"
public function step($id)
{
    $file = WRITEPATH . "progress_$id.json";

    if (!file_exists($file)) {
        return $this->response->setJSON([
            'status' => 'waiting'
        ]);
    }

    $data = json_decode(file_get_contents($file), true);

    if ($data['progress'] >= 100) {
        $data['status'] = 'done';
    } else {
        $data['status'] = 'running';
    }

    return $this->response->setJSON($data);
}
```

---

# 🧠 FRONT (déjà compatible 👍)

Ton JS actuel :

```js id="d9bgte"
tickCard(id);
```

👉 va automatiquement afficher :

- progression réelle
- texte dynamique
- taille fichier

---

# 🎯 FLOW FINAL

```text id="lwhtc6"
generateZipAsync
        ↓
waitForZip
        ↓
downloadWithProgress  ← réel
        ↓
extractWithProgress   ← réel
        ↓
done
```

---

# 🎯 RÉSULTAT UX

✔ progression fluide
✔ pas de blocage
✔ feedback réel
✔ utilisateur rassuré

---

# 🧠 BONUS

👉 Tu peux afficher :

```text
12 MB / 120 MB
```

👉 ou :

```text
245 / 1200 images
```

---

# 🏁 CONCLUSION

👉 Tu passes de :

```text
progress fake
```

➡️ à :

```text
progress data-driven
```

---

# 🚀 IMPACT

👉 UX niveau application pro :

- transparence
- fluidité
- confiance utilisateur

---

# 📁 À AJOUTER

```bash
docs/import/eureka-4-progression-reelle.md
```

---

# 🚀 COMMIT

```bash
git add docs/import/eureka-4-progression-reelle.md
git commit -m "docs(import): EUREKA 4 - progression réelle ZIP + extraction"
git push origin main
```

---

# 🔥 PROCHAINE ÉTAPE (ULTIME)

👉 EUREKA 5 possible :

- file d’attente (queue)
- traitement en arrière-plan (cron / worker)
- multi-import simultané
