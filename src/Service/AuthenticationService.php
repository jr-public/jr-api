<?php
namespace App\Service;

use App\DTO\UserAuthDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class AuthenticationService {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }


    public function authenticate(UserAuthDTO $dto): User {
        // Get the default repository for the User entity
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $dto->username]);

        if (!$user) {
            throw new \Exception('USER_NOT_FOUND');
        }
        elseif ($user->get('password') !== $dto->password) {
            throw new \Exception('INVALID_PASSWORD');
        }
        elseif ($user->get('status') !== 'active') {
            throw new \Exception('USER_NOT_ACTIVE');
        }
        return $user;
    }
    public function generateToken( User $user, string $device ): string {
        $issued_at = time();
        $expiration = $issued_at + 3600; // Token valid for 1 hour

        $payload = [
            'iat' => $issued_at,           // Issued at
            'exp' => $expiration,          // Expiration time
            'sub' => $user->get('client')->get('id'), // Subject (client ID)
            'data' => $user->get('id'),
            'dev' => $device
        ];
        return JWT::encode($payload, getenv("JWT_SECRET"), 'HS256');
    }
    public function validateToken(string $token, string $device ): User {
		$decoded = JWT::decode($token, new Key(getenv("JWT_SECRET"), 'HS256'));
		if ( $decoded->dev != $device ) {
            throw new \Exception('BAD_DEVICE');
        }
		$user = $this->entityManager->find(User::class, $decoded->data);
        if (!$user) {
            throw new \Exception('USER_NOT_FOUND');
        }
        elseif ($user->get('status') !== 'active') {
            throw new \Exception('USER_NOT_ACTIVE');
        }
        return $user;
    }

}