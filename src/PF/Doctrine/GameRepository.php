<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

use PF\Utilities\StringUtil;

class GameRepository extends EntityRepository {
  public function getGames($request, $hydration_mode = Doctrine\ORM\Query::HYDRATE_OBJECT) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('g'));
    $qb->from('\PF\Game', 'g');
    $qb->orderBy('g.name', 'ASC');

    if (!empty($request->get('q'))) {
      $name = $request->get('q');

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

    if (!empty($request->get('i'))) {
      $ipdb = $request->get('i');

      $qb->andWhere($qb->expr()->eq('g.ipdb', $qb->expr()->literal($ipdb)));
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

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery()
      ->setHydrationMode($hydration_mode);

    return new Paginator($query);
  }
}
