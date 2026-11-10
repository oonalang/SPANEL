<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
	require SYSTEMPATH . 'Config/Routes.php';
}

/** Router Setup */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
if (ENVIRONMENT === 'development') {
    $routes->get('dbg', 'Auth::index');
}
$routes->get('logout', 'Auth::logout');
$routes->get('dashboard', 'User::index');
$routes->match(['GET', 'POST'], '/', 'Auth::login');
$routes->match(['GET', 'POST'], 'login', 'Auth::login');
$routes->match(['GET', 'POST'], 'register', 'Auth::register');
$routes->match(['GET', 'POST'], 'setup', 'Setup::index');//Server



//
$routes->match(['GET', 'POST'], 'settings', 'User::settings');
$routes->match(['GET', 'POST'], 'verify_pass', 'User::verify_pass');
$routes->match(['GET', 'POST'], 'token', 'User::token');
$routes->match(['GET', 'POST'], 'Forget', 'User::Forget');
$routes->match(['GET', 'POST'], 'update', 'User::update');
$routes->match(['GET', 'POST'], 'Server', 'User::Server');
$routes->match(['GET'],'alter','Keys::alterUser');
$routes->get('lib', 'PrivateLibrary::index');
$routes->post('lib/upload', 'PrivateLibrary::upload');
$routes->get('lib/download', 'PrivateLibrary::download');
$routes->post('lib/delete', 'PrivateLibrary::delete');
$routes->match(['GET', 'POST'], 'PrivateDashboard', 'User::PrivateDashboard');
$routes->match(['GET', 'POST'], 'ManageHackingAttempt', 'User::ManageHackingAttempt');
$routes->match(['GET', 'POST'], 'ExtendDuration', 'User::ExtendDuration');
$routes->match(['GET', 'POST'], 'ManageShortenerLinks', 'User::ManageShortenerLinks');

$routes->match(['GET', 'POST'], 'Links', 'User::Links');
$routes->match(['GET', 'POST'], 'DeleteAllHistory', 'User::DeleteAllHistory');
$routes->match(['GET', 'POST'], 'ClearAllHistory', 'User::ClearAllHistory');





//
//testing
$routes->match(['GET', 'POST'], 'New', 'Home::index');
//$routes->get('server', 'User::server');
//

$routes->group('keys', function ($routes) {
	$routes->match(['GET', 'POST'], '/', 'Keys::index');
	$routes->match(['GET', 'POST'], 'generate', 'Keys::generate');
	$routes->match(['GET', 'POST'], 'ExtendDurationKey', 'Keys::ExtendDurationKey');
	$routes->get('(:num)', 'Keys::edit_key/$1');
	$routes->get('reset', 'Keys::api_key_reset');
	$routes->post('edit', 'Keys::edit_key');
	$routes->match(['GET', 'POST'], 'api', 'Keys::api_get_keys');
	
	$routes->match(['GET'],'alter','Keys::alterKeys');
	$routes->match(['GET'],'download/all','Keys::download_all_Keys');
	$routes->match(['GET'],'download/new','Keys::download_new_Keys');
	$routes->get('delete', 'Keys::api_key_delete');
	$routes->match(['GET'],'delete','Keys::deleteKeys');
	$routes->match(['GET'],'start','Keys::startDate');
	$routes->match(['GET'],'DelKeygen','Keys::deleteallkeygen');
    $routes->match(['GET'],'Un-UsedKeygen','Keys::Unusedkeygen');
    $routes->match(['GET'],'Del-EXPKeygen','Keys::deleteEXPkeygen');
    $routes->match(['GET'],'DelblockKeys','Keys::deleteBLOCKKeys');
    $routes->match(['GET'],'DelEXPKeys','Keys::deleteEXPKeys');
    $routes->match(['GET'],'resetAll','Keys::resetDeviceKey');
});

$routes->group('admin', ['filter' => 'admin'], function ($routes) {
	$routes->match(['GET', 'POST'], 'create-referral', 'User::ref_index');
	$routes->match(['GET', 'POST'], 'manage-users', 'User::manage_users');
	$routes->match(['GET', 'POST'], 'user/(:num)', 'User::user_edit/$1');
	$routes->match(['GET'],'user/delete/(:num)','User::user_delete/$1');
	
	$routes->match(['GET', 'POST'], 'referral/(:num)', 'User::referral_edit/$1');
	$routes->match(['GET'],'referral/delete/(:num)','User::referral_delete/$1');
	
	$routes->match(['GET', 'POST'], 'EditReferral', 'User::EditReferral');

	/* --------------------------- Admin API Grouping -------------------------- */
	$routes->group('api', function ($routes) {
		$routes->match(['GET', 'POST'], 'users', 'User::api_get_users');
	});
});

// Read-only public catalog. The legacy alias preserves the original loader path.
$routes->get('loader/catalog', 'LoaderCatalog::index');
$routes->post('loader/catalog/assigned', 'LoaderCatalog::assigned');
$routes->get('loader/catalog/file/(:segment)', 'LoaderCatalog::file/$1');
$routes->get('Loader/index.php', 'LoaderCatalog::index');
$routes->get('admin/loader-catalog', 'LoaderCatalogAdmin::index', ['filter' => 'auth']);
$routes->post('admin/loader-catalog/save', 'LoaderCatalogAdmin::save', ['filter' => 'auth']);
$routes->post('admin/loader-catalog/remove', 'LoaderCatalogAdmin::remove', ['filter' => 'auth']);

$routes->match(['GET', 'POST'], 'connect', 'Connect::index');
$routes->match(['GET', 'POST'], 'member-key', 'Keys::memberKey');
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
