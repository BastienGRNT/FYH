<?php

namespace App\Controllers;

use App\Repositories\HackathonRepository;
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
}