<?php
namespace App\Controller;

use App\DTO\UserRegistrationDTO;
use App\Entity\Client;
use App\Entity\User;
use App\Service\AuthService;
use App\Service\RegistrationService;
use App\Service\UserContextService;
use App\Service\UserManagementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController {
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        // private readonly UserContextService $userContext,
        private readonly UserManagementService $ums,
        private readonly RegistrationService $regs,
        private readonly AuthService $auths,
        private readonly Request $request
    ) {}

    private function findUserById(int $id): User {
        $repo = $this->entityManager->getRepository(User::class);
        $user = $repo->get($id, $this->userContext->getUser()->get('client'));
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

    public function register(string $username, string $email, string $password, string $client_id): JsonResponse {
        $Client = $this->entityManager->find(Client::class, $client_id);
        $dto = new UserRegistrationDTO($username, $email, $password, $Client);
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

    public function activate(string $id): JsonResponse {
        $User = $this->entityManager->find(User::class, $id);
        $updatedUser = $this->ums->activate($User);
        return new JsonResponse([
            'data' => []
        ], 200);
    }

    public function login(string $username, string $password): JsonResponse { 
        $client = $this->entityManager->getRepository(Client::class)->findOneBy([
            'domain' => $this->request->getHost()
        ]);
	    $device = $this->request->headers->get('User-Agent', 'unknown');
        $login = $this->auths->login($username, $password, $client->get('id'), $device);
        return new JsonResponse([
            'data' => [
                'user' => $login['user'],
                'token' => $login['token']
            ]
        ], 200);
    }

}