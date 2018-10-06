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
}
