<?php

namespace App\Controllers;

use App\Repositories\HackathonRepository;
use JetBrains\PhpStorm\NoReturn;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController extends BaseController {

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(): void
    {
        $repo = new HackathonRepository();
        $hackathons = $repo->findAll();

        $this->render('home.html.twig', [
            'hackathons' => $hackathons,
            'title' => 'Accueil - Find Your Hackathon'
        ]);
    }

    #[NoReturn]
    public function search(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';
        $repo = new HackathonRepository();

        echo json_encode($repo->search($term));
        exit;
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function show(): void
    {
        if (!isset($_GET['id'])) {
            header('Location: /');
            exit;
        }

        $repo = new \App\Repositories\HackathonRepository();
        $hackathon = $repo->findById((int)$_GET['id']);

        if (!$hackathon) {
            header('Location: /');
            exit;
        }

        $this->render('hackathon.html.twig', [
            'title' => $hackathon->nom,
            'hackathon' => $hackathon
        ]);
    }
}