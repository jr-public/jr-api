<?php
namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Client;
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

    #[Assert\NotBlank]
    public Client $client;

    public function __construct(string $username, string $email, string $password, Client $client) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->client = $client;
    }
}