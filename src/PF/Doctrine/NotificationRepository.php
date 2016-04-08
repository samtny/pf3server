<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class NotificationRepository extends EntityRepository {
  public function getNotifications($request) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('n'));
    $qb->from('\PF\Notification', 'n');

    $qb->orderBy('n.updated', 'DESC');

    $p = !empty($request->get('p')) ? $request->get('p') : 0;
    $l = !empty($request->get('l')) ? $request->get('l') : 70;

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery();

    return new Paginator($query);
  }
}
