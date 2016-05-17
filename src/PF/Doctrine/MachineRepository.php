<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MachineRepository extends EntityRepository {
  public function getMachinesForVenueIds($venueIds, $hydration_mode = Doctrine\ORM\Query::HYDRATE_OBJECT) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('m', 'g', 'v'));
    $qb->from('\PF\Machine', 'm')
      ->leftJoin('m.game', 'g')
      ->leftJoin('m.venue', 'v');

    $qb->orderBy('g.name', 'ASC');

    $qb->where($qb->expr()->in('IDENTITY(m.venue)', ':venue_ids'));
    $qb->setParameter('venue_ids', $venueIds);

    $query = $qb->getQuery()
      ->setHydrationMode($hydration_mode);

    return new Paginator($query);
  }
}
