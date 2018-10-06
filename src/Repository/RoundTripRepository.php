<?php

namespace Dpeuscher\BahnSearch\Repository;

use Doctrine\ORM\EntityRepository;
use Dpeuscher\BahnSearch\Entity\RoundTrip;

/**
 * @method RoundTrip|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoundTrip|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoundTrip[]    findAll()
 * @method RoundTrip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoundTripRepository extends EntityRepository
{
}
