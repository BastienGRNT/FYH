<?php

namespace App\Controllers\Api;

use App\Models\Hackathon;
use App\Models\User;
use App\Services\AuthService;
use App\Services\EmailService;
use Exception;
use PDOException;

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
            $code = $e->getCode();
            $httpCode = (is_numeric($code) && $code >= 400 && $code < 600) ? (int)$code : 401;

            $this->json(['error' => $e->getMessage()], $httpCode);
        }
    }

    private function authenticateAdmin(): void
    {
        try {
            $this->authService->verifyAdmin($this->getAuthHeader());
        } catch (Exception $e) {
            $code = $e->getCode();
            $httpCode = (is_numeric($code) && $code >= 400 && $code < 600) ? (int)$code : 401;

            $this->json(['error' => $e->getMessage()], $httpCode);
            exit;
        }
    }

    public function login(): void
    {
        try {
            $data = $this->getRequestData();
            $email = trim($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->json(['error' => 'Requête invalide : identifiants manquants'], 400);
            }

            $user = User::findByEmail($email);

            if ($user && password_verify($password, $user->getPassword())) {
                $token = $this->authService->generateToken((object)[
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                ]);
                $this->json(['token' => $token]);
            }

            $this->json(['error' => 'Identifiants incorrects'], 401);

        } catch (PDOException $e) {
            error_log("DB Error in login: " . $e->getMessage());
            $this->json(['error' => 'Erreur interne du serveur'], 500);
        } catch (Exception $e) {
            error_log("Error in login: " . $e->getMessage());
            $this->json(['error' => 'Erreur inattendue'], 500);
        }
    }

    public function getAll(): void
    {
        try {
            $page = (int)($_GET['page'] ?? 0);
            $limit = (int)($_GET['limit'] ?? 10);

            if ($page > 0) {
                $this->json(Hackathon::findPaginated($page, $limit));
            }

            $this->json(Hackathon::findAll());

        } catch (PDOException $e) {
            error_log("DB Error in getAll: " . $e->getMessage());
            $this->json(['error' => 'Erreur interne lors de la récupération des données'], 500);
        }
    }

    public function getById(int $id): void
    {
        try {
            $hackathon = Hackathon::findById($id);

            if ($hackathon) {
                $this->json($hackathon);
            }

            $this->json(['error' => 'Non trouvé'], 404);

        } catch (PDOException $e) {
            error_log("DB Error in getById: " . $e->getMessage());
            $this->json(['error' => 'Erreur interne lors de la récupération des données'], 500);
        }
    }

    public function create(): void
    {
        $this->authenticate();

        try {
            $data = $this->getRequestData();

            if (empty($data['nom']) || empty($data['date_event'])) {
                $this->json(['error' => 'Données incomplètes'], 400);
            }

            $hackathon = new Hackathon();
            $hackathon->fill($data);
            $this->handleBase64Image($hackathon, $data);

            if ($hackathon->save()) {
                try {
                    $emailService = new EmailService();
                    $emailService->sendHackathonConfirmation($hackathon, false);
                } catch (Exception $e) {
                    error_log("EmailService Error (Create): " . $e->getMessage());
                    // On ne bloque pas la réponse si seul l'email échoue
                }

                $this->json(['success' => true, 'id' => $hackathon->getId()], 201);
            }

            $this->json(['error' => 'Erreur lors de l\'enregistrement'], 500);

        } catch (PDOException $e) {
            error_log("DB Error in create: " . $e->getMessage());
            $this->json(['error' => 'Erreur de base de données'], 500);
        } catch (Exception $e) {
            error_log("Error in create: " . $e->getMessage());
            $this->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }

    public function update(int $id): void
    {
        $this->authenticate();

        try {
            $hackathon = Hackathon::findById($id);

            if (!$hackathon) {
                $this->json(['error' => 'Non trouvé'], 404);
            }

            $data = $this->getRequestData();
            $hackathon->fill($data);
            $this->handleBase64Image($hackathon, $data);

            if ($hackathon->save()) {
                try {
                    $emailService = new EmailService();
                    $emailService->sendHackathonConfirmation($hackathon, true);
                } catch (Exception $e) {
                    error_log("EmailService Error (Update): " . $e->getMessage());
                }

                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Erreur lors de la mise à jour'], 500);

        } catch (PDOException $e) {
            error_log("DB Error in update: " . $e->getMessage());
            $this->json(['error' => 'Erreur de base de données'], 500);
        } catch (Exception $e) {
            error_log("Error in update: " . $e->getMessage());
            $this->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }

    public function delete(int $id): void
    {
        $this->authenticate();

        try {
            $hackathon = Hackathon::findById($id);

            if (!$hackathon) {
                $this->json(['error' => 'Non trouvé'], 404);
            }

            if ($hackathon->delete()) {
                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Erreur lors de la suppression'], 500);

        } catch (PDOException $e) {
            error_log("DB Error in delete: " . $e->getMessage());
            $this->json(['error' => 'Erreur de base de données'], 500);
        } catch (Exception $e) {
            error_log("Error in delete: " . $e->getMessage());
            $this->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }

    private function handleBase64Image(Hackathon $hackathon, array $data): void
    {
        if (empty($data['photo_base64'])) {
            return;
        }

        try {
            $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/';
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
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    throw new Exception("Impossible de créer le répertoire d'upload");
                }
            }

            $filename = uniqid() . '.' . $extension;
            $filePath = $uploadDir . $filename;

            if (file_put_contents($filePath, $imageBase64) !== false) {
                $hackathon->setPhotoUrl('/uploads/' . $filename);
            } else {
                throw new Exception("Échec de l'écriture du fichier image");
            }

        } catch (Exception $e) {
            error_log("Error in handleBase64Image: " . $e->getMessage());
        }
    }
}