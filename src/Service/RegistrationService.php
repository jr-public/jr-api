<?php
namespace App\Service;

use App\Entity\User;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Service\EmailService;
use App\Service\RequestContextService;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService {
    public function __construct(
        private readonly EmailService $emails,
        private readonly EntityManagerInterface $entityManager, 
        private readonly JWTService $jwts, 
        private readonly RequestContextService $context,
        private readonly ValidatorInterface $validator
    ) {}

    public function registration(string $username, string $email, string $password): array {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password);
            $user->setClient($this->context->getClient());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            $token = $this->jwts->create([
                'iss'   => $this->context->getClient()->get('id'),
                'sub'   => $user->get('id'),
                'type'  => 'activation'
            ]);

            $this->emails->sendActivationEmail(
                $user->get('email'),
                $user->get('username'),
                $token
            );
            $this->entityManager->getConnection()->commit(); 
            return ['user' => $user->toArray()];
        } catch (\Throwable $th) {
            $this->entityManager->getConnection()->rollBack();
            throw $th;
        }
    }
    public function activation(string $token): User {
        $requiredClaims = [
            'iss'   => $this->context->getClient()->get('id'),
            'type'  => 'activation'
        ];
        $decoded    = $this->jwts->decode($token);
        foreach ($requiredClaims as $key => $expectedValue) {
            if (!isset($decoded->$key) || $decoded->$key !== $expectedValue) {
                throw new ValidationException('BAD_TOKEN', "Invalid activation token: missing or incorrect claim '$key'");
            }
        }
        $user       = $this->entityManager->find(User::class, (int)$decoded->sub);
        if (!$user) {
            throw new NotFoundException('BAD_USER','User not found');
        }
        if ($user->get('status') === 'active') {
            return $user;
        }
        $user->activate();
        $this->entityManager->flush();
        return $user;
    }
}