<?php

namespace PF;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VenueRepository extends EntityRepository {
  public function getVenues($request, $page = 0) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('v', 'm', 'g', 'c'));
    $qb->from('\PF\Venue', 'v')
      ->leftJoin('v.comments', 'c')
      ->leftJoin('v.machines', 'm')
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

    if (!empty($request->get('q'))) {
      $name = $request->get('q');

      $name_clean = StringUtil::cleanName($name);
      $name_dm = StringUtil::dmName($name);

      $qb->add('where', $qb->expr()->orX(
        $qb->expr()->like('v.name_clean', ':name_clean'),
        $qb->expr()->like('v.name_dm', ':name_dm')
      ))
        ->setParameter('name_clean', '%' . $name_clean . '%')
        ->setParameter('name_dm', '%' . $name_dm . '%');
    }

    $l = !empty($request->get('l')) ? $request->get('l') : 70;

    $qb->setFirstResult($page * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery();

    //$query->setHydrationMode(Doctrine\ORM\Query::HYDRATE_ARRAY);

    return new Paginator($query);
  }
}
