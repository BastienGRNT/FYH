<?php
namespace App\Controllers;

use App\Repositories\HackathonRepository;
use App\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class ApiController
{
    private HackathonRepository $repo;

    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->repo = new HackathonRepository();
    }

    private function checkAuth(): void
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant ou mal formaté']);
            exit;
        }

        $jwt = $matches[1];

        try {
            JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide ou expiré : ' . $e->getMessage()]);
            exit;
        }
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $userRepo = new UserRepository();
        $user = $userRepo->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            $payload = [
                'iat' => time(),
                'exp' => time() + (60 * 60 * 2),
                'id' => $user->id,
                'email' => $user->email
            ];

            // Encodage avec la librairie Firebase
            $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

            echo json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants incorrects']);
        }
    }

    public function getAll(): void
    {
        echo json_encode($this->repo->findAll());
    }

    public function getById(int $id): void
    {
        $hackathon = $this->repo->findById($id);
        if ($hackathon) {
            echo json_encode($hackathon);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Non trouvé']);
        }
    }

    public function create(): void
    {
        $this->checkAuth();

        $data = json_decode(file_get_contents('php://input'), true);
        $insertData = [
            ':nom' => $data['nom'] ?? '',
            ':description' => $data['description'] ?? '',
            ':date_event' => $data['date_event'] ?? '',
            ':prix' => (float)($data['prix'] ?? 0),
            ':latitude' => (float)($data['latitude'] ?? 0),
            ':longitude' => (float)($data['longitude'] ?? 0),
            ':ville' => $data['ville'] ?? '',
            ':email_organisateur' => $data['email_organisateur'] ?? '',
            ':photo_url' => $data['photo_url'] ?? null
        ];

        if ($this->repo->create($insertData)) {
            http_response_code(201);
            echo json_encode(['success' => true, 'id' => $this->repo->getLastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }

    public function update(int $id): void
    {
        $this->checkAuth();

        if (!$this->repo->findById($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Non trouvé']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $updateData = [
            ':nom' => $data['nom'] ?? '',
            ':description' => $data['description'] ?? '',
            ':date_event' => $data['date_event'] ?? '',
            ':prix' => (float)($data['prix'] ?? 0),
            ':latitude' => (float)($data['latitude'] ?? 0),
            ':longitude' => (float)($data['longitude'] ?? 0),
            ':ville' => $data['ville'] ?? '',
            ':email_organisateur' => $data['email_organisateur'] ?? '',
            ':photo_url' => $data['photo_url'] ?? null
        ];

        if ($this->repo->update($id, $updateData)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }

    public function delete(int $id): void
    {
        $this->checkAuth();

        if ($this->repo->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }
}