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

$routes->get('test/copain/(:num)', 'ImportTest::copain/$1');
/*
|--------------------------------------------------------------------------
COMPETITIONS
|--------------------------------------------------------------------------
*/

$routes->get(
    'competitions',
    'Competitions::index'
);


/*
IMPORTANT
import doit être AVANT competitions/(:num)
*/

$routes->get(
    'competitions/import',
    'ImportFromCopain::index'
);

$routes->match(
    ['GET', 'POST'],
    'competitions/import/run',
    'ImportFromCopain::run'
);

$routes->get(
    'competitions/(:num)',
    'Competitions::show/$1'
);

$routes->get(
    'competitions/(:num)/photos',
    'Competitions::photos/$1'
);

$routes->get(
    'competitions/delete/(:num)',
    'Competitions::delete/$1'
);



/*
|--------------------------------------------------------------------------
IMPORT SAFE (async)
|--------------------------------------------------------------------------
*/

$routes->get('import/start/(:num)', 'ImportFromCopain::start/$1');
$routes->get('import/progress/(:num)', 'ImportFromCopain::progress/$1');
$routes->get('import/step/(:num)', 'ImportFromCopain::step/$1');


/*
|--------------------------------------------------------------------------
IMPORT COPAINS ancien workflow
|--------------------------------------------------------------------------
*/

$routes->match(
    ['GET', 'POST'],
    'competitions/import/run',
    'ImportFromCopain::run'
);

$routes->match(
    ['GET', 'POST'],
    'import/copain/run',
    'ImportFromCopain::run'
);


/*
|--------------------------------------------------------------------------
IMPORT ZIP LOCAL
|--------------------------------------------------------------------------
*/

$routes->get('import', 'Import::index');
$routes->get('import/run/(:any)', 'Import::run/$1');
/*
|--------------------------------------------------------------------------
JUGEMENT
|--------------------------------------------------------------------------
*/

$routes->get(
    'jugement',
    'Jugement::index'
);

$routes->get(
    'competitions/(:num)/jugement/photo/(:num)',
    'Jugement::photo/$1/$2'
);

$routes->post(
    'competitions/(:num)/jugement/saveNote',
    'Jugement::saveNote/$1'
);

$routes->get(
    'competitions/(:num)/jugement/disqualify/(:num)',
    'Jugement::disqualify/$1/$2'
);

$routes->get(
    'juges',
    'Juges::index'
);



/*
|--------------------------------------------------------------------------
EXPORT
|--------------------------------------------------------------------------
*/

$routes->get(
    'export',
    'Export::index'
);



/*
|--------------------------------------------------------------------------
SUIVI
|--------------------------------------------------------------------------
*/

$routes->get(
    'suivi',
    'Suivi::index'
);

$routes->get(
    'suivi/create',
    'Suivi::create'
);

$routes->get(
    'suivi/edit/(:num)',
    'Suivi::edit/$1'
);

$routes->post(
    'suivi/save',
    'Suivi::save'
);



/*
|--------------------------------------------------------------------------
TEST
|--------------------------------------------------------------------------
*/

$routes->get(
    'test/run',
    function () {
        echo "TEST OK";
    }
);



/*
|--------------------------------------------------------------------------
AUTOROUTE OFF
|--------------------------------------------------------------------------
*/

$routes->setAutoRoute(false);