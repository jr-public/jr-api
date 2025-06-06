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

/**
 * Service for managing user operations including blocking, unblocking, registration, activation, and password management.
 * Handles role-based permissions and user lifecycle operations.
 */
class UserManagementService
{
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
    /**
     * @param EntityManagerInterface $entityManager Doctrine entity manager
     * @param EmailService $emails Email service instance
     * @param JWTService $jwts JWT service instance
     * @param RequestContextService $context Request context service instance
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly EmailService $emails, private readonly JWTService $jwts, private readonly RequestContextService $context)
    {
    }

    /**
     * Blocks a user account with optional reason.
     *
     * @param User $targetUser The user to block
     * @param string|null $reason Optional reason for blocking
     * @return User The blocked user
     * @throws BusinessException If permission denied or business rules violated
     */
    public function blockUser(User $targetUser, ?string $reason = null): User
    {
        $this->verifyPermissionToManage($targetUser);
        $targetUser->block();
        $this->entityManager->flush();
        return $targetUser;
    }

    /**
     * Unblocks a previously blocked user account.
     *
     * @param User $targetUser The user to unblock
     * @param string|null $reason Optional reason for unblocking
     * @return User The unblocked user
     * @throws BusinessException If permission denied or business rules violated
     */
    public function unblockUser(User $targetUser, ?string $reason = null): User
    {
        $this->verifyPermissionToManage($targetUser);
        $targetUser->unblock();
        $this->entityManager->flush();
        return $targetUser;
    }

    /**
     * Verifies if the acting user has permission to manage the target user.
     * Checks role hierarchy and client ownership.
     *
     * @param User $targetUser The user being managed
     * @param bool $allow_self Whether to allow self-management
     * @return void
     * @throws BusinessException If permission denied
     */
    private function verifyPermissionToManage(User $targetUser, bool $allow_self = false): void
    {
        $actingUser = $this->context->getUser();
        $actingRole = $actingUser->get('role');
        $targetRole = $targetUser->get('role');
        if ($actingUser->get('client')->get('id') !== $targetUser->get('client')->get('id')) {
            throw new BusinessException('BUSINESS_ERROR', 'Cannot manage users from different clients');
        }
        if (!isset(self::ROLE_HIERARCHY[$actingRole])) {
            throw new BusinessException('BUSINESS_ERROR', 'Invalid acting role');
        }
        if ($actingUser->get('id') != $targetUser->get('id') && !in_array($targetRole, self::ROLE_HIERARCHY[$actingRole])) {
            throw new BusinessException('BUSINESS_ERROR', sprintf('User with role %s does not have permission to manage users with role %s', $actingRole, $targetRole));
        }
        // i dont think i need the first part of this conditional
        if ($actingUser->get('id') == $targetUser->get('id') && !$allow_self) {
            throw new BusinessException('BUSINESS_ERROR', 'Cannot manage self');
        }
    }

    /**
     * Registers a new user with email activation workflow.
     *
     * @param string $username The desired username
     * @param string $email The user's email address
     * @param string $password The user's password (will be hashed)
     * @return array User data array
     * @throws \Throwable If registration fails
     */
    public function registration(string $username, string $email, string $password): array
    {
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
    /**
     * Activates a user account using activation token.
     *
     * @param string $token JWT activation token
     * @return User The activated user
     * @throws ValidationException If token is invalid
     * @throws NotFoundException If user not found
     */
    public function activation(string $token): User
    {
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
            throw new NotFoundException('BAD_USER', 'User not found');
        }
        if ($user->get('status') === 'active') {
            return $user;
        }
        $user->activate();
        $this->entityManager->flush();
        return $user;
    }
    /**
     * Initiates password reset process by sending reset email.
     *
     * @param string $email User's email address
     * @return bool True if reset email sent successfully
     * @throws NotFoundException If user not found
     */
    public function passwordForgot(string $email): bool
    {
        $client = $this->context->getClient();
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email, 'client' => $client]);
        if (!$user) {
            throw new NotFoundException('BAD_USER', 'User not found');
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
    /**
     * Resets user password using reset token.
     *
     * @param string $token JWT password reset token
     * @param string $newPassword The new password (will be hashed)
     * @return bool True if password reset successfully
     * @throws ValidationException If token is invalid
     * @throws NotFoundException If user not found
     */
    public function passwordReset(string $token, string $newPassword): bool
    {
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
            throw new NotFoundException('BAD_USER', 'User not found');
        }
        $targetUser->setPassword($newPassword);
        $this->entityManager->flush();
        return true;
    }
}
