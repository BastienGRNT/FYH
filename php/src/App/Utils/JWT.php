<?php
namespace App\Utils;

class JWT
{
    public static function generate(array $payload, int $expiresIn = 3600): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + $expiresIn;
        $payloadJson = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $_ENV['JWT_SECRET'], true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verify(string $jwt): ?object
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;

        list($header, $payload, $signature) = $parts;

        $validSignature = hash_hmac('sha256', $header . "." . $payload, $_ENV['JWT_SECRET'], true);
        $base64UrlValidSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));

        if (!hash_equals($base64UrlValidSignature, $signature)) return null;

        $payloadData = json_decode(base64_decode($payload));
        if (isset($payloadData->exp) && $payloadData->exp < time()) return null;

        return $payloadData;
    }
}