<?php
namespace App\Controller;

use App\Entity\User;
// use App\Entity\Client;
// use App\DTO\UserRegistrationDTO;
// use App\Service\AuthService;
// use App\Service\RegistrationService;
use App\Service\UserManagementService;
// use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController {
    private User $activeUser;
    private EntityManagerInterface $entityManager;
    // private ValidatorInterface $validator;
    // private AuthService $authService;
    // private RegistrationService $registrationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        User $activeUser
        // ValidatorInterface $validator,
        // AuthService $authService,
        // RegistrationService $registrationService
    ) {
        $this->entityManager = $entityManager;
        $this->activeUser = $activeUser;
        // echo "HOLA";
        // die();
        // $this->validator = $validator;
        // $this->authService = $authService;
        // $this->registrationService = $registrationService;
    }
    // public function list(string $role = null, bool $active = null, string $sort = null, int $limit = null, int $page = 1): array {
    //     return $args;
    // }
    public function get(int $id): array {
        return [
            "id" => $id,
            "username" => "john_doe"
        ];
    }
    /*

    public function block(int $userId, string $token, ?string $reason = null): array {
        // Implementation will use UserManagementService to block user
    }

    public function unblock(int $userId, string $token, ?string $reason = null): array {
        // Implementation will use UserManagementService to unblock user
    }

    public function activate(string $activationToken): array {
        // Implementation will use RegistrationService to activate user
    }

    public function register(string $username, string $email, string $password, int $clientId): array {
        // Implementation will use RegistrationService to create new user
    }

    public function edit(
        int $userId, 
        string $token, 
        ?string $username = null, 
        ?string $email = null,
        ?string $role = null
    ): array {
        // Implementation will update user data with validation
    }

    public function delete(int $userId, string $token): array {
        // Implementation will remove user (requires proper permissions)
    }

    public function create(
        string $username, 
        string $email, 
        string $password, 
        int $clientId, 
        string $token,
        string $role = 'user'
    ): array {
        // Implementation will create user directly (bypass registration flow)
    }

    public function password_forgot(string $email, int $clientId): array {
        // Implementation will generate reset token and send email
    }

    public function password_reset(string $resetToken, string $newPassword): array {
        // Implementation will validate token and update password
    }
    */
}