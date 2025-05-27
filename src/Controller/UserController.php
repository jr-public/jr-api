<?php
namespace App\Controller;

use App\DTO\UserRegistrationDTO;
use App\Entity\Client;
use App\Entity\User;
use App\Service\RegistrationService;
use App\Service\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController {
    private User $activeUser;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    // private AuthService $authService;

    public function __construct(
        EntityManagerInterface $entityManager,
        User $activeUser,
        ValidatorInterface $validator,
        // AuthService $authService = null,
    ) {
        $this->entityManager = $entityManager;
        $this->activeUser = $activeUser;
        $this->validator = $validator;
        // $this->authService = $authService ?? new AuthService($entityManager);
    }
    private function findUserById(int $id): User {
        $repo = $this->entityManager->getRepository(User::class);
        $user = $repo->get($id, $this->activeUser->get('client'));
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
        $ums = new UserManagementService($this->entityManager, $this->activeUser);
        $updatedUser = $ums->blockUser($targetUser, $reason);
        return new JsonResponse([
            'data' => []
        ], 200);
    }

    public function unblock(int $id, ?string $reason = null): JsonResponse {
        $targetUser = $this->findUserById($id);
        $ums = new UserManagementService($this->entityManager, $this->activeUser);
        $updatedUser = $ums->unblockUser($targetUser, $reason);
        return new JsonResponse([
            'data' => []
        ], 200);
    }

    public function register(string $username, string $email, string $password, string $client_id): JsonResponse {
        $Client = $this->entityManager->find(Client::class, $client_id);
        $dto = new UserRegistrationDTO($username, $email, $password, $Client);
        $service = new RegistrationService($this->entityManager, $this->validator);
        $registration = $service->registration($dto);
        return new JsonResponse([
            'data' => $registration
        ], 200);
    }

    public function resetPassword(int $id, ?string $password = null): JsonResponse {
        $targetUser = $this->findUserById($id);
        $ums = new UserManagementService($this->entityManager, $this->activeUser);
        $updatedUser = $ums->resetPassword($targetUser, $password);
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

    public function activate(string $id): JsonResponse {
        $User = $this->entityManager->find(User::class, $id);
        $ums = new UserManagementService($this->entityManager, $this->activeUser);
        $updatedUser = $ums->activate($User);
        return new JsonResponse([
            'data' => []
        ], 200);
    }

}