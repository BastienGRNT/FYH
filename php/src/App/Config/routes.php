<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;

return function($router) {
    $router->get('/', function() {
        (new HomeController())->index();
    });

    $router->get('/admin/login', function() {
        (new AuthController())->login();
    });

    $router->post('/admin/login', function() {
        (new AuthController())->login();
    });

    $router->get('/admin/logout', function() {
        (new AuthController())->logout();
    });

    $router->get('/admin/dashboard', function() {
        (new AdminController())->index();
    });

    $router->post('/admin/hackathon/delete', function() {
        (new AdminController())->delete();
    });

    $router->get('/admin/hackathon/add', function() {
        (new AdminController())->form();
    });
    $router->post('/admin/hackathon/add', function() {
        (new AdminController())->form();
    });

    $router->get('/admin/hackathon/edit', function() {
        (new AdminController())->form();
    });

    $router->post('/admin/hackathon/edit', function() {
        (new AdminController())->form();
    });

    $router->get('/search', function() {
        (new HomeController())->search();
    });

    $router->get('/hackathon', function() {
        (new HomeController())->show();
    });
};