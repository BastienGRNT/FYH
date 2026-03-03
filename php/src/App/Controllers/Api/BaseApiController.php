<?php

namespace App\Controllers\Api;

use JetBrains\PhpStorm\NoReturn;

class BaseApiController
{
    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    #[NoReturn]
    protected function json(array|object $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function getRequestData(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function getAuthHeader(): ?string
    {
        $headers = getallheaders();
        return $headers['Authorization'] ?? $headers['authorization'] ?? null;
    }
}