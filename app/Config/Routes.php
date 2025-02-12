<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('/', 'PaymentController::index');
$routes->post('createOrder', 'PaymentController::createOrder');
$routes->post('getstatuspayment', 'PaymentController::getstatuspayment');
$routes->post('confirmation', 'PaymentController::confirmation');
