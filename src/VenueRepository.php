<?php

namespace PF;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VenueRepository extends EntityRepository {
  public function getRecentVenues($number = 75, $page = 0) {
    $venues = array();

    $dql = "SELECT v FROM \PF\Venue v ORDER BY v.created DESC";

    $query = $this->getEntityManager()->createQuery($dql);
    $query->setFirstResult($page * $number);
    $query->setMaxResults($number);

    $query->setHydrationMode(Doctrine\ORM\Query::HYDRATE_ARRAY);

    $paginator = new Paginator($query);

    foreach ($paginator as $venue) {
      $venues[] = $venue;
    }

    return $venues;
  }
}
