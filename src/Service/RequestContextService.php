<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
class RequestContextService {
	
	private User $user;
	private Client $client;
	private string $device;

	public function __construct(
		private readonly EntityManagerInterface $entityManager, 
		private readonly Request $request
	) {}
	
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
		if (empty($this->user)) {
			throw new \Exception("Request context user not found");
		}
		return $this->user;
	}

	public function getClient(): Client {
		if (empty($this->client)) {
			$client = $this->entityManager->getRepository(Client::class)->findOneBy([
				'domain' => $this->request->getHost()
			]);
			if (empty($client)) {
				throw new \Exception("Request context client not found");
			}
			$this->setClient($client);
		}
		return $this->client;
	}

	public function getDevice(): string {
		if (empty($this->device)) {
			$this->setDevice($this->request->headers->get('User-Agent', 'unknown'));
		}
		return $this->device;
	}

	public function getRequest(): Request {
		return $this->request;
	}
}