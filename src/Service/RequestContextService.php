<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Client;
use App\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
class RequestContextService {
	
	private ?User $user 		= null;
	private ?Client $client 	= null;
	private ?string $device 	= null;

	public function __construct(
		private readonly EntityManagerInterface $entityManager, 
		private readonly Request $request
	) {}
	
    public function hasUser(): bool {
		return !empty($this->user);
	}

	public function hasClient(): bool {
		return !empty($this->client);
	}

	public function hasDevice(): bool {
		return !empty($this->device);
	}

    public function setUser(User $user): self {
		$this->user = $user;
		return $this;
	}

	public function setClient(Client $client): self {
		$this->client = $client;
		return $this;
	}
	public function setDevice(string $device): self {
		$this->device = $device;
		return $this;
	}

	public function getUser(): User {
		if (!$this->hasUser()) {
			throw new NotFoundException('BAD_USER', "Request context user not found");
		}
		return $this->user;
	}

	public function getClient(): Client {
		if (!$this->hasClient()) {
			$client = $this->entityManager->getRepository(Client::class)->findOneBy([
				'domain' => $this->request->getHost()
			]);
			if (empty($client)) {
				throw new NotFoundException('BAD_CLIENT',"Request context client not found");
			}
			$this->setClient($client);
		}
		return $this->client;
	}

	public function getDevice(): string {
		if (!$this->hasDevice()) {
			$this->setDevice($this->request->headers->get('User-Agent', 'unknown'));
		}
		return $this->device;
	}

	public function getRequest(): Request {
		return $this->request;
	}
}