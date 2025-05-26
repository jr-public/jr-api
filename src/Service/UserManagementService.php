<?php
namespace App\Service;

use App\Entity\User;
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
    private EntityManagerInterface $entityManager;
    private User $actingUser;
    
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

    public function __construct(EntityManagerInterface $entityManager, User $actingUser) {
        $this->entityManager = $entityManager;
        $this->actingUser = $actingUser;
    }

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
    public function activate(User $targetUser): User {
        $this->verifyPermissionToManage($targetUser, true);
        $targetUser->activate();
        $this->entityManager->flush();
        return $targetUser;
    }
    public function resetPassword(User $targetUser): User {
        $this->verifyPermissionToManage($targetUser, true);
        $targetUser->resetPassword();
        $this->entityManager->flush();
        // Generate reset token
        // $jwtService = new JWTService();
        // $resetToken = $jwtService->createToken([
        //     'sub' => $targetUser->get('id'),
        //     'type' => 'password_reset'
        // ], 3600); // 1 hour expiry
        return $targetUser;
    }
    private function verifyPermissionToManage(User $targetUser, bool $allow_self = false): void {
        $actingRole = $this->actingUser->get('role');
        $targetRole = $targetUser->get('role');
        
        if ($this->actingUser->get('client')->get('id') !== $targetUser->get('client')->get('id')) {
            throw new \Exception('Cannot manage users from different clients');
        }
        if (!isset(self::ROLE_HIERARCHY[$actingRole])) {
            throw new \Exception('Invalid acting role');
        }
        if ($this->actingUser->get('id') != $targetUser->get('id') && !in_array($targetRole, self::ROLE_HIERARCHY[$actingRole])) {
            throw new \Exception(sprintf(
                'User with role %s does not have permission to manage users with role %s',
                $actingRole,
                $targetRole
            ));
        }
        // i dont think i need the first part of this conditional
        if ( $this->actingUser->get('id') == $targetUser->get('id') && !$allow_self ) {
            throw new \Exception('Cannot manage self');
        }
    }
}