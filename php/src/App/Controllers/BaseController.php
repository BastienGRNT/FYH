<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class BaseController {
    protected Environment $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../views');

        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        $this->twig->addGlobal('csrf_token', $_SESSION['csrf_token'] ?? '');
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    protected function render($template, $data = []): void
    {
        echo $this->twig->render($template, $data);
    }
}