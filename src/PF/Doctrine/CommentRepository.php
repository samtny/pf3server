<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CommentRepository extends EntityRepository {
  public function find($id, $lockMode = null, $lockVersion = null) {
    $comment = parent::find($id, $lockMode, $lockVersion);

    if (!empty($comment)) {
      if ($comment->getStatus() === 'DELETED') {
        $comment = null;
      }
    };

    return $comment;
  }

  public function getComments($params) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('c'));
    $qb->from('\PF\Comment', 'c');

    $s = !empty($params['s']) ? $params['s'] : 'APPROVED';

    $qb->andWhere($qb->expr()->eq('c.status', ':status'))
      ->setParameter('status', $s);

    $qb->orderBy('c.updated', 'DESC');

    $p = !empty($params['p']) ? $params['p'] : 0;
    $l = !empty($params['l']) ? $params['l'] : 70;

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery();

    return new Paginator($query);
  }
}
