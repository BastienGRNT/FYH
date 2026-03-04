<?php

namespace App\Controllers;

use App\Models\Hackathon;
use Exception;
use PDOException;

class HomeController extends BaseController
{
    public function index(): void
    {
        try {
            $hackathons = Hackathon::findAll();
        } catch (PDOException $e) {
            error_log("Home index error (DB): " . $e->getMessage());
            $hackathons = [];
            $this->addFlash('danger', 'Une erreur est survenue lors de la récupération des événements.');
        }

        $this->render('home.html.twig', [
            'hackathons' => $hackathons,
            'title' => 'Accueil - Find Your Hackathon'
        ]);
    }

    public function search(): void
    {
        header('Content-Type: application/json');

        try {
            $term = $_GET['q'] ?? '';
            echo json_encode(Hackathon::search($term));

        } catch (Exception $e) {
            error_log("Home search error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur lors de la recherche']);
        }

        exit;
    }

    public function show(): void
    {
        if (!isset($_GET['id'])) {
            $this->addFlash('warning', 'Aucun identifiant de hackathon spécifié.');
            header('Location: /');
            exit;
        }

        try {
            $hackathon = Hackathon::findById((int)$_GET['id']);

            if (!$hackathon) {
                $this->addFlash('warning', 'Ce hackathon n\'existe pas ou a été supprimé.');
                header('Location: /');
                exit;
            }

            $this->render('hackathon.html.twig', [
                'hackathon' => $hackathon,
                'title' => $hackathon->getNom()
            ]);

        } catch (Exception $e) {
            error_log("Home show error: " . $e->getMessage());
            $this->addFlash('danger', 'Erreur technique lors de la récupération des détails.');
            header('Location: /');
            exit;
        }
    }
}