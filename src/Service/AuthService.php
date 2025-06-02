<?php
namespace App\Service;

use App\Service\JWTService;
use App\Service\RequestContextService;
use App\Entity\User;
use App\Exception\AuthException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
class AuthService {
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JWTService $jwts,
        private readonly RequestContextService $context
    ) {}

    public function login( string $username, string $password ): array {
        $user   = $this->authenticate($username, $password);
        $jwts   = new JWTService();
        $token  = $jwts->create([
            'sub' => $user->get('id'),
            'iss' => $this->context->getClient()->get('id'),
            'dev' => $this->context->getDevice(),
            'type' => 'session'
        ]);
        return ['token' => $token, 'user' => $user->toArray()];
    }
    public function authenticate(string $username, string $password): User {
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findByUsernameAndClient($username, $this->context->getClient()->get('id'));
        if (!$user) {
            throw new AuthException('BAD_CREDENTIALS', 'Invalid username');
        }
        elseif (!password_verify($password, $user->get('password'))) {
            throw new AuthException('BAD_CREDENTIALS', 'Invalid password');
        }
        return $user;
    }

    public function authorize(string $jwt): User {
        $jwtService = new JWTService();
        $decoded = $jwtService->decode($jwt);
        if (!isset($decoded->sub)) {
            throw new AuthException('BAD_TOKEN', 'Invalid token: missing user identifier');
        }
        if (!isset($decoded->iss) || $decoded->iss !== $this->context->getClient()->get('id')) {
            throw new AuthException('BAD_TOKEN','Invalid token: client mismatch');
        }
        if (!isset($decoded->dev) || $decoded->dev !== $this->context->getDevice()) {
            throw new AuthException('BAD_TOKEN','Invalid token: device mismatch');
        }
        if (!isset($decoded->type) || $decoded->type !== 'session') {
            throw new AuthException('BAD_TOKEN','Invalid token: invalid token type');
        }
        $user = $this->entityManager->find(User::class, $decoded->sub);
        if (!$user) {
            throw new AuthException('BAD_TOKEN','Invalid token - user not found');
        }
        if ($user->get('status') !== 'active') {
            throw new AuthException('NOT_ACTIVE', 'Account is not active');
        }
        if ($user->get('reset_password')) {
            throw new AuthException('RESET_PASSWORD', 'Password reset required');
        }
        return $user;
    }
    public function extractJwt(Request $request): string {
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7); // Remove "Bearer " prefix
        }
        throw new AuthException('BAD_TOKEN', 'Missing or invalid Authorization header');
    }
}