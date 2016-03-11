<?php

namespace PF;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VenueRepository extends EntityRepository {
  public function getVenues($n = null, $number = 70, $page = 0) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('v', 'm', 'g'));
    $qb->from('\PF\Venue', 'v')
      ->join('v.machines', 'm')
      ->join('m.game', 'g')
      ->where($qb->expr()->andX(
        $qb->expr()->isNotNull('v.latitude'),
        $qb->expr()->isNotNull('v.longitude')
      ));

    if (!empty($n)) {
      $point = explode(',', $n);

      $qb->add('select', '( 3959 * acos( cos( radians(:latitude) ) * cos( radians( v.latitude ) ) * cos( radians( v.longitude ) - radians(:longitude) ) + sin( radians(:latitude) ) * sin( radians( v.latitude ) ) ) ) AS HIDDEN distance', true)
        ->setParameter('latitude', $point[0])
        ->setParameter('longitude', $point[1])
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
