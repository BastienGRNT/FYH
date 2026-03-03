<?php

namespace App\Controllers\Api;

use App\Models\Hackathon;
use App\Models\User;
use App\Services\AuthService;
use App\Services\EmailService;
use Exception;

class ApiController extends BaseApiController
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    private function authenticate(): void
    {
        try {
            $this->authService->validateToken($this->getAuthHeader());
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], $e->getCode() ?: 401);
        }
    }

    public function login(): void
    {
        $data = $this->getRequestData();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::findByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            $token = $this->authService->generateToken((object)[
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ]);
            $this->json(['token' => $token]);
        }

        $this->json(['error' => 'Identifiants incorrects'], 401);
    }

    public function getAll(): void
    {
        $page = (int)($_GET['page'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 10);

        if ($page > 0) {
            $this->json(Hackathon::findPaginated($page, $limit));
        }

        $this->json(Hackathon::findAll());
    }

    public function getById(int $id): void
    {
        $hackathon = Hackathon::findById($id);

        if ($hackathon) {
            $this->json($hackathon);
        }

        $this->json(['error' => 'Non trouvé'], 404);
    }

    public function create(): void
    {
        $this->authenticate();

        $data = $this->getRequestData();
        $hackathon = new Hackathon();

        $hackathon->fill($data);
        $this->handleBase64Image($hackathon, $data);

        if ($hackathon->save()) {
            $emailService = new EmailService();
            $emailService->sendHackathonConfirmation($hackathon, false);

            $this->json(['success' => true, 'id' => $hackathon->getId()], 201);
        }

        $this->json(['error' => 'Erreur serveur'], 500);
    }

    public function update(int $id): void
    {
        $this->authenticate();

        $hackathon = Hackathon::findById($id);

        if (!$hackathon) {
            $this->json(['error' => 'Non trouvé'], 404);
        }

        $data = $this->getRequestData();

        $hackathon->fill($data);
        $this->handleBase64Image($hackathon, $data);

        if ($hackathon->save()) {
            $emailService = new EmailService();
            $emailService->sendHackathonConfirmation($hackathon, true);

            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Erreur serveur'], 500);
    }

    public function delete(int $id): void
    {
        $this->authenticate();

        $hackathon = Hackathon::findById($id);

        if (!$hackathon) {
            $this->json(['error' => 'Non trouvé'], 404);
        }

        if ($hackathon->delete()) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Erreur serveur'], 500);
    }

    private function handleBase64Image(Hackathon $hackathon, array $data): void
    {
        if (empty($data['photo_base64'])) {
            return;
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        $base64String = $data['photo_base64'];
        $extension = 'png';

        if (strpos($base64String, ';base64,') !== false) {
            $imageParts = explode(";base64,", $base64String);
            $imageTypeAux = explode("image/", $imageParts[0]);
            $extension = $imageTypeAux[1] ?? 'png';
            $base64String = $imageParts[1];
        }

        $imageBase64 = base64_decode($base64String);

        if ($imageBase64 === false) {
            return;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '.' . $extension;

        if (file_put_contents($uploadDir . $filename, $imageBase64)) {
            $hackathon->setPhotoUrl('/uploads/' . $filename);
        }
    }
}