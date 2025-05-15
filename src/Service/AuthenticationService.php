<?php
namespace App\Service;

use App\DTO\UserAuthDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AuthenticationService {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }


    public function authenticate(UserAuthDTO $dto): ?User {
        // Get the default repository for the User entity
        $userRepository = $this->entityManager->getRepository(User::class);

        // Find the user by email
        /** @var User|null $user */
        $user = $userRepository->findOneBy(['username' => $dto->username]);

        // If user not found, authentication fails
        if (!$user) {
            return null;
        }

        // IMPORTANT: Comparing plain text passwords. This is insecure and MUST be changed
        // to use password_verify() with hashed passwords in a production environment.
        if ($user->get('password') === $dto->password) {
            // Authentication successful
            return $user;
        } else {
            // Password does not match
            return null;
        }
    }
}