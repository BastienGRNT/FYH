<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Exception;

class BaseController {
    protected Environment $twig;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $loader = new FilesystemLoader(__DIR__ . '/../views');

        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        $this->twig->addGlobal('csrf_token', $_SESSION['csrf_token'] ?? '');

        $this->twig->addGlobal('is_logged_in', isset($_SESSION['user']));

        $flashes = $_SESSION['flash'] ?? [];
        $this->twig->addGlobal('flashes', $flashes);
        unset($_SESSION['flash']);
    }

    protected function addFlash(string $type, string $message): void
    {
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    protected function render($template, $data = []): void
    {
        try {
            echo $this->twig->render($template, $data);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            error_log("Twig Render Error ({$template}): " . $e->getMessage());

            http_response_code(500);
            if (ini_get('display_errors')) {
                echo "Erreur de rendu du template : " . $e->getMessage();
            } else {
                echo "Une erreur interne est survenue.";
            }
        }
    }
}