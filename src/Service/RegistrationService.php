<?php
namespace App\Service;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService {
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly ValidatorInterface $validator
    ) {}

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
        $token = $jwt_s->createToken([
            'sub'   => $user->get('id'),
            'type'  => 'activation'
        ]);
        return ['token' => $token, 'user' => $user->toArray()];
    }
    public function activation(string $token): User {
        $jwt_s      = new JWTService();
        $decoded    = $jwt_s->validateToken($token, [
            'type' => 'activation'
        ]);
        $userId     = $decoded->sub;
        $user       = $this->entityManager->find(User::class, $userId);
        if (!$user) {
            throw new \Exception('User not found');
        }
        if ($user->get('status') === 'active') {
            return $user;
        }
        // Update user status
        $ums = new UserManagementService($this->entityManager, $user);
        $ums->activate($user);
        
        return $user;
    }
}