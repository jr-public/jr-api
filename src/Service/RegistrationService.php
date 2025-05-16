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

    public function registration(UserRegistrationDTO $dto): User {
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
        
        return $user;
    }
}