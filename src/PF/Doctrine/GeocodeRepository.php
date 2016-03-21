<?php

namespace PF\Doctrine;

use Doctrine\ORM\EntityRepository;

use PF\Geocode;

class GeocodeRepository extends EntityRepository {
  private $base_url = "http://maps.googleapis.com/maps/api/geocode/xml?sensor=false";

  private $tooSmall = array(
    "street_address",
    "intersection",
    "premise",
    "subpremise",
    "natural_feature",
    "park",
    "point_of_interest",
    "post_box",
    "street_number",
    "floor",
    "room"
  );

  public function findOneBy(array $criteria, array $orderBy = NULL) {
    $geocode = parent::findOneBy($criteria, $orderBy);

    if (empty($geocode) && !empty($criteria['string'])) {
      $geocode = $this->geocodeString($criteria['string']);
    }

    return $geocode;
  }

  public function geocodeString($string) {
    $geocode = new Geocode();

    $geocode->setString($string);

    $request_url = $this->base_url . "&address=" . urlencode($string);

    $ch = curl_init($request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $response = curl_exec($ch);
    curl_close($ch);

    $xml = simplexml_load_string($response);

    $status = $xml->status;

    if (strcmp($status, "OK") == 0) {
      $geocode->setCoordinateLatitude((float)$xml->result[0]->geometry->location->lat);
      $geocode->setCoordinateLongitude((float)$xml->result[0]->geometry->location->lng);

      $type = $xml->result[0]->type;
      if (!$type) {
        $type = $xml->result[0]->type[0];
      }

      if (!in_array($type, $this->tooSmall)) {
        if ($xml->result[0]->geometry->bounds) {
          $geocode->setSouthwestLatitude((float)$xml->result[0]->geometry->bounds->southwest->lat);
          $geocode->setSouthwestLongitude((float)$xml->result[0]->geometry->bounds->southwest->lng);
          $geocode->setNortheastLatitude((float)$xml->result[0]->geometry->bounds->northeast->lat);
          $geocode->setNortheastLongitude((float)$xml->result[0]->geometry->bounds->northeast->lng);
        } else {
          $geocode->setSouthwestLatitude((float)$xml->result[0]->geometry->viewport->southwest->lat);
          $geocode->setSouthwestLongitude((float)$xml->result[0]->geometry->viewport->southwest->lng);
          $geocode->setNortheastLatitude((float)$xml->result[0]->geometry->viewport->northeast->lat);
          $geocode->setNortheastLongitude((float)$xml->result[0]->geometry->viewport->northeast->lng);
        }
      }
    }

    $this->getEntityManager()->persist($geocode);
    $this->getEntityManager()->flush();

    return $geocode;
  }
}
