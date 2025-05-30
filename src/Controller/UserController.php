<?php
namespace App\Controller;

use App\DTO\UserRegistrationDTO;
use App\Entity\User;
use App\Service\AuthService;
use App\Service\RegistrationService;
use App\Service\RequestContextService;
use App\Service\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
            throw new \RuntimeException('User not found', 404);
        }
        return $user;
    }

    public function get(int $id): JsonResponse {
        $targetUser = $this->findUserById($id);
        return new JsonResponse([
            'data' => $targetUser->toArray()
        ], 200);
    }

    public function block(int $id, ?string $reason = null): JsonResponse {
        $targetUser = $this->findUserById($id);
        $updatedUser = $this->ums->blockUser($targetUser, $reason);
        return new JsonResponse([
            'data' => []
        ], 200);
    }

    public function unblock(int $id, ?string $reason = null): JsonResponse {
        $targetUser = $this->findUserById($id);
        $updatedUser = $this->ums->unblockUser($targetUser, $reason);
        return new JsonResponse([
            'data' => []
        ], 200);
    }

    public function register(string $username, string $email, string $password): JsonResponse {
        $dto = new UserRegistrationDTO($username, $email, $password);
        $registration = $this->regs->registration($dto);
        return new JsonResponse([
            'data' => $registration
        ], 200);
    }

    public function resetPassword(int $id, ?string $password = null): JsonResponse {
        $targetUser = $this->findUserById($id);
        $updatedUser = $this->ums->resetPassword($targetUser, $password);
        return new JsonResponse([
            'data' => []
        ], 200);
    }

    public function forgotPassword(string $email): JsonResponse {
        // SEND EMAIL WITH TOKEN
        return new JsonResponse([
            'data' => []
        ], 200);
    }

    public function activate(string $token): JsonResponse {
        $user = $this->regs->activation($token);
        return new JsonResponse([
            'data' => [
                'message' => 'Account activated successfully',
                'user' => $user->toArray()
            ]
        ], 200);
    }
    public function login(string $username, string $password): JsonResponse { 
        $client = $this->context->getClient();
	    $device = $this->context->getDevice();
        $login = $this->auths->login($username, $password, $client->get('id'), $device);
        return new JsonResponse([
            'data' => [
                'user' => $login['user'],
                'token' => $login['token']
            ]
        ], 200);
    }

}