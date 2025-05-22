<?php
namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
    public function validateToken(string $token, array $requiredClaims = []): object {
        $decoded = JWT::decode($token, new Key($this->secretKey, $this->algo));
        foreach ($requiredClaims as $key => $expected) {
            if (!isset($decoded->$key) || $decoded->$key !== $expected) {
                throw new \Exception('Invalid token');
            }
        }
        return $decoded;
    }
    public function refreshToken(string $token, ?int $ttl = null): string {
        $decoded = $this->validateToken($token);
        return $this->createToken([
            'iss' => $decoded->iss,
            'sub' => $decoded->sub,
            'dev' => $decoded->dev,
            'type' => $decoded->type
        ], $ttl);
    }
}
