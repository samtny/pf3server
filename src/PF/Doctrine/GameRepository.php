<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

use PF\Utilities\StringUtil;

class GameRepository extends EntityRepository {
  public function getGames($params, $hydration_mode = Doctrine\ORM\Query::HYDRATE_OBJECT) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('g'));
    $qb->from('\PF\Game', 'g');
    $qb->orderBy('g.name', 'ASC');

    if (!empty($params['q'])) {
      $name = $params['q'];

      $name_clean = StringUtil::cleanName($name);
      $name_dm = StringUtil::dmName($name);

      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('g.name', ':name'),
        $qb->expr()->like('g.name_clean', ':name_clean'),
        $qb->expr()->like('g.name_dm', ':name_dm')
      ))
        ->setParameter('name', '%' . $name . '%')
        ->setParameter('name_clean', '%' . $name_clean . '%')
        ->setParameter('name_dm', '%' . $name_dm . '%');
    }

    if (!empty($params['i'])) {
      $ipdb = $params['i'];

      $qb->andWhere($qb->expr()->eq('g.ipdb', $qb->expr()->literal($ipdb)));
    }

    if (!empty($params['x'])) {
      $x_parts = explode(',', $params['x']);

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

    $p = !empty($params['p']) ? $params['p'] : 0;
    $l = !empty($params['l']) ? $params['l'] : 70;

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery()
      ->setHydrationMode($hydration_mode);

    return new Paginator($query);
  }
}
