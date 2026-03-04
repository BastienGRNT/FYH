<?php

namespace App\Controllers;

use App\Models\User;
use Exception;

class AuthController extends BaseController
{
    public function login(): void
    {
        if (isset($_SESSION['user']) && $_SESSION['user']->getRole() !== 'admin') {
            header('Location: /admin/dashboard');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    throw new Exception("CSRF Validation failed", 403);
                }

                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'] ?? '';

                $user = User::findByEmail($email);

                if ($user && password_verify($password, $user->getPassword())) {
                    $user->setPassword('');
                    $_SESSION['user'] = $user;

                    $this->addFlash('success', 'Bienvenue sur le tableau de bord.');
                    header('Location: /admin/dashboard');
                    exit;
                }

                $error = "Identifiants incorrects.";

            } catch (Exception $e) {
                error_log("Auth login error: " . $e->getMessage());
                if ($e->getCode() === 403) {
                    $error = "Erreur de sécurité (CSRF). Veuillez réessayer.";
                } else {
                    $error = "Une erreur technique est survenue.";
                }
            }
        }

        $this->render('auth/login.html.twig', [
            'title' => 'Connexion',
            'error' => $error
        ]);
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
        $this->addFlash('info', 'Vous avez été déconnecté avec succès.');
        header('Location: /admin/login');
        exit;
    }
}