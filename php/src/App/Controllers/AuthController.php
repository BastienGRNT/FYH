<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use JetBrains\PhpStorm\NoReturn;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AuthController extends BaseController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function login(): void
    {
        if (isset($_SESSION['user'])) {
            header('Location: /admin/dashboard');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            $repo = new UserRepository();
            $user = $repo->findByEmail($email);

            if ($user && password_verify($password, $user->password)) {
                unset($user->password);
                $_SESSION['user'] = $user;

                header('Location: /admin/dashboard');
                exit;
            } else {
                $error = "Identifiants incorrects.";
            }
        }

        $this->render('auth/login.html.twig', [
            'title' => 'Connexion',
            'error' => $error
        ]);
    }

    #[NoReturn]
    public function logout(): void
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}