<?php

namespace PF\Legacy\Tests;

use GuzzleHttp\Client;

class LegacyTest extends \PHPUnit\Framework\TestCase {
  public function testLegacyCreateVenue() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<!DOCTYPE pinfinderapp SYSTEM "http://www.pinballfinder.org/pinfinderapp.dtd">';
    $xml .= '<pinfinderapp version="2.2.2"><locations><loc><name>test venue</name><addr>Smoething</addr><city></city><state></state><zipcode></zipcode><phone></phone><url></url><game><abbr>TMNT</abbr><cond>3</cond><price>0.75</price></game><comment><ctext>Sup</ctext></comment></loc></locations></pinfinderapp>';

    $response = $client->post('/pf2/pf', array(
      'form_params' => array(
        'doc' => $xml,
      ),
    ));

    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testLegacyCreateToken() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<!DOCTYPE pinfinderapp SYSTEM "http://www.pinballfinder.org/pinfinderapp.dtd">';
    $xml .= '<pinfinderapp><meta><user><token service="apnsfree2">&lt;abcdabce f8211498 8cd6dc4f 76ac2bab 41f4064c b165583c e702419e d1cd88db&gt;</token></user></meta></pinfinderapp>';

    $response = $client->post('/pf2/pf', array(
      'form_params' => array(
        'doc' => $xml,
      ),
    ));

    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testLegacyCreateVenueWithToken() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<!DOCTYPE pinfinderapp SYSTEM "http://www.pinballfinder.org/pinfinderapp.dtd">';
    $xml .= '<pinfinderapp><meta><user><token service="apnsfree2">&lt;abcdabce f8211498 8cd6dc4f 76ac2bab 41f4064c b165583c e702419e d1cd88db&gt;</token></user></meta><locations count="1"><loc><source>pinfinderfree</source><name>TEST - Modern Pinball NYC - WITH TOKEN</name><addr>362 3rd Ave</addr><city>New York</city><state>New York</state><zipcode>10016</zipcode><phone>646-415-8440</phone><lat>40.741073</lat><lon>-73.981888</lon><date>2016-01-02</date><created>2013-12-03</created><url>www.modernpinballnyc.com</url><game new="1"><abbr>P</abbr><cond>5</cond><price>0.00</price><ipdb>5938</ipdb></game><comment><ctext>01/02/16 - Game of thrones pro is in - TEST</ctext><cdate>2016-01-02 12:00:00</cdate></comment></loc></locations></pinfinderapp>';

    $response = $client->post('/pf2/pf', array(
      'form_params' => array(
        'doc' => $xml,
      ),
    ));

    $this->assertEquals(200, $response->getStatusCode());
  }

  private function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
  }

  public function testLegacyDownloadVenuesNearLocation() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
    ));

    $response = $client->get('/pf2/pf?n=40.759211,-73.984638');

    $this->assertEquals(200, $response->getStatusCode());

    $xml = $response->getBody();

    $doc = new \DOMDocument();
    $doc->loadXML($xml);

    $locs = $doc->getElementsByTagName("loc");

    $this->assertNotEmpty($locs->item(0));

    foreach ($locs as $index => $loc) {
      if ($index === 0) {
        $resultLat = $loc->getElementsByTagName("lat")->item(0)->nodeValue;
        $resultLon = $loc->getElementsByTagName("lon")->item(0)->nodeValue;

        $dist = $this->haversineGreatCircleDistance($resultLat, $resultLon, 40.759211, -73.984638);

        $this->assertGreaterThanOrEqual(1, $dist);
        $this->assertLessThan(1000, $dist);
      }

      $this->assertNotEmpty($loc->getElementsByTagName("name")->item(0)->nodeValue);
    }
  }

  public function testLegacyDeleteGame() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<!DOCTYPE pinfinderapp SYSTEM "http://www.pinballfinder.org/pinfinderapp.dtd">';
    $xml .= '<pinfinderapp version="2.2.2"><locations><loc key="11606"><game deleted="1" /></loc></locations></pinfinderapp>';

    $response = $client->post('/pf2/pf', array(
      'form_params' => array(
        'doc' => $xml,
      ),
    ));

    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testLegacyGetSpecialRecent() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/pf2/pf?q=recent&t=special');

    $this->assertEquals(200, $response->getStatusCode());

    $xml = $response->getBody();

    $doc = new \DOMDocument();
    $doc->loadXML($xml);

    $locs = $doc->getElementsByTagName("loc");

    $this->assertNotEmpty($locs->item(0));

    foreach ($locs as $loc) {
      $this->assertNotEmpty($loc->getElementsByTagName("name")->item(0)->nodeValue);
    }
  }

  public function testLegacyGetFishtales() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/pf2/pf?q=fish tales&t=game');

    $this->assertEquals(200, $response->getStatusCode());

    $xml = $response->getBody();

    $doc = new \DOMDocument();
    $doc->loadXML($xml);

    $locs = $doc->getElementsByTagName("loc");

    $this->assertNotEmpty($locs->item(0));

    foreach ($locs as $loc) {
      $this->assertNotEmpty($loc->getElementsByTagName("name")->item(0)->nodeValue);

      $fishtales = array();

      $games = $loc->getElementsByTagName("game");

      foreach ($games as $g) {
        if (!empty($g->getElementsByTagName("abbr")->item(0)->nodeValue)) {
          if ($g->getElementsByTagName("abbr")->item(0)->nodeValue === 'FT') {
            $fishtales[] = 'FT';

            break;
          }
        }
      }

      $this->assertEquals(array('FT'), $fishtales);

      break;
    }
  }

  public function testLegacySearchSunshineLaundromat() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/pf2/pf?q=sunshine%20laundromat&t=venue');

    $this->assertEquals(200, $response->getStatusCode());

    $xml = $response->getBody();

    $doc = new \DOMDocument();
    $doc->loadXML($xml);

    $locs = $doc->getElementsByTagName("loc");

    $this->assertNotEmpty($locs->item(0));

    foreach ($locs as $loc) {
      $this->assertEquals('Sunshine Laundromat', $loc->getElementsByTagName("name")->item(0)->nodeValue);

      break;
    }
  }

  public static function tearDownAfterClass() {
    $entityManager = \Bootstrap::getEntityManager();

    $names = array(
      'test venue',
      'TEST - Modern Pinball NYC',
      'TEST - Modern Pinball NYC - WITH TOKEN'
    );

    foreach ($names as $name) {
      $venue = $entityManager->getRepository('\PF\Venue')->findOneBy(array('name' => $name));

      if (!empty($venue)) {
        $entityManager->remove($venue);

        $entityManager->flush($venue);
      }
    }
  }
}
