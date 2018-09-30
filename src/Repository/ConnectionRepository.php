<?php

namespace Dpeuscher\BahnSearch\Repository;

use Doctrine\ORM\EntityRepository;
use Dpeuscher\BahnSearch\Entity\Connection;

/**
 * @method Connection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Connection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Connection[]    findAll()
 * @method Connection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConnectionRepository extends EntityRepository
{

    /**
     * @param int $id
     * @return Connection[] Returns an array of Connection objects
     */
    public function findById(int $id): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.id = :id')
            ->setParameter('id', $id)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
