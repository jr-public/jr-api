<?php
namespace App\Service;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Service\RequestContextService;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService {
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly JWTService $jwts, 
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
            throw new ValidationException('Validation failed: ' . implode(', ', $errors));
        }

        // Continue with existing registration logic
        $user = new User();
        $user->setUsername($dto->username);
        $user->setEmail($dto->email);
        $user->setPassword($dto->password);
        $user->setClient($this->context->getClient());
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $jwts = new JWTService();
        $token = $jwts->create([
            'iss'   => $this->context->getClient()->get('id'),
            'sub'   => $user->get('id'),
            'type'  => 'activation'
        ]);
        return ['token' => $token, 'user' => $user->toArray()];
    }
    public function activation(string $token): User {
        $jwts      = new JWTService();
        $requiredClaims = [
            'iss'   => $this->context->getClient()->get('id'),
            'type'  => 'activation'
        ];
        $decoded    = $jwts->decode($token);
        foreach ($requiredClaims as $key => $expectedValue) {
            if (!isset($decoded->$key) || $decoded->$key !== $expectedValue) {
                throw new ValidationException("Invalid token: missing or incorrect claim '$key'");
            }
        }
        $user       = $this->entityManager->find(User::class, $decoded->sub);
        if (!$user) {
            throw new NotFoundException('User not found');
        }
        if ($user->get('status') === 'active') {
            return $user;
        }
        $user->activate();
        $this->entityManager->flush();
        return $user;
    }
}