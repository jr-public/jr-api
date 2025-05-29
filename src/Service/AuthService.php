<?php
namespace App\Service;

use App\Service\JWTService;
use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
class AuthService {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function login( string $username, string $password, string $client_id, string $device ): array {
        $user   = $this->authenticate($username, $password, $client_id);
        $claims = [
            'sub' => $user->get('id'),
            'iss' => $client_id,
            'dev' => $device,
            'type' => 'session'
        ];
        $jwt_s  = new JWTService();
        $token  = $jwt_s->createToken($claims);
        return ['token' => $token, 'user' => $user->toArray()];
    }
    public function authenticate( string $username, string $password, string $client_id ): User {
        $dql = 'SELECT u FROM App\Entity\User u WHERE u.username = :username AND u.client = :client_id';
        $query = $this->entityManager->createQuery($dql)
            ->setParameter('username', $username)
            ->setParameter('client_id', $client_id);
        $user = $query->getOneOrNullResult();
        if (!$user) {
            throw new \Exception('USER_NOT_FOUND');
        }
        elseif ($user->get('password') !== $password) {
            throw new \Exception('INVALID_PASSWORD');
        }
        return $user;
    }

    public function authorize( User|string $data, array $requiredClaims = [] ): User {
        if ( $data instanceof User ) {
            $user = $data;
        }
        else {
            $jwt = $data;
            $decoded = new JWTService();
            $decoded = $decoded->validateToken($jwt, $requiredClaims);
            if (!$decoded) {
                throw new \Exception('INVALID_TOKEN');
            }
            $user = $this->entityManager->find(User::class, $decoded->sub);
            if (!$user) {
                throw new \Exception('USER_NOT_FOUND');
            }
        }

        if ($user->get('status') !== 'active') {
            throw new \Exception('USER_NOT_ACTIVE');
        }
        if ($user->get('reset_password')) {
            throw new \Exception('RESET_PASSWORD');
        }

        return $user;
    }
    public function extractJwt(Request $request): string {
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7); // Remove "Bearer " prefix
        }
        throw new \Exception('INVALID_TOKEN: '.$authHeader);
    }
}