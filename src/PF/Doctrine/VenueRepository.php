<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

use PF\Utilities\StringUtil;

class VenueRepository extends EntityRepository {
  public function find($id) {
    $venue = parent::find($id);

    if (!empty($venue)) {
      if ($venue->getStatus() === 'DELETED') {
        $venue = null;
      }
    };

    return $venue;
  }

  public function getFreshnessStats() {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select('COUNT(v.id) as total, CASE WHEN DATEDIFF(CURRENT_DATE(), v.updated) <= 365 THEN \'Fresh\' ELSE \'Not Fresh\' END as freshness')
      ->from('\PF\Venue', 'v')
      ->where('v.status = \'APPROVED\'')
      ->groupBy('freshness');

    return $qb->getQuery()->getArrayResult();
  }

  public function getCreatedStats() {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select('YEAR(v.created) as HIDDEN created_year, MONTH(v.created) as HIDDEN created_month, DATE_FORMAT(v.created, \'%b\') as month, COUNT(v) as total')
      ->from('\PF\Venue', 'v')
      ->where('DATEDIFF(LAST_DAY(CURRENT_DATE()), v.created) <= 365')
      ->groupBy('created_year, created_month')
      ->orderBy('created_year, created_month');

    return $qb->getQuery()->getArrayResult();
  }

  public function getUpdatedStats() {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select('YEAR(v.updated) as HIDDEN updated_year, MONTH(v.updated) as HIDDEN updated_month, DATE_FORMAT(v.updated, \'%b\') as month, COUNT(v) as total')
      ->from('\PF\Venue', 'v')
      ->where('DATEDIFF(LAST_DAY(CURRENT_DATE()), v.updated) <= 365')
      ->groupBy('updated_year, updated_month')
      ->orderBy('updated_year, updated_month');

    return $qb->getQuery()->getArrayResult();
  }

  public function getVenues($request, $hydration_mode = Doctrine\ORM\Query::HYDRATE_OBJECT) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('v', 'm', 'g', 'c'));
    $qb->from('\PF\Venue', 'v')
      ->leftJoin('v.comments', 'c')
      ->leftJoin('v.machines', 'm')
      ->leftJoin('m.game', 'g');

    $s = !empty($request->get('s')) ? $request->get('s') : 'APPROVED';

    $qb->andWhere($qb->expr()->eq('v.status', ':status'))
      ->setParameter('status', $s);

    if ($s === 'APPROVED') {
      $qb->andWhere($qb->expr()->andX(
        $qb->expr()->isNotNull('v.latitude'),
        $qb->expr()->isNotNull('v.longitude')
      ));
    }

    if (!empty($request->get('n'))) {
      $n = $request->get('n');

      preg_match('/^([0-9.-]+),([0-9.-]+)$/', $n, $latlon);

      if ($latlon) {
        $latitude = $latlon[1];
        $longitude = $latlon[2];

        $qb->add('select', '( 3959 * ACOS( COS( RADIANS(:latitude) ) * COS( RADIANS( v.latitude ) ) * COS( RADIANS( v.longitude ) - RADIANS(:longitude) ) + SIN( RADIANS(:latitude) ) * SIN( RADIANS( v.latitude ) ) ) ) AS HIDDEN distance', true)
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
      $qb->orderBy('v.updated', 'DESC');
    }

    if (!empty($request->get('q'))) {
      if (is_numeric($request->get('q'))) {
        $id = $request->get('q');

        $qb->andWhere($qb->expr()->eq('v.id', $id));
      } else {
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
    }

    if (!empty($request->get('g'))) {
      $game = $request->get('g');

      $game_clean = StringUtil::cleanName($game);
      $game_dm = StringUtil::dmName($game);

      $qb2 = $this->getEntityManager()->createQueryBuilder();

      $qb2->select(array('identity(m2.venue)'))
        ->from('PF\Machine', 'm2')
        ->innerJoin('m2.game', 'g2')
        ->where($qb->expr()->andX(
          $qb->expr()->like('g2.name_clean', ':game_clean'),
          $qb->expr()->like('g2.name_dm', ':game_dm')
        ));

      $qb->andWhere($qb->expr()->in('v.id', $qb2->getDQL()));

      $qb->setParameter('game_clean', '%' . $game_clean . '%')
        ->setParameter('game_dm', '%' . $game_dm . '%');
    }

    if (!empty($request->get('x'))) {
      $x_parts = explode(',', $request->get('x'));

      foreach ($x_parts as $x_part) {
        switch ($x_part) {
          case 'new':
            $qb->andWhere($qb->expr()->eq('g.new', $qb->expr()->literal(true)));

            break;
          case 'rare':
            $qb->andWhere($qb->expr()->eq('g.rare', $qb->expr()->literal(true)));

            break;
        }
      }
    }

    $p = !empty($request->get('p')) ? $request->get('p') : 0;
    $l = !empty($request->get('l')) ? $request->get('l') : 70;

    $qb->addOrderBy('g.name', 'ASC');
    $qb->addOrderBy('c.created', 'DESC');

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery()
      ->setHydrationMode($hydration_mode);

    return new Paginator($query);
  }
}
