<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
class AuthService {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function login( string $username, string $password, array $claims = [] ): array {
        $user   = $this->authenticate($username, $password, $claims['iss'] ?? '');
        $user   = $this->authorize($user);
        $claims['sub'] = $user->get('id');
        $jwt_s  = new JwtService();
        $token  = $jwt_s->createToken($claims);
        return ['token' => $token, 'user' => $user->toArray()];
    }
    public function authenticate( string $username, string $password, string $client_id ): User {
        // Get the default repository for the User entity
        $client = $this->entityManager->find(Client::class, $client_id);
        if (!$client) {
            throw new \Exception('CLIENT_NOT_FOUND');
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $username, 'client' => $client]);

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

        return $user;
    }

}