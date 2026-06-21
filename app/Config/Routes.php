<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');   // Now root URL loads Auth::login
$routes->get('login', 'Auth::login');

$routes->get('/logout', 'Auth::logout');

$routes->post('/authenticate_user', 'Auth::authenticate_user');

$routes->get('/dashboard', 'Dashboard::dashboard');

$routes->get('/campaigns', 'BulkEmail::index');

$routes->get('/campaigns_list', 'BulkEmail::campaigns_list');

$routes->get('/get-campaigns-list', 'BulkEmail::get_campaigns_list');

$routes->post('/send-bulk-emails', 'BulkEmail::send_bulk_emails');

//get call this function after the page get loaded
$routes->get('/get-recipient-groups', 'BulkEmail::get_recipient_groups');

//get call this function to for the senders list from the backend application 
$routes->get('/get-senders-list', 'BulkEmail::get_senders_list');

//get call this function to for the senders list from the backend application 
$routes->post('/upload-recipient-group', 'BulkEmail::excelUpload');