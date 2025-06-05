<?php
namespace App\Service;

use App\Entity\User;
use App\Service\EmailService;
use App\Service\JWTService;
use App\Service\RequestContextService;
use App\Exception\BusinessException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;

class UserManagementService {
    

    // Role hierarchy defined as constants for clarity
    private const ROLE_USER = 'user';
    private const ROLE_MODERATOR = 'moderator';
    private const ROLE_ADMIN = 'admin';
    
    // Role hierarchy mapping (higher roles can manage lower roles)
    private const ROLE_HIERARCHY = [
        self::ROLE_ADMIN => [self::ROLE_MODERATOR, self::ROLE_USER],
        self::ROLE_MODERATOR => [self::ROLE_USER],
        self::ROLE_USER => []
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly EmailService $emails,
        private readonly JWTService $jwts, 
        private readonly RequestContextService $context
    ) {}

    public function blockUser(User $targetUser, ?string $reason = null): User {
        $this->verifyPermissionToManage($targetUser);
        $targetUser->block();
        $this->entityManager->flush();
        return $targetUser;
    }

    public function unblockUser(User $targetUser, ?string $reason = null): User {
        $this->verifyPermissionToManage($targetUser);
        $targetUser->unblock();
        $this->entityManager->flush();
        return $targetUser;
    }

    private function verifyPermissionToManage(User $targetUser, bool $allow_self = false): void {
        $actingUser = $this->context->getUser();
        $actingRole = $actingUser->get('role');
        $targetRole = $targetUser->get('role');
        
        if ($actingUser->get('client')->get('id') !== $targetUser->get('client')->get('id')) {
            throw new BusinessException('BUSINESS_ERROR','Cannot manage users from different clients');
        }
        if (!isset(self::ROLE_HIERARCHY[$actingRole])) {
            throw new BusinessException('BUSINESS_ERROR','Invalid acting role');
        }
        if ($actingUser->get('id') != $targetUser->get('id') && !in_array($targetRole, self::ROLE_HIERARCHY[$actingRole])) {
            throw new BusinessException('BUSINESS_ERROR',sprintf(
                'User with role %s does not have permission to manage users with role %s',
                $actingRole,
                $targetRole
            ));
        }
        // i dont think i need the first part of this conditional
        if ( $actingUser->get('id') == $targetUser->get('id') && !$allow_self ) {
            throw new BusinessException('BUSINESS_ERROR','Cannot manage self');
        }
    }










    
    // GUEST
    
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
    public function passwordForgot(string $email): bool {
        $client = $this->context->getClient();
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email, 'client' => $client]);
        if (!$user) {
            throw new NotFoundException('BAD_USER','User not found');
        }
        
        $token = $this->jwts->create([
            'iss'   => $client->get('id'),
            'sub'   => $user->get('id'),
            'type'  => 'password_reset'
        ], 3600);

        $this->emails->sendPasswordResetEmail(
            $user->get('email'),
            $user->get('username'),
            $token
        );
        return true;

    }
    public function passwordReset(string $token, string $newPassword ): bool {
        $requiredClaims = [
            'iss'   => $this->context->getClient()->get('id'),
            'type'  => 'password_reset'
        ];
        $decoded    = $this->jwts->decode($token);
        foreach ($requiredClaims as $key => $expectedValue) {
            if (!isset($decoded->$key) || $decoded->$key !== $expectedValue) {
                throw new ValidationException('BAD_TOKEN', "Invalid password reset token: missing or incorrect claim '$key'");
            }
        }
        $targetUser       = $this->entityManager->find(User::class, (int)$decoded->sub);
        if (!$targetUser) {
            throw new NotFoundException('BAD_USER','User not found');
        }
        $targetUser->setPassword($newPassword);
        $this->entityManager->flush();
        return true;
    }
}