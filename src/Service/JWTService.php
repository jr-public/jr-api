<?php

namespace App\Service;

use App\Exception\AuthException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Service for creating and decoding JWT tokens
 */
class JWTService
{
    private string $secretKey;
    private string $algo;
    private int $defaultTtl;

    /**
     * @param string|null $secretKey JWT secret key (defaults to JWT_SECRET env var)
     * @param string $algo JWT algorithm (default: HS256)
     * @param int $defaultTtl Default token TTL in seconds (default: 3600)
     */
    public function __construct(?string $secretKey = null, string $algo = 'HS256', int $defaultTtl = 3600)
    {
        $this->secretKey    = $secretKey ?? getenv('JWT_SECRET');
        $this->algo         = $algo;
        $this->defaultTtl   = $defaultTtl;
    }

    /**
     * Creates a JWT token with the given claims
     *
     * @param array $claims Token payload claims
     * @param int|null $ttl Token TTL in seconds (uses default if null)
     * @return string Encoded JWT token
     */
    public function create(array $claims, ?int $ttl = null): string
    {
        $now = time();
        $ttl = $ttl ?? $this->defaultTtl;
        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttl,
        ]);
        return JWT::encode($payload, $this->secretKey, $this->algo);
    }
    /**
     * Decodes and validates a JWT token
     *
     * @param string $token JWT token to decode
     * @return object Decoded token payload
     * @throws AuthException If token is expired, invalid signature, or malformed
     */
    public function decode(string $token): object
    {
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
