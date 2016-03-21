<?php

namespace PF\Slim\Tests;

use GuzzleHttp\Client;

class PinfinderAppTest extends \PHPUnit_Framework_TestCase
{
  public function testPOST()
  {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $data = array(
      'name' => 'Arcade Age',
      'street' => '1018 Commercial Dr.',
      'city' => 'Brooklyn',
      'state' => 'New York',
      'zipcode' => '11238',
      'latitude' => 30.4331415,
      'longitude' => -84.2938679,
      'phone' => '3789337',
      'url' => 'pinballfinder.org',
      'machines' => array(
        array(
          'name' => 'Dracula',
          'ipdb' => 728,
          'condition' => 3,
          'price' => '0.50',
        )
      ),
      'comments' => array(
        array(
          'text' => 'Here is a comment, yo',
        ),
      ),
    );

    $response = $client->post('/venue', array(
      'body' => json_encode($data),
    ));

    $this->assertEquals(201, $response->getStatusCode());

    $data = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $data);
    $this->assertArrayHasKey('message', $data);
  }
}
