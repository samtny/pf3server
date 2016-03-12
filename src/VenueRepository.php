<?php

namespace PF;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class VenueRepository extends EntityRepository {
  public function getVenues($request) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('v', 'm', 'g', 'c'));
    $qb->from('\PF\Venue', 'v')
      ->leftJoin('v.comments', 'c')
      ->leftJoin('v.machines', 'm')
      ->leftJoin('m.game', 'g')
      ->where($qb->expr()->andX(
        $qb->expr()->isNotNull('v.latitude'),
        $qb->expr()->isNotNull('v.longitude')
      ));

    $s = !empty($request->get('s')) ? $request->get('s') : 'APPROVED';

    $qb->andWhere($qb->expr()->eq('v.status', ':status'))
      ->setParameter('status', $s);

    if (!empty($request->get('n'))) {
      $n = $request->get('n');

      preg_match('/^([0-9.-]+),([0-9.-]+)$/', $n, $latlon);

      if ($latlon) {
        $latitude = $latlon[1];
        $longitude = $latlon[2];

        $qb->add('select', '( 3959 * acos( cos( radians(:latitude) ) * cos( radians( v.latitude ) ) * cos( radians( v.longitude ) - radians(:longitude) ) + sin( radians(:latitude) ) * sin( radians( v.latitude ) ) ) ) AS HIDDEN distance', true)
          ->setParameter('latitude', $latitude)
          ->setParameter('longitude', $longitude)
          ->addOrderBy('distance', 'ASC');
      } else {
        $geocode = $this->getEntityManager()->getRepository('\PF\Geocode')->findOneBy(array('string' => $n));

        if ($geocode->getSouthwestLongitude() <= $geocode->getNortheastLongitude()) {
          $qb->andWhere($qb->expr()->andX(
            $qb->expr()->between('v.latitude', ':southwest_latitude', ':northeast_latitude'),
            $qb->expr()->between('v.longitude', ':southwest_longitude', ':northeast_longitude')
          ));
        } else {
          $qb->andWhere($qb->expr()->andX(
            $qb->expr()->orX(
              $qb->expr()->between('v.longitude', ':southwest_longitude', $qb->expr()->literal(180)),
              $qb->expr()->between('v.longitude', $qb->expr()->literal(-180), ':northeast_longitude')
            ),
            $qb->expr()->between('v.latitude', ':southwest_latitude', ':northeast_latitude')
          ));
        }

        $qb->setParameter('southwest_latitude', $geocode->getSouthwestLatitude())
          ->setParameter('northeast_latitude', $geocode->getNortheastLatitude())
          ->setParameter('southwest_longitude', $geocode->getSouthwestLongitude())
          ->setParameter('northeast_longitude', $geocode->getNortheastLongitude());
      }
    } else {
      $qb->orderBy('v.created', 'DESC');
    }

    if (!empty($request->get('q'))) {
      $name = $request->get('q');

      $name_clean = StringUtil::cleanName($name);
      $name_dm = StringUtil::dmName($name);

      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('v.name_clean', ':name_clean'),
        $qb->expr()->like('v.name_dm', ':name_dm')
      ))
        ->setParameter('name_clean', '%' . $name_clean . '%')
        ->setParameter('name_dm', '%' . $name_dm . '%');
    }

    if (!empty($request->get('g'))) {
      $game = $request->get('g');

      $game_clean = StringUtil::cleanName($game);
      $game_dm = StringUtil::dmName($game);

      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('g.name_clean', ':game_clean'),
        $qb->expr()->like('g.name_dm', ':game_dm')
      ))
        ->setParameter('game_clean', '%' . $game_clean . '%')
        ->setParameter('game_dm', '%' . $game_dm . '%');
    }

    $p = !empty($request->get('p')) ? $request->get('p') : 0;
    $l = !empty($request->get('l')) ? $request->get('l') : 70;

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery();

    return new Paginator($query);
  }
}
