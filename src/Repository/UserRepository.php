<?php
namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {
    public function get(int $id, Client $client): ?User {
        return $this->findOneBy([
            'id' => $id,
            'client' => $client
        ]);
    }
    
    // public function findActiveUsersByClient(Client $client): array
    // {
    //     return $this->createQueryBuilder('u')
    //         ->where('u.client = :client')
    //         ->andWhere('u.status = :status')
    //         ->setParameter('client', $client)
    //         ->setParameter('status', 'active')
    //         ->getQuery()
    //         ->getResult();
    // }
}