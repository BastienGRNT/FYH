<?php

use App\Controllers\HomeController;

return function($router) {
    $router->get('/', function() {
        $controller = new HomeController();
        $controller->index();
    });

    $router->get('/test', function() {
        echo "Test réussi";
    });
};