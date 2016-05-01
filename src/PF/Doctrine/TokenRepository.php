<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TokenRepository extends EntityRepository {
  public function getValidTokens($app = null, $hydration_mode = Doctrine\ORM\Query::HYDRATE_OBJECT) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('t'));
    $qb->from('\PF\Token', 't');
    $qb->where($qb->expr()->eq('t.status', ':status'))
      ->setParameter('status', 'VALID');

    if (!empty($app)) {
      $qb->andWhere($qb->expr()->eq('t.app', ':app'))
        ->setParameter('app', $app);
    }

    $query = $qb->getQuery()
      ->setHydrationMode($hydration_mode);

    return new Paginator($query);
  }
}
