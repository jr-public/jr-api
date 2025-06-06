<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Client;
use App\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Service for managing request context including user, client, and device information
 */
class RequestContextService
{
    /** @var User|null Current authenticated user */
    private ?User $user = null;
    /** @var Client|null Current client based on request domain */
    private ?Client $client = null;
    /** @var string|null Current device identifier from User-Agent */
    private ?string $device = null;


    /**
     * @param EntityManagerInterface $entityManager Doctrine entity manager
     * @param Request $request Current HTTP request
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Request $request
    ) {
    }
    /**
     * Check if user is set in context
     * @return bool
     */
    public function hasUser(): bool
    {
        return !empty($this->user);
    }

    /**
     * Check if client is set in context
     * @return bool
     */
    public function hasClient(): bool
    {
        return !empty($this->client);
    }

    /**
     * Check if device is set in context
     * @return bool
     */
    public function hasDevice(): bool
    {
        return !empty($this->device);
    }

    /**
     * Set the current user
     * @param User $user
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set the current client
     * @param Client $client
     * @return self
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
    /**
     * Set the current device identifier
     * @param string $device
     * @return self
     */
    public function setDevice(string $device): self
    {
        $this->device = $device;
        return $this;
    }

    /**
     * Get the current user
     * @return User
     * @throws NotFoundException When user is not set in context
     */
    public function getUser(): User
    {
        if (!$this->hasUser()) {
            throw new NotFoundException('BAD_USER', "Request context user not found");
        }
        return $this->user;
    }

    /**
     * Get the current client, auto-loading from request host if not set
     * @return Client
     * @throws NotFoundException When client cannot be found or determined
     */
    public function getClient(): Client
    {
        if (!$this->hasClient()) {
            $client = $this->entityManager->getRepository(Client::class)->findOneBy([
                'domain' => $this->request->getHost()
            ]);
            if (empty($client)) {
                throw new NotFoundException('BAD_CLIENT', "Request context client not found");
            }
            $this->setClient($client);
        }
        return $this->client;
    }

    /**
     * Get the current device identifier, auto-loading from User-Agent if not set
     * @return string
     */
    public function getDevice(): string
    {
        if (!$this->hasDevice()) {
            $this->setDevice($this->request->headers->get('User-Agent', 'unknown'));
        }
        return $this->device;
    }

    /**
     * Get the current HTTP request
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
