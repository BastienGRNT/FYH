<?php

return function($router) {
    $router->get('/', function() {
        echo "Bienvenue sur l'API FYH !!!!";
    });

    $router->get('/test', function() {
        echo "Test réussi";
    });
};