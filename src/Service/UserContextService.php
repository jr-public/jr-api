<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Client;

class UserContextService {

    public function __construct(
        private readonly User $user,
        // private readonly string $device
    ) {}

    public function getUser(): User {
        return $this->user;
    }
    public function getClient(): Client {
        return $this->user->get('client');
    }
    // public function getDevice(): string {
    //     return $this->device;
    // }

}
