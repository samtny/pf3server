<?php

namespace PF\Doctrine;

use Doctrine;
use Doctrine\ORM\EntityRepository;

class StatRepository extends EntityRepository {

  public function getRequestStats($days = 365) {
    $qb = $this->getEntityManager()->createQueryBuilder();

    $qb->select('YEAR(s.created) as HIDDEN created_year, MONTH(s.created) as HIDDEN created_month, DATE_FORMAT(s.created, \'%b\') as month, COUNT(s) as total')
      ->from('\PF\StatRecord', 's')
      ->where('DATEDIFF(LAST_DAY(CURRENT_DATE()), s.created) <= :days')
      ->setParameter('days', $days)
      ->groupBy('created_year, created_month')
      ->orderBy('created_year, created_month');

    return $qb->getQuery()->getArrayResult();
  }

}
