<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('/', 'PaymentController::index');
$routes->post('createOrder', 'PaymentController::createOrder');
$routes->post('getstatuspayment', 'PaymentController::getstatuspayment');


//this route is to test mikrotik
$routes->post('logmikrotik', 'TestMikrotikController::logmikrotik');

$routes->get('/contact-transference', 'UsersController::index');

$routes->post('sendNotification', 'UsersController::sendNotification');
