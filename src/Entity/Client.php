<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'clients')]
class Client {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;
    #[ORM\Column(type: 'string')]
    private string $name;
    
    public function get( string $prop ) {
		return $this->$prop ?? null;
	}
	public function init( array $data ): self {
		$this->name     = $data['name'];
		return $this;
	}
}