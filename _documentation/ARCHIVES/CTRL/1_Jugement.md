/**
 * ============================================================
 * 🎯 CONTROLLER : Jugement
 * ============================================================
 *
 * 📅 Date        : 2026-04
 * 👤 Auteur      : (à compléter)
 * 📍 Localisation : app/Controllers/Jugement.php
 * 🧱 Architecture : CodeIgniter 4 (CI4)
 *
 * ============================================================
 * 🎯 OBJECTIFS
 * ============================================================
 *
 * Gérer l’interface de jugement des compétitions.
 *
 * Ce controller est responsable de :
 *
 * ✔ Charger les données de la compétition (photos, juges, notes)
 * ✔ Préparer les données pour la vue (grille + photo active)
 * ✔ Fournir les chemins d’accès aux images via CompetitionStorage
 * ✔ Gérer la navigation dans les photos (ordre, passage)
 *
 * 👉 Il NE DOIT PAS :
 *
 * ❌ construire des chemins filesystem manuellement
 * ❌ utiliser FCPATH directement
 * ❌ manipuler des dossiers (mkdir, etc.)
 *
 * 👉 Toute la gestion des chemins est déléguée à :
 * CompetitionStorage
 *
 *
 * ============================================================
 * 🧠 CONCEPT
 * ============================================================
 *
 * Séparation stricte des responsabilités :
 *
 * - Controller → logique applicative + préparation données
 * - Storage → gestion filesystem
 * - Vue → affichage uniquement
 *
 *
 * ============================================================
 * ⚠️ RISQUES / POINTS DE VIGILANCE
 * ============================================================
 *
 * ⚠️ Variables critiques :
 *
 * - $photosPath → utilisé pour afficher les images
 * - $photo       → photo active
 * - $photos      → liste des photos
 *
 * ⚠️ Erreurs fréquentes :
 *
 * ❌ variable $img non définie dans la vue
 * ❌ utilisation de $competitionFolder (legacy)
 * ❌ chemins construits à la main
 *
 * ⚠️ Toujours utiliser :
 *
 * $storage->getPhotosPath($competition)
 *
 *
 * ============================================================
 * 🔄 FLUX GLOBAL
 * ============================================================
 *
 * 1. Chargement compétition
 * 2. Chargement photos + notes
 * 3. Calcul état (pending / partial / done)
 * 4. Détermination photo active
 * 5. Injection paths via CompetitionStorage
 * 6. Envoi vers la vue
 *
 *
 * ============================================================
 * 🚀 UTILISATION TYPE
 * ============================================================
 *
 * $storage = new CompetitionStorage();
 *
 * $photosPath = $storage->getPhotosPath($competition);
 *
 * return view('jugement/index', [...]);
 *
 * ============================================================
 */
