<?php

namespace App\Service;

use App\Service\JWTService;
use App\Service\RequestContextService;
use App\Entity\User;
use App\Exception\AuthException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles user authentication and authorization operations
 */
class AuthService
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param JWTService             $jwts
     * @param RequestContextService  $context
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTService $jwts,
        private readonly RequestContextService $context
    ) {
    }
    /**
     * Creates a JWT token for the given user
     *
     * @param  User   $user The user to create token for
     * @param  string $type Token type (default: 'session')
     * @return string The generated JWT token
     */
    public function createToken(User $user, string $type = 'session'): string
    {
        $token  = $this->jwts->create(
            [
            'sub' => $user->get('id'),
            'iss' => $this->context->getClient()->get('id'),
            'dev' => $this->context->getDevice(),
            'type' => $type
            ]
        );
        return $token;
    }
    /**
     * Renews the current user's token
     *
     * @return string The new JWT token
     */
    public function renewToken(): string
    {
        $user = $this->context->getUser();
        $token = $this->createToken($user);
        return $token;
    }
    /**
     * Authenticates user credentials and returns login data
     *
     * @param  string $username
     * @param  string $password
     * @return array Contains 'token' and 'user' keys
     * @throws AuthException When credentials are invalid
     */
    public function login(string $username, string $password): array
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findByUsernameAndClient($username, $this->context->getClient()->get('id'));
        if (!$user) {
            throw new AuthException('BAD_CREDENTIALS', 'Invalid username');
        } elseif (!password_verify($password, $user->get('password'))) {
            throw new AuthException('BAD_CREDENTIALS', 'Invalid password');
        }
        return ['token' => $this->createToken($user), 'user' => $user->toArray()];
    }
    /**
     * Validates JWT token and returns the authorized user
     *
     * @param  string $jwt The JWT token to validate
     * @return User The authorized user
     * @throws AuthException When token is invalid or user is not active
     */
    public function authorize(string $jwt): User
    {
        $decoded = $this->jwts->decode($jwt);
        if (!isset($decoded->sub)) {
            throw new AuthException('BAD_TOKEN', 'Invalid token: missing user identifier');
        }
        if (!isset($decoded->iss) || $decoded->iss !== $this->context->getClient()->get('id')) {
            throw new AuthException('BAD_TOKEN', 'Invalid token: client mismatch');
        }
        if (!isset($decoded->dev) || $decoded->dev !== $this->context->getDevice()) {
            throw new AuthException('BAD_TOKEN', 'Invalid token: device mismatch');
        }
        if (!isset($decoded->type) || $decoded->type !== 'session') {
            throw new AuthException('BAD_TOKEN', 'Invalid token: invalid token type');
        }
        $user = $this->entityManager->find(User::class, $decoded->sub);
        if (!$user) {
            throw new AuthException('BAD_TOKEN', 'Invalid token - user not found');
        }
        if ($user->get('status') !== 'active') {
            throw new AuthException('NOT_ACTIVE', 'Account is not active');
        }
        if ($user->get('reset_password')) {
            throw new AuthException('RESET_PASSWORD', 'Password reset required');
        }
        return $user;
    }
    /**
     * Extracts JWT token from Authorization header
     *
     * @param  Request $request
     * @return string The JWT token
     * @throws AuthException When Authorization header is missing or invalid
     */
    public function extractJwt(Request $request): string
    {
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7); // Remove "Bearer " prefix
        }
        throw new AuthException('BAD_TOKEN', 'Missing or invalid Authorization header');
    }
}
