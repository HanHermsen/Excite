<?php
/*
|--------------------------------------------------------------------------
|Application Routes
|--------------------------------------------------------------------------||
Here is where you can register all of the routes for an application.| It's a breeze. Simply tell Laravel the URIs it should respond to| and give it the controller to call when that URI is requested.|
*/


/* PUBLIC accessable stuff; no auth and Permissions here */

// Portal (Montana Media)
Route::get('/', ['uses' => 'Portal\HomeController@index']);
Route::get('waarom-en-hoe', [ 'uses' => 'Portal\WhyhowController@index']);
Route::get('meer-info', [ 'uses' => 'Portal\MoreinfoController@index']);
Route::get('abonnementen', [ 'uses' => 'Portal\SubscriptionsController@index']);
Route::get('privacy', [ 'uses' => 'Portal\PrivacyController@index']);
Route::get('voorwaarden', [ 'uses' => 'Portal\TermsController@index']);
Route::get('contact', [ 'uses' => 'Portal\ContactController@index']);
Route::get('wachtwoord-vergeten', [ 'uses' => 'Portal\ForgotpasswordController@index']);
Route::post('wachtwoord-vergeten', [ 'uses' => 'Portal\ForgotpasswordController@indexPost']);
Route::get('wachtwoord-vergeten/reset/{token}', [ 'uses' => 'Portal\ForgotpasswordController@getReset']);
Route::post('wachtwoord-vergeten/reset', [ 'uses' => 'Portal\ForgotpasswordController@postReset']);
Route::get('bestellen/express', [ 'uses' => 'Portal\OrderController@indexExpress']);
Route::get('bestellen/excite', [ 'uses' => 'Portal\OrderController@indexExcite']);
Route::get('get-app', [ 'uses' => 'Portal\GetappController@index']);


// public questions url for Portal
Route::get('questions/getMiniStatsHTML', ['uses' => 'Questions\QController@getMiniStatsHTML']);

// qbrowser
Route::get('qbrowser/{option?}', [ 'uses' => 'Home\QBrowserController@index']);

// question by email
Route::get('q_by_email/{option?}/{opt_out?}', [ 'uses' => 'Questions\QController@qByEmail']);
Route::post('q_by_email', [ 'uses' => 'Questions\QController@storeAnswer']);
// Montana version
Route::get('question-by-email/{option?}/{opt_out?}', [ 'uses' => 'Portal\QuestionController@getEmail']);
Route::post('question-by-email', [ 'uses' => 'Portal\QuestionController@postEmailAnswer']);

/** metahlApi for Ionic Apps; api integration in Excite Laravel Codebase */
// put routes in seperate file for tests on own domain with dedicated Laravel MetahlApi Codebase
include __DIR__ . '/routes.php.metahlApi';

/** Ionic yixow App experiment */
// for running the standalone node.js served App
Route::get('yixow_ion',[]);
// The API for the App
// for CORS preflight requests; cannot be handled in the middleware API group; why not????
Route::options('yixow/', [ 'middleware' => 'yixowApiAuth']);
Route::options('yixow/login', ['middleware' => 'yixowApiAuth']);
Route::group(['middleware' => 'yixowApiAuth'], function() {
	Route::get('yixow/', [ 'uses' => 'Yixow\YixowController@questions']);
	Route::get('yixow/login', ['uses' => 'Yixow\YixowController@login']);
	Route::post('yixow/login', ['uses' => 'Yixow\YixowController@login']);
});

// express test van public version of ego; for Montana Portal;
Route::get('express', [ 'uses' => 'Home\ExpressController@index']);
// post still in use by Portal
Route::post('express', [ 'uses' => 'Ego\EgoController@portalOrder']);

// portal order urls; post not yet in use
Route::post('porder', [ 'uses' => 'Ego\EgoController@portalOrder']);
Route::get('porder/getPrice', [ 'uses' => 'Ego\EgoController@getPrice']);
Route::get('porder/getLatLng', [ 'uses' => 'Ego\EgoController@getLatLng']);
Route::get('porder/getZipCode', [ 'uses' => 'Ego\EgoController@getZipCode']);
Route::get('porder/isValidKvk', [ 'uses' => 'Ego\EgoController@isValidKvk']);
Route::get('porder/isValidDomain', [ 'uses' => 'Ego\EgoController@isValidDomain']);



/* PROTECTED stuff */
Route::group(['middleware' => '\Excite\Http\Middleware\Permissions'], function()
{
	 // Re-write Default/HOME
	Route::get('home', ['middleware' => 'auth', 'uses' => 'Questions\QController@index']);
	//Route::get('/', ['middleware' => 'auth', 'uses' => 'Questions\QController@index']);
	
	// push notifications
	Route::get('push',['middleware' => 'auth', 'uses' => 'PushController@index']);
	Route::post('push',['middleware' => 'auth', 'uses' => 'PushController@push']);
	Route::get('push2',['middleware' => 'auth', 'uses' => 'PushController2@index']);
	Route::post('push2',['middleware' => 'auth', 'uses' => 'PushController2@push']);

	// Settings
	Route::get('settings', ['middleware' => 'auth', 'uses' => 'Settings\SettingsController@index']);
	Route::post('settings', ['middleware' => 'auth', 'uses' => 'Settings\SettingsController@change']);
	Route::post('settings/changepwd', ['middleware' => 'auth', 'uses' => 'Settings\SettingsController@changePwd']);

	// questions
	Route::get('questions', ['middleware' => 'auth', 'uses' => 'Questions\QController@index']);
	Route::post('questions', ['middleware' => 'auth', 'uses' => 'Questions\QController@store']);
	Route::get('questions/getQ', ['middleware' => 'auth', 'uses' => 'Questions\QController@getQ']);
	Route::get('questions/delQ', ['middleware' => 'auth', 'uses' => 'Questions\QController@delQ']);
	Route::get('questions/updateQdates', ['middleware' => 'auth', 'uses' => 'Questions\QController@updateQdates']);
	Route::get('questions/getStatsHTML', ['middleware' => 'auth', 'uses' => 'Questions\QController@getStatsHTML']);
	Route::get('questions/getTableData', ['middleware' => 'auth', 'uses' => 'Questions\QTableController@getTableData']);

	// groups
	Route::get('groups', ['middleware' => 'auth', 'uses' => 'Groups\GroupController@index']);
	Route::post('groups', ['middleware' => 'auth', 'uses' => 'Groups\GroupController@AddGroup']);
	Route::get('groups/getTableData', ['middleware' => 'auth', 'uses' => 'Groups\GroupTableController@getTableData']);
	Route::get('/groups/getGroupFormHTML', ['middleware' => 'auth', 'uses' => 'Groups\GroupController@getGroupFormHTML']);
	Route::post('/groups/changeGroup', ['middleware' => 'auth', 'uses' => 'Groups\GroupController@changeGroup']);

	// ego express group order and eXcite trial order (migrated from GroupController)
	Route::get('/ego', ['middleware' => 'auth', 'uses' => 'Ego\EgoController@ego']);
	Route::post('/ego', ['middleware' => 'auth', 'uses' => 'Ego\EgoController@storeContract']);
	Route::get('/ego/exciteTrial', ['middleware' => 'auth', 'uses' => 'Ego\EgoController@exciteTrial']);
	Route::get('ego/getContractTableData', ['middleware' => 'auth', 'uses' => 'Ego\ContractTableController@getTableData']);
	Route::get('ego/getPrice', ['middleware' => 'auth', 'uses' => 'Ego\EgoController@getPrice']);
	Route::get('ego/getLatLng', ['middleware' => 'auth', 'uses' => 'Ego\EgoController@getLatLng']);
	Route::get('ego/getZipCode', ['middleware' => 'auth', 'uses' => 'Ego\EgoController@getZipCode']);
	Route::get('/ego/getGroupNames', ['middleware' => 'auth', 'uses' => 'Ego\EgoController@getGroupNames']);

	// guests
	Route::get('guests',['middleware' => 'auth', 'uses' => 'Guests\GuestController@index']);
	Route::post('guests',['middleware' => 'auth', 'uses' => 'Guests\GuestController@postData']);
	Route::get('guests/getTableData', ['middleware' => 'auth', 'uses' => 'Guests\GuestTableController@getTableData']);
	Route::get('guests/getGroupMembers', ['middleware' => 'auth', 'uses' => 'Guests\GuestController@getGroupMembers']);
	Route::get('guests/getGroupInvitations', ['middleware' => 'auth', 'uses' => 'Guests\GuestController@getGroupInvitations']);
	Route::get('guests/invite/{option?}/{id?}/{gId?}' ,['middleware' => 'auth', 'uses' => 'Guests\InviteController@index']);
});

Route::get('test',['middleware' => 'auth', 'uses' => 'testController@index']);
Route::post('test',['middleware' => 'auth', 'uses' => 'testController@push']);

// Authentication routes...
//Route::get('auth/login/{showQuestions?}', 'Auth\AuthController@index');
Route::get('auth/login/{showQuestions?}', 'Portal\HomeController@index');
Route::post('auth/login', 'Auth\AuthController@authenticate');
Route::get('auth/logout', 'Auth\AuthController@getLogout');
// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');
// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');
// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');