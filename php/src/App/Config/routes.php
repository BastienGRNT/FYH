<?php

use App\Controllers\AdminController;
use App\Controllers\Api\ApiController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;

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

    $router->get('/admin/search', function() {
        (new AdminController())->search();
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

    $router->get('/uploads/(.*)', function($filename) {
        if (empty(trim($filename)) || str_contains($filename, '..')) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }

        $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $filename;

        if (is_file($path)) {
            session_write_close();

            $mime = mime_content_type($path) ?: 'application/octet-stream';
            header('Content-Type: ' . $mime);
            readfile($path);
            exit;
        }

        header("HTTP/1.0 404 Not Found");
        exit;
    });

    $router->mount('/api', function() use ($router) {
        $router->post('/login', function() {
            (new ApiController())->login();
        });

        $router->get('/hackathons', function() {
            (new ApiController())->getAll();
        });

        $router->get('/hackathons/(\d+)', function($id) {
            (new ApiController())->getById((int)$id);
        });

        $router->post('/hackathons', function() {
            (new ApiController())->create();
        });

        $router->put('/hackathons/(\d+)', function($id) {
            (new ApiController())->update((int)$id);
        });

        $router->delete('/hackathons/(\d+)', function($id) {
            (new ApiController())->delete((int)$id);
        });
    });
};
