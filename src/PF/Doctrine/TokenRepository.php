<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TokenRepository extends EntityRepository {
  public function getValidTokens($ordered = false) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('t'));
    $qb->from('\PF\Token', 't');
    $qb->where($qb->expr()->eq('t.status', ':status'))
      ->setParameter('status', 'VALID');

    if ($ordered) {
      $qb->orderBy('t.id');
    }

    $query = $qb->getQuery();

    return new Paginator($query);
  }
}
