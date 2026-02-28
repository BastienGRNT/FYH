<?php

use App\Controllers\ApiController;
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

    $router->get('/uploads/(.*)', function($filename) {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $filename;

        if (file_exists($path)) {
            $mime = mime_content_type($path);
            header('Content-Type: ' . $mime);
            readfile($path);
            exit;
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "L'image n'est pas trouvée physiquement ici : " . $path;
        }
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