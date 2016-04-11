<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class NotificationRepository extends EntityRepository {
  public function getPendingNotifications() {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('n'));
    $qb->from('\PF\Notification', 'n');

    $s = !empty($request->get('s')) ? $request->get('s') : 'NEW';

    $qb->andWhere($qb->expr()->eq('n.status', ':status'))
      ->setParameter('status', $s);

    $qb->orderBy('n.updated', 'ASC');

    $query = $qb->getQuery();

    return new Paginator($query);
  }

  public function getNotifications($request) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select(array('n'));
    $qb->from('\PF\Notification', 'n');

    $s = !empty($request->get('s')) ? $request->get('s') : 'NEW';

    $qb->andWhere($qb->expr()->eq('n.status', ':status'))
      ->setParameter('status', $s);

    $qb->orderBy('n.updated', 'DESC');

    $p = !empty($request->get('p')) ? $request->get('p') : 0;
    $l = !empty($request->get('l')) ? $request->get('l') : 70;

    $qb->setFirstResult($p * $l)
      ->setMaxResults($l);

    $query = $qb->getQuery();

    return new Paginator($query);
  }
}