<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CommentRepository extends EntityRepository {
  public function find($id) {
    $comment = parent::find($id);

    if (!empty($comment)) {
      if ($comment->getStatus() === 'DELETED') {
        $comment = null;
      }
    };

    return $comment;
  }

  public function getComments($request) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('c'));
    $qb->from('\PF\Comment', 'c');

    $s = !empty($request->get('s')) ? $request->get('s') : 'APPROVED';

    $qb->andWhere($qb->expr()->eq('c.status', ':status'))
      ->setParameter('status', $s);

    $qb->orderBy('c.updated', 'DESC');

    $p = !empty($request->get('p')) ? $request->get('p') : 0;
    $l = !empty($request->get('l')) ? $request->get('l') : 70;

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery();

    return new Paginator($query);
  }
}
