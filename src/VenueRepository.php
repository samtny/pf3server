<?php

namespace PF;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VenueRepository extends EntityRepository {
  public function getVenues($n = null, $number = 75, $page = 0) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('v', 'm', 'g'));
    $qb->from('\PF\Venue', 'v')
      ->join('v.machines', 'm')
      ->join('m.game', 'g');

    if (!empty($n)) {
      $point = explode(',', $n);

      $qb->add('select', 'ST_Distance(Point(:lng, :lat), v.coordinate) AS HIDDEN distance', true)
        ->setParameter('lng', $point[1])
        ->setParameter('lat', $point[0])
        ->addOrderBy('distance', 'ASC');
    } else {
      $qb->orderBy('v.created', 'DESC');
    }

    $qb->setFirstResult($page * $number)
      ->setMaxResults($number);

    $query = $qb->getQuery();

    //$query->setHydrationMode(Doctrine\ORM\Query::HYDRATE_ARRAY);

    return new Paginator($query);
  }
}
