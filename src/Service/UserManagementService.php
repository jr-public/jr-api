<?php
namespace App\Service;

use App\Entity\User;
use App\Exception\BusinessException;
use App\Service\RequestContextService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * User Management Service (UMS)
 * 
 * This service provides a centralized entry point for managing users within the application.
 * It enforces permission checks based on role hierarchy and ensures proper validation.
 * 
 * IMPORTANT: This service should be used for all user management operations instead of
 * directly modifying User entities.
 */
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
        private readonly RequestContextService $requestContext
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
    public function resetPassword(User $targetUser, ?string $newPassword = null ): User {
        $this->verifyPermissionToManage($targetUser, true);
        if (empty($newPassword)) {
            $targetUser->resetPassword();
            $this->entityManager->flush();
            // Generate reset token
            // $jwtService = new JWTService();
            // $resetToken = $jwtService->createToken([
            //     'sub' => $targetUser->get('id'),
            //     'type' => 'password_reset'
            // ], 3600); // 1 hour expiry
        }
        else {
            $targetUser->setPassword($newPassword);
            $this->entityManager->flush();
        }
        return $targetUser;
    }
    private function verifyPermissionToManage(User $targetUser, bool $allow_self = false): void {
        $actingUser = $this->requestContext->getUser();
        $actingRole = $actingUser->get('role');
        $targetRole = $targetUser->get('role');
        
        if ($actingUser->get('client')->get('id') !== $targetUser->get('client')->get('id')) {
            throw new BusinessException('Cannot manage users from different clients');
        }
        if (!isset(self::ROLE_HIERARCHY[$actingRole])) {
            throw new BusinessException('Invalid acting role');
        }
        if ($actingUser->get('id') != $targetUser->get('id') && !in_array($targetRole, self::ROLE_HIERARCHY[$actingRole])) {
            throw new BusinessException(sprintf(
                'User with role %s does not have permission to manage users with role %s',
                $actingRole,
                $targetRole
            ));
        }
        // i dont think i need the first part of this conditional
        if ( $actingUser->get('id') == $targetUser->get('id') && !$allow_self ) {
            throw new BusinessException('Cannot manage self');
        }
    }
}