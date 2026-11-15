<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('health', 'Health::index');

$routes->get('favicon.ico', 'Media::favicon/32');
$routes->get('favicon-32.png', 'Media::favicon/32');
$routes->get('favicon-16.png', 'Media::favicon/16');
$routes->get('apple-touch-icon.png', 'Media::favicon/180');

$routes->get('setup', 'Setup::index');
$routes->post('setup', 'Setup::create');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Dashboard::index');

    $routes->get('change-password', 'ChangePassword::index');
    $routes->post('change-password', 'ChangePassword::update');

    $routes->get('orders', 'Orders::index');
    $routes->get('orders/new', 'Orders::new');
    $routes->post('orders', 'Orders::create');
    $routes->get('orders/(:num)', 'Orders::show/$1');
    $routes->post('orders/(:num)/update', 'Orders::update/$1');
    $routes->post('orders/(:num)/status', 'Orders::status/$1');
    $routes->post('orders/(:num)/items', 'Orders::addItem/$1');
    $routes->post('orders/(:num)/items/(:num)/status', 'Orders::itemStatus/$1/$2');
    $routes->post('orders/(:num)/items/(:num)/delete', 'Orders::deleteItem/$1/$2');
    $routes->get('orders/(:num)/pdf', 'Orders::pdf/$1');
    $routes->get('orders/(:num)/whatsapp', 'Orders::whatsapp/$1');

    $routes->get('customers', 'Customers::index');
    $routes->get('customers/new', 'Customers::new');
    $routes->post('customers', 'Customers::create');
    $routes->get('customers/(:num)', 'Customers::show/$1');
    $routes->get('customers/(:num)/edit', 'Customers::edit/$1');
    $routes->post('customers/(:num)', 'Customers::update/$1');
    $routes->post('customers/(:num)/delete', 'Customers::delete/$1');

    $routes->get('motorcycles', 'Motorcycles::index');
    $routes->get('motorcycles/new', 'Motorcycles::new');
    $routes->post('motorcycles', 'Motorcycles::create');
    $routes->get('motorcycles/(:num)', 'Motorcycles::show/$1');
    $routes->get('motorcycles/(:num)/edit', 'Motorcycles::edit/$1');
    $routes->post('motorcycles/(:num)', 'Motorcycles::update/$1');
    $routes->post('motorcycles/(:num)/delete', 'Motorcycles::delete/$1');

    $routes->get('parts', 'Parts::index');
    $routes->get('parts/new', 'Parts::new');
    $routes->post('parts', 'Parts::create');
    $routes->get('parts/(:num)/edit', 'Parts::edit/$1');
    $routes->post('parts/(:num)', 'Parts::update/$1');
    $routes->post('parts/(:num)/toggle', 'Parts::toggle/$1');
    $routes->post('parts/(:num)/delete', 'Parts::delete/$1');

    $routes->get('suppliers', 'Suppliers::index');
    $routes->get('suppliers/new', 'Suppliers::new');
    $routes->post('suppliers', 'Suppliers::create');
    $routes->get('suppliers/(:num)', 'Suppliers::show/$1');
    $routes->get('suppliers/(:num)/edit', 'Suppliers::edit/$1');
    $routes->post('suppliers/(:num)', 'Suppliers::update/$1');
    $routes->post('suppliers/(:num)/delete', 'Suppliers::delete/$1');
});

$routes->group('admin', ['filter' => ['auth', 'admin']], static function (RouteCollection $routes): void {
    $routes->get('settings', 'Admin\\Settings::index');
    $routes->post('settings', 'Admin\\Settings::update');
    $routes->post('settings/test-email', 'Admin\\Settings::testEmail');

    $routes->get('users', 'Admin\\Users::index');
    $routes->post('users', 'Admin\\Users::create');
    $routes->post('users/(:num)/toggle', 'Admin\\Users::toggle/$1');
    $routes->post('users/(:num)/password', 'Admin\\Users::password/$1');
    $routes->post('users/(:num)/delete', 'Admin\\Users::delete/$1');
});
