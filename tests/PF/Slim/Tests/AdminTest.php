<?php

namespace PF\Slim\Tests;

use GuzzleHttp\Client;

class AdminTest extends \PHPUnit_Framework_TestCase
{
  public function testAdminRouteAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false
    ));

    $response = $client->get('/admin');

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testVenueApproveRouteAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false
    ));

    $response = $client->post('/venue/123456/approve');

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testDeleteCommentAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false,
    ));

    $response = $client->delete('/comment/1');

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testGeocodeAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false,
    ));

    $response = $client->get('/geocode');

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testNotificationSearchAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false,
    ));

    $response = $client->get('/notification/search');

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testNotificationGetAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false,
    ));

    $response = $client->get('/notification/123');

    $this->assertEquals(401, $response->getStatusCode());
  }
}
