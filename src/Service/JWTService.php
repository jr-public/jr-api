<?php
namespace App\Service;

use App\Exception\AuthException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JWTService {

    private string $secretKey;
    private string $algo;
    private int $defaultTtl;

    public function __construct(?string $secretKey = null, string $algo = 'HS256', int $defaultTtl = 3600) {
        $this->secretKey    = $secretKey ?? getenv('JWT_SECRET');
        $this->algo         = $algo;
        $this->defaultTtl   = $defaultTtl;
    }

    public function create(array $claims, ?int $ttl = null): string {
        $now = time();
        $ttl = $ttl ?? $this->defaultTtl;
        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);
        return JWT::encode($payload, $this->secretKey, $this->algo);
    }
    public function decode(string $token): object {
        try {
            return JWT::decode($token, new Key($this->secretKey, $this->algo));
        } catch (ExpiredException $e) {
            throw new AuthException('BAD_TOKEN', 'Token has expired');
        } catch (SignatureInvalidException $e) {
            throw new AuthException('BAD_TOKEN', 'Invalid token signature');
        } catch (\Exception $e) {
            throw new AuthException('BAD_TOKEN', 'Invalid token: ' . $e->getMessage());
        }
    }
}
