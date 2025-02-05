<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HostpotController::index');
$routes->post('creater-order-payment', 'PaymentController::index');
$routes->post('createOrder', 'PaymentController::createOrder');
$routes->post('getstatuspayment', 'PaymentController::getstatuspayment');
