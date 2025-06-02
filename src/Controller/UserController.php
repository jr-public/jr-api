<?php
namespace App\Controller;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Service\AuthService;
use App\Service\RegistrationService;
use App\Service\RequestContextService;
use App\Service\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;

class UserController {
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestContextService $context,
        private readonly UserManagementService $ums,
        private readonly RegistrationService $regs,
        private readonly AuthService $auths
    ) {}
    private function findUserById(int $id): User {
        $repo = $this->entityManager->getRepository(User::class);
        $user = $repo->get($id, $this->context->getClient()->get('id'));
        if ( empty($user) ) {
            throw new NotFoundException('USER_NOT_FOUND');
        }
        return $user;
    }

    public function get(int $id): array {
        $targetUser = $this->findUserById($id);
        return $targetUser->toArray();
    }

    public function block(int $id, ?string $reason = null): bool {
        $targetUser = $this->findUserById($id);
        $updatedUser = $this->ums->blockUser($targetUser, $reason);
        return true;
    }

    public function unblock(int $id, ?string $reason = null): bool {
        $targetUser = $this->findUserById($id);
        $updatedUser = $this->ums->unblockUser($targetUser, $reason);
        return true;
    }

    public function register(string $username, string $email, string $password): array {
        $dto = new UserRegistrationDTO($username, $email, $password);
        $registration = $this->regs->registration($dto);
        return $registration;
    }

    public function resetPassword(int $id, ?string $password = null): bool {
        $targetUser = $this->findUserById($id);
        $updatedUser = $this->ums->resetPassword($targetUser, $password);
        return true;
    }

    public function forgotPassword(string $email): bool {
        // SEND EMAIL WITH TOKEN
        return true;
    }

    public function activate(string $token): array {
        $user = $this->regs->activation($token);
        return $user->toArray();
    }
    public function login(string $username, string $password): array { 
        $login = $this->auths->login($username, $password);
        return [
            'user' => $login['user'],
            'token' => $login['token']
        ];
    }

}