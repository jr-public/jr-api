<?php
namespace App\Controller;

use App\Entity\User;
use App\Service\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController {
    private User $activeUser;
    private EntityManagerInterface $entityManager;
    // private ValidatorInterface $validator;
    // private AuthService $authService;
    // private RegistrationService $registrationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        User $activeUser,
        // ValidatorInterface $validator = null,
        // AuthService $authService = null,
        // RegistrationService $registrationService = null
    ) {
        $this->entityManager = $entityManager;
        $this->activeUser = $activeUser;
        // $this->validator = $validator ?? new \Symfony\Component\Validator\Validation::createValidator();
        // $this->authService = $authService ?? new AuthService($entityManager);
        // $this->registrationService = $registrationService ?? new RegistrationService($entityManager, $this->validator);
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
        return new JsonResponse([], 200);
    }

    public function unblock(int $id, ?string $reason = null): JsonResponse {
        $targetUser = $this->findUserById($id);
        $ums = new UserManagementService($this->entityManager, $this->activeUser);
        $updatedUser = $ums->unblockUser($targetUser, $reason);
        return new JsonResponse([], 200);
    }

    public function resetPassword(int $id): JsonResponse {
        $targetUser = $this->findUserById($id);
        $ums = new UserManagementService($this->entityManager, $this->activeUser);
        $updatedUser = $ums->resetPassword($targetUser);
        return new JsonResponse([], 200);
    }

}