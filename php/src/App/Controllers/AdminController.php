<?php

namespace App\Controllers;

use App\Repositories\HackathonRepository;
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
        $repo = new HackathonRepository();
        $hackathons = $repo->findAll();

        $this->render('admin/dashboard.html.twig', [
            'title' => 'Tableau de bord',
            'hackathons' => $hackathons
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $repo = new HackathonRepository();
            $repo->delete((int)$_POST['id']);
        }

        header('Location: /admin/dashboard');
        exit;
    }

    public function form(): void
    {
        $repo = new HackathonRepository();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $hackathon = $id ? $repo->findById($id) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':nom' => $_POST['nom'] ?? '',
                ':description' => $_POST['description'] ?? '',
                ':date_event' => $_POST['date_event'] ?? '',
                ':prix' => (float)($_POST['prix'] ?? 0),
                ':latitude' => (float)($_POST['latitude'] ?? 0),
                ':longitude' => (float)($_POST['longitude'] ?? 0),
                ':ville' => $_POST['ville'] ?? '',
                ':email_organisateur' => $_POST['email_organisateur'] ?? ''
            ];

            if ($id) {
                $repo->update($id, $data);
            } else {
                $repo->create($data);

                $lastId = $repo->getLastInsertId();
                $this->sendConfirmationEmail($data, $lastId);
            }

            header('Location: /admin/dashboard');
            exit;
        }

        $this->render('admin/form.html.twig', [
            'title' => $id ? 'Modifier un Hackathon' : 'Ajouter un Hackathon',
            'hackathon' => $hackathon
        ]);
    }

    private function sendConfirmationEmail(array $data, int $id): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];
            $mail->Password   = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $_ENV['SMTP_PORT'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($_ENV['SMTP_USER'], 'Find Your Hackathon');
            $mail->addAddress($data[':email_organisateur']);

            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de création : ' . $data[':nom'];

            $urlDetails = "http://" . $_SERVER['HTTP_HOST'] . "/hackathon?id=" . $id;

            $mail->Body = "
                <h2>Votre Hackathon a bien été enregistré !</h2>
                <ul>
                    <li><strong>Nom :</strong> {$data[':nom']}</li>
                    <li><strong>Date :</strong> {$data[':date_event']}</li>
                    <li><strong>Ville :</strong> {$data[':ville']}</li>
                    <li><strong>Prix :</strong> {$data[':prix']} €</li>
                </ul>
                <p>Retrouvez la page de votre événement ici : <a href='{$urlDetails}'>Voir mon événement</a></p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email: {$mail->ErrorInfo}");
            die("❌ Erreur critique d'envoi d'email (PHPMailer) : " . $mail->ErrorInfo);
        }
    }
}