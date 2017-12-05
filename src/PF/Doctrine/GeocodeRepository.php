<?php

namespace PF\Doctrine;

use Doctrine\ORM\EntityRepository;

use PF\Utilities\GeocodeUtil;

class GeocodeRepository extends EntityRepository {

  public function findOneBy(array $criteria, array $orderBy = NULL) {
    $geocode = parent::findOneBy($criteria, $orderBy);

    if (empty($geocode) && !empty($criteria['string'])) {
      $geocode = GeocodeUtil::geocodeString($criteria['string']);

      $this->getEntityManager()->persist($geocode);
      $this->getEntityManager()->flush();
    }

    return $geocode;
  }

}
