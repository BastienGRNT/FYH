<?php

namespace App\Controllers;

use App\Models\Hackathon;
use App\Services\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($_SESSION['user'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function index(): void
    {
        $hackathons = Hackathon::findAll();

        $this->render('admin/dashboard.html.twig', [
            'title' => 'Tableau de bord',
            'hackathons' => $hackathons
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Erreur CSRF : Action non autorisée.");
            }

            $hackathon = Hackathon::findById((int)$_POST['id']);
            if ($hackathon) {
                $hackathon->delete();
            }
        }

        header('Location: /admin/dashboard');
        exit;
    }

    public function form(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $hackathon = $id ? Hackathon::findById($id) : new Hackathon();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Erreur CSRF : Action non autorisée.");
            }

            $hackathon->fill($_POST);

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
                $filename = uniqid() . '-' . basename($_FILES['photo']['name']);
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    $hackathon->setPhotoUrl('/uploads/' . $filename);
                }
            }

            if ($hackathon->save()) {
                $emailService = new EmailService();
                $emailService->sendHackathonConfirmation($hackathon, $id !== null);

                header('Location: /admin/dashboard');
                exit;
            }
        }

        $this->render('admin/form.html.twig', [
            'title' => $id ? 'Modifier un Hackathon' : 'Ajouter un Hackathon',
            'hackathon' => $hackathon
        ]);
    }

    public function search(): void
    {
        header('Content-Type: application/json');

        $query = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (empty($query)) {
            echo json_encode(Hackathon::findAll());
            return;
        }

        $hackathons = Hackathon::search($query);

        echo json_encode($hackathons);
    }
}