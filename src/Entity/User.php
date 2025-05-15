<?php
namespace App\Entity;
use App\DTO\UserRegistrationDTO;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable:false)]
    private Client $client;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created;

     #[ORM\Column(type: 'string')]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    public function get( string $prop ) {
        return $this->$prop ?? null;
    }
    
    public function setClient( Client $client ): self {
        $this->client = $client;
        return $this;
    }
    public function setName( string $name ): self {
        $this->name = $name;
        return $this;
    }
    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }
    public function setPassword(string $password): self {
        $this->password = $password;
        return $this;
    }

    public static function registration(UserRegistrationDTO $dto): self {
        $user = new self();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setClient($dto->client);
        $user->created = new \DateTimeImmutable("now");
		 // Hash the password!!
        $user->setPassword($dto->password);
        return $user;
    }
}
