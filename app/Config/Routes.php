<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/*
|--------------------------------------------------------------------------
DASHBOARD
|--------------------------------------------------------------------------
*/
$routes->get('/', 'Dashboard::index');
$routes->get('dashboard', 'Dashboard::index');

/*
|--------------------------------------------------------------------------
COMPETITIONS
|--------------------------------------------------------------------------
*/
$routes->get('competitions', 'Competitions::index');

/*
IMPORTANT : spécifique AVANT (:num)
*/
$routes->get('competitions/import', 'ImportFromCopain::index');

$routes->match(['GET', 'POST'], 'competitions/import/run', 'ImportFromCopain::run');

$routes->get('competitions/(:num)', 'Competitions::show/$1');
$routes->get('competitions/(:num)/photos', 'Competitions::photos/$1');
$routes->get('competitions/delete/(:num)', 'Competitions::delete/$1');

/*
|--------------------------------------------------------------------------
IMPORT COPAIN (UI + workflow async)
|--------------------------------------------------------------------------
*/
$routes->get('import/copain', 'ImportFromCopain::index');

$routes->get('import/start/(:num)', 'ImportFromCopain::start/$1');
$routes->get('import/progress/(:num)', 'ImportFromCopain::progress/$1');
$routes->get('import/step/(:num)', 'ImportFromCopain::step/$1');

/*
|--------------------------------------------------------------------------
IMPORT DB
|--------------------------------------------------------------------------
*/
$routes->get('import/db/(:num)', 'ImportFromCopain::importOne/$1');

/*
|--------------------------------------------------------------------------
IMPORT FULL (DB + ZIP)
|--------------------------------------------------------------------------
*/
$routes->get('import/full/(:num)', 'ImportFromCopain::importFull/$1');

/*
👉 UNE SEULE route ZIP → ImportFromCopain
*/
$routes->get('import/zip/(:num)', 'ImportFromCopain::importZip/$1');

/*
👉 import DB ciblé
*/
$routes->get('import/db/(:num)', 'ImportFromCopain::importOne/$1');

/*
|--------------------------------------------------------------------------
IMPORT LOCAL (ZIP manuel)
|--------------------------------------------------------------------------
*/
$routes->get('import', 'Import::index');
$routes->get('import/run/(:any)', 'Import::run/$1');

/*
|--------------------------------------------------------------------------
JUGEMENT
|--------------------------------------------------------------------------
*/
$routes->get('jugement', 'Jugement::index');

$routes->get('jugement/photo/(:num)', 'Jugement::photo/$1');

$routes->get('competitions/(:num)/jugement/photo/(:num)', 'Jugement::photo/$1/$2');
$routes->post('competitions/(:num)/jugement/saveNote', 'Jugement::saveNote/$1');
$routes->get('competitions/(:num)/jugement/disqualify/(:num)', 'Jugement::disqualify/$1/$2');

/*
|--------------------------------------------------------------------------
EXPORT
|--------------------------------------------------------------------------
*/

$routes->get('dashboard/export', 'Dashboard::export');

/*
|--------------------------------------------------------------------------
SUIVI
|--------------------------------------------------------------------------
*/
$routes->get('suivi', 'Suivi::index');
$routes->get('suivi/create', 'Suivi::create');
$routes->get('suivi/edit/(:num)', 'Suivi::edit/$1');
$routes->post('suivi/save', 'Suivi::save');

/*
|--------------------------------------------------------------------------
TEST
|--------------------------------------------------------------------------
*/

$routes->get('test', 'Home::index');

$routes->get('compute/(:num)', 'Home::compute/$1');

$routes->get('test/run', fn() => print("TEST OK"));


$routes->get('test/copain', 'Home::testCopain');
$routes->get('test/copain-api', 'Home::testCopainApi');

$routes->get('test/import', 'Home::testImport');

$routes->get('test/zip', 'Home::testZip');
$routes->get('test/zip-init', 'Home::testZip_init');
$routes->get('test/zip-national', 'Home::testZipNational');
$routes->get('test/import/(:num)', 'Home::importregional/$1');
$routes->get('test/importnat/(:num)', 'Home::importnational/$1');


$routes->get('coloc/test/(:num)', 'ColocController::test/$1');
$routes->get('coloc/run', 'ColocController::run');
$routes->get('coloc/debug/(:num)', 'ColocController::debug/$1');

/*
|--------------------------------------------------------------------------
| VIGNETTES - TOOLS
|--------------------------------------------------------------------------
*/

// import batch (déjà existant)
$routes->get('tools/generervignettes/(:num)', 'Tools\GenererVignettes::index/$1');

// 🔥 regeneration complète
$routes->get('tools/vignettes/regenerate/(:num)', 'Tools\GenererVignettes::regenerateAll/$1');

// 🧩 reprise manquantes (1 compétition)
$routes->get('tools/vignettes/resume/(:num)', 'Tools\GenererVignettes::resumeMissing/$1');

// 🌍 reprise globale (toutes les compétitions)
$routes->get('tools/vignettes/resume-all', 'Tools\GenererVignettes::resumeAllCompetitions');

// 📊 scan dashboard
$routes->get('tools/vignettes/scan', 'Tools\GenererVignettes::scanCompetitionsStatus');

$routes->get('tools/vignettes/stop', function () {
    file_put_contents(WRITEPATH . 'tmp/thumbs_control.json', json_encode([
        'stop' => true
    ]));
    echo "STOP demandé";
});


$routes->group('dashboard', function ($routes) {

    $routes->get('/', 'Dashboard::index');
    $routes->get('synthese', 'Dashboard::synthese');
    $routes->get('analyse', 'Dashboard::analyse');
    $routes->get('rebuild/(:num)', 'Dashboard::rebuild/$1');
    $routes->get('rebuild-all', 'Dashboard::rebuildAll');
    $routes->get('coloc', 'Dashboard::coloc');
});


/*
|--------------------------------------------------------------------------
AUTOROUTE OFF
|--------------------------------------------------------------------------
*/
$routes->setAutoRoute(false);
