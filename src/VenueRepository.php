<?php

namespace PF;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VenueRepository extends EntityRepository {
  public function getVenues($request, $page = 0) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('v', 'm', 'g'));
    $qb->from('\PF\Venue', 'v')
      ->join('v.machines', 'm')
      ->join('m.game', 'g')
      ->where($qb->expr()->andX(
        $qb->expr()->isNotNull('v.latitude'),
        $qb->expr()->isNotNull('v.longitude')
      ));

    if (!empty($request->get('n'))) {
      $latlon = explode(',', $request->get('n'));

      $qb->add('select', '( 3959 * acos( cos( radians(:latitude) ) * cos( radians( v.latitude ) ) * cos( radians( v.longitude ) - radians(:longitude) ) + sin( radians(:latitude) ) * sin( radians( v.latitude ) ) ) ) AS HIDDEN distance', true)
        ->setParameter('latitude', $latlon[0])
        ->setParameter('longitude', $latlon[1])
        ->addOrderBy('distance', 'ASC');
    } else {
      $qb->orderBy('v.created', 'DESC');
    }

    $l = !empty($request->get('l')) ? $request->get('l') : 70;

    $qb->setFirstResult($page * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery();

    //$query->setHydrationMode(Doctrine\ORM\Query::HYDRATE_ARRAY);

    return new Paginator($query);
  }
}
