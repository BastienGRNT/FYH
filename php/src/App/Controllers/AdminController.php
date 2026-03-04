<?php

namespace App\Controllers;

use App\Models\Hackathon;
use App\Services\EmailService;
use Exception;
use PDOException;

class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'admin') {
            header('Location: /admin/login');
            exit;
        }
    }

    public function index(): void
    {
        try {
            $hackathons = Hackathon::findAll();
        } catch (PDOException $e) {
            error_log("Admin index error (DB): " . $e->getMessage());
            $hackathons = [];
            $this->addFlash('danger', 'Erreur lors du chargement des hackathons.');
        }

        $this->render('admin/dashboard.html.twig', [
            'title' => 'Tableau de bord',
            'hackathons' => $hackathons
        ]);
    }

    public function delete(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    throw new Exception("CSRF Validation failed", 403);
                }

                $hackathon = Hackathon::findById((int)$_POST['id']);
                if ($hackathon) {
                    $hackathon->delete();
                    $this->addFlash('success', 'Hackathon supprimé avec succès.');
                }
            }
        } catch (Exception $e) {
            error_log("Admin delete error: " . $e->getMessage());
            $this->addFlash('danger', 'Erreur lors de la suppression : action non autorisée.');
        }

        header('Location: /admin/dashboard');
        exit;
    }

    public function form(): void
    {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $hackathon = $id ? Hackathon::findById($id) : new Hackathon();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    throw new Exception("CSRF Validation failed", 403);
                }

                $hackathon->fill($_POST);

                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
                    $filename = uniqid() . '-' . basename($_FILES['photo']['name']);

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                        $hackathon->setPhotoUrl('/uploads/' . $filename);
                    } else {
                        throw new Exception("File upload failed");
                    }
                }

                if ($hackathon->save()) {
                    $this->addFlash('success', 'Hackathon ' . ($id ? 'modifié' : 'ajouté') . ' avec succès.');

                    try {
                        $emailService = new EmailService();
                        $emailService->sendHackathonConfirmation($hackathon, $id !== null);
                    } catch (Exception $e) {
                        error_log("EmailService error in Admin form: " . $e->getMessage());
                    }

                    header('Location: /admin/dashboard');
                    exit;
                }

                $this->addFlash('danger', 'Une erreur est survenue lors de l\'enregistrement.');
            }

            $this->render('admin/form.html.twig', [
                'title' => $id ? 'Modifier un Hackathon' : 'Ajouter un Hackathon',
                'hackathon' => $hackathon
            ]);

        } catch (Exception $e) {
            error_log("Admin form error: " . $e->getMessage());
            $this->addFlash('danger', 'Action non autorisée ou erreur technique.');

            header('Location: /admin/dashboard');
            exit;
        }
    }

    public function search(): void
    {
        header('Content-Type: application/json');

        try {
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';

            if (empty($query)) {
                echo json_encode(Hackathon::findAll());
                return;
            }

            $hackathons = Hackathon::search($query);
            echo json_encode($hackathons);

        } catch (Exception $e) {
            error_log("Admin search error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur lors de la recherche']);
        }
    }
}