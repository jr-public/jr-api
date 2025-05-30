<?php
namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {
    public function get(int $id, int $client_id): ?User {
        // return $this->findOneBy([
        //     'id' => $id,
        //     'client' => $client
        // ]);
        $dql = 'SELECT u FROM App\Entity\User u WHERE u.id = :id AND u.client = :client_id';
        $query = $this->getEntityManager()->createQuery($dql)
            ->setParameter('id', $id)
            ->setParameter('client_id', $client_id);
        $user = $query->getOneOrNullResult();
        return $user;
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