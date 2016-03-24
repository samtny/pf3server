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

    $this->assertEquals(301, $response->getStatusCode());

    $location = $response->getHeader('Location')[0];

    $this->assertEquals($location, '/login');
  }

  public function testVenueApproveRouteAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false
    ));

    $response = $client->post('/venue/123456/approve');

    $this->assertEquals(301, $response->getStatusCode());

    $location = $response->getHeader('Location')[0];

    $this->assertEquals($location, '/login');
  }

  public function testDeleteCommentAnonymous() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false,
    ));

    $response = $client->delete('/comment/1');

    $this->assertEquals(301, $response->getStatusCode());
  }
}
