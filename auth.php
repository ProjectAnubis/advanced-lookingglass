<?php
declare(strict_types=1);
require_once __DIR__.'/vendor/autoload.php'; // Composer autoload (example firebase/php-jwt)

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static string $secret = 'YOUR_VERY_SECURE_SECRET'; // Secure key

    /**
     * Generates JWT tokens for the user.
     */
    public static function generateToken(array $payload): string {
        // Add expiration time to token for additional security
        $payload['exp'] = time() + 3600; // Valid for 1 hour
        return JWT::encode($payload, self::$secret, 'HS256');
    }

    /**
     * Verifies the incoming token.
     */
    public static function validateToken(string $token): ?array {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret, 'HS256'));
            return (array)$decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
