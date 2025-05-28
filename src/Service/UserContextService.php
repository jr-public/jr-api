<?php
namespace App\Service;

use App\Entity\User;

class UserContextService {

    public function __construct(
        private readonly User $user,
        private readonly string $device
    ) {}

    public function getUser(): User {
        return $this->user;
    }

    public function getDevice(): string {
        return $this->device;
    }

}
