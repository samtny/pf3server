<?php

namespace PF\Legacy\Tests;

use GuzzleHttp\Client;

class LegacyVenueTest extends \PHPUnit_Framework_TestCase {
  public function testLegacyCreateVenue() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<!DOCTYPE pinfinderapp SYSTEM "http://www.pinballfinder.org/pinfinderapp.dtd">';
    $xml .= '<pinfinderapp><status>success</status><locations count="1"><loc><source>pinfinderfree</source><name>TEST - Modern Pinball NYC</name><addr>362 3rd Ave</addr><city>New York</city><state>New York</state><zipcode>10016</zipcode><phone>646-415-8440</phone><lat>40.741073</lat><lon>-73.981888</lon><date>2016-01-02</date><created>2013-12-03</created><url>www.modernpinballnyc.com</url><game new="1"><abbr>P</abbr><cond>5</cond><price>0.00</price><ipdb>5938</ipdb></game><comment><ctext>01/02/16 - Game of thrones pro is in - TEST</ctext><cdate>2016-01-02 12:00:00</cdate></comment></loc></locations></pinfinderapp>';

    $response = $client->post('/legacy', array(
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

    $response = $client->post('/legacy', array(
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

    $response = $client->post('/legacy', array(
      'form_params' => array(
        'doc' => $xml,
      ),
    ));

    $this->assertEquals(200, $response->getStatusCode());
  }

  public static function tearDownAfterClass() {
    $entityManager = \Bootstrap::getEntityManager();

    $venue = $entityManager->getRepository('\PF\Venue')->findOneBy(array('name' => 'TEST - Modern Pinball NYC'));

    if (!empty($venue)) {
      $entityManager->remove($venue);

      $entityManager->flush();
    }
  }
}