<?php
namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;
class UserRegistrationDTO {
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 255)]
    public string $password;


    public function __construct(string $username, string $email, string $password) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }
}