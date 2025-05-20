<?php
namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class JWTService {

    private string $secretKey;
    private string $algo;
    private int $defaultTtl;

    public function __construct(?string $secretKey = null, string $algo = 'HS256', int $defaultTtl = 3600) {
        $this->secretKey    = $secretKey ?? getenv('JWT_SECRET');
        $this->algo         = $algo;
        $this->defaultTtl   = $defaultTtl;
    }

    public function createToken(array $claims, ?int $ttl = null): string {
        $now = time();
        $ttl = $ttl ?? $this->defaultTtl;

        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);

        return JWT::encode($payload, $this->secretKey, $this->algo);
    }
    
    public function decodeToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->secretKey, $this->algo));
        } catch (ExpiredException | SignatureInvalidException | BeforeValidException | \UnexpectedValueException $e) {
            return null; // invalid or expired
        }
    }

    public function validateToken(string $token, array $requiredClaims = []): ?object
    {
        $decoded = $this->decodeToken($token);

        if (!$decoded) return null;

        foreach ($requiredClaims as $key => $expected) {
            if (!isset($decoded->$key) || $decoded->$key !== $expected) {
                return null;
            }
        }

        return $decoded;
    }

    public function createActivationToken(int|string $userId, int $ttl = 86400): string
    {
        return $this->createToken([
            'sub' => $userId,
            'type' => 'activation',
        ], $ttl);
    }

    public function validateActivationToken(string $token): int|string|null
    {
        $decoded = $this->validateToken($token, ['type' => 'activation']);
        return $decoded?->sub ?? null;
    }

    // You can add similar methods for:
    // createPasswordResetToken(), validatePasswordResetToken()
    // createSessionToken(), validateSessionToken()
}
