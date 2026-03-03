<?php

namespace App\Controllers;

use App\Models\User;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AuthController extends BaseController
{
    public function login(): void
    {
        if (isset($_SESSION['user'])) {
            header('Location: /admin/dashboard');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Erreur CSRF : Action non autorisée.");
            }

            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            $user = User::findByEmail($email);

            if ($user && password_verify($password, $user->getPassword())) {
                $user->setPassword('');
                $_SESSION['user'] = $user;

                header('Location: /admin/dashboard');
                exit;
            }

            $error = "Identifiants incorrects.";
        }

        $this->render('auth/login.html.twig', [
            'title' => 'Connexion',
            'error' => $error
        ]);
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /admin/login');
        exit;
    }
}