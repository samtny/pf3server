<?php

namespace PF;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VenueRepository extends EntityRepository {
  public function getRecentVenues($number = 75, $page = 0) {
    $dql = "SELECT v, m FROM \PF\Venue v JOIN v.machines m ORDER BY v.created DESC";

    $query = $this->getEntityManager()->createQuery($dql);
    $query->setFirstResult($page * $number);
    $query->setMaxResults($number);

    $query->setHydrationMode(Doctrine\ORM\Query::HYDRATE_ARRAY);

    return new Paginator($query);
  }
}
