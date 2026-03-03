<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthService
{
    /**
     * @throws Exception
     */
    public function validateToken(?string $authHeader): void
    {
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new Exception('Token manquant ou mal formaté', 401);
        }

        try {
            JWT::decode($matches[1], new Key($_ENV['JWT_SECRET'], 'HS256'));
        } catch (Exception $e) {
            throw new Exception('Token invalide ou expiré : ' . $e->getMessage(), 401);
        }
    }

    public function generateToken(object $user): string
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60 * 2),
            'id' => $user->id,
            'email' => $user->email
        ];

        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }
}