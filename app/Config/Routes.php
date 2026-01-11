<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('/', 'PaymentController::index'); 
$routes->post('createOrder', 'PaymentController::createOrder');
$routes->post('getstatuspayment', 'PaymentController::getstatuspayment');
$routes->post('confirmation', 'PaymentController::confirmation');

//this route is to test mikrotik
$routes->post('logmikrotik', 'TestMikrotikController::logmikrotik');

$routes->get('/contact-transference', 'UsersController::index');

$routes->post('sendNotification', 'UsersController::sendNotification');

//loginToMikrotik
$routes->post('/login-to-mik', 'UsersController::loginToMikrotik');

//register user
$routes->post('/create-user-mikrotik', 'UsersController::createUserMikrotik');

//register
$routes->get('/create-order-payment', 'PaymentController::createOrderPayment');



