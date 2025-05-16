<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserAuthDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $password;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $device;

    public function __construct(string $username, string $password, string $device) {
        $this->username = $username;
        $this->password = $password;
        $this->device = $device;
    }
}