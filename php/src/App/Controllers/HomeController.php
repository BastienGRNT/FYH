<?php

namespace App\Controllers;

use App\Models\Hackathon;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeController extends BaseController
{
    public function index(): void
    {
        $hackathons = Hackathon::findAll();

        $this->render('home.html.twig', [
            'hackathons' => $hackathons,
            'title' => 'Accueil - Find Your Hackathon'
        ]);
    }

    public function search(): void
    {
        header('Content-Type: application/json');

        $term = $_GET['q'] ?? '';

        echo json_encode(Hackathon::search($term));
        exit;
    }

    public function show(): void
    {
        if (!isset($_GET['id'])) {
            header('Location: /');
            exit;
        }

        $hackathon = Hackathon::findById((int)$_GET['id']);

        if (!$hackathon) {
            header('Location: /');
            exit;
        }

        $this->render('hackathon.html.twig', [
            'hackathon' => $hackathon,
            'title' => $hackathon->getNom()
        ]);
    }
}