<?php
namespace App\Controller;

use App\Entity\User;
use App\Exception\NotFoundException;
use App\Service\AuthService;
use App\Service\RequestContextService;
use App\Service\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;

class UserController {
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestContextService $context,
        private readonly UserManagementService $ums,
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

    public function login(string $username, string $password): array { 
        $login = $this->auths->login($username, $password);
        return [
            'user' => $login['user'],
            'token' => $login['token']
        ];
    }
    public function register(string $username, string $email, string $password): array {
        $registration = $this->ums->registration($username, $email, $password);
        return $registration;
    }
    public function activate(string $token): array {
        $user = $this->ums->activation($token);
        return $user->toArray();
    }
    public function passwordForgot(string $email): bool {
        $sent = $this->ums->passwordForgot($email);
        return $sent;
    }
    public function passwordReset(string $token, string $new_pass): bool {
        $reset = $this->ums->passwordReset($token, $new_pass);
        return $reset;
    }

}