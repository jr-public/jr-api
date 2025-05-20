<?php
namespace App\Service;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService {
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function registration(UserRegistrationDTO $dto): array {
        // Validate the DTO first
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        // Continue with existing registration logic
        $user = new User();
        $user->setUsername($dto->username);
        $user->setEmail($dto->email);
        $user->setClient($dto->client);
        $user->setPassword($dto->password);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $jwt_s = new JWTService();
        $token = $jwt_s->createActivationToken($user->get('id'));
        
        return ['token' => $token, 'user' => $user->toArray()];
    }
    public function activation(string $token): User {
        $jwt_s = new JWTService();
        $userId = $jwt_s->validateActivationToken($token);
        
        if (!$userId) {
            throw new \InvalidArgumentException('Invalid or expired activation token');
        }
        
        // Find the user
        $user = $this->entityManager->find(User::class, $userId);
        
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }
        
        // Check if already activated
        if ($user->get('status') === 'active') {
            return $user; // Already activated
        }
        
        // Update user status to active
        $user->activate();
        $this->entityManager->flush();
        
        return $user;
    }
}