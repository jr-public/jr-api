<?php
namespace App\Service;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationService {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function registration(UserRegistrationDTO $dto): User {
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