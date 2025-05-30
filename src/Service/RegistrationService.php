<?php
namespace App\Service;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use App\Service\RequestContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService {
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly RequestContextService $context,
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
        $user->setPassword($dto->password);
        $user->setClient($this->context->getClient());
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $jwt_s = new JWTService();
        $token = $jwt_s->createToken([
            'iss'   => $this->context->getClient()->get('id'),
            'sub'   => $user->get('id'),
            'type'  => 'activation'
        ]);
        return ['token' => $token, 'user' => $user->toArray()];
    }
    public function activation(string $token): User {
        $jwt_s      = new JWTService();
        $decoded    = $jwt_s->validateToken($token, [
            'iss'   => $this->context->getClient()->get('id'),
            'type'  => 'activation'
        ]);
        $user       = $this->entityManager->find(User::class, $decoded->sub);
        if (!$user) {
            throw new \Exception('User not found');
        }
        if ($user->get('status') === 'active') {
            return $user;
        }
        $user->activate();
        $this->entityManager->flush();
        return $user;
    }
}