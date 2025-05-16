<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $username;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable:false)]
    private Client $client;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created;

     #[ORM\Column(type: 'string')]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;
    
    public function __construct() {
        $this->created = new \DateTimeImmutable();
    }
    
    public function get( string $prop ) {
        return $this->$prop ?? null;
    }
    
    public function setClient( Client $client ): self {
        $this->client = $client;
        return $this;
    }
    public function setUsername( string $username ): self {
        $this->username = $username;
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
}
