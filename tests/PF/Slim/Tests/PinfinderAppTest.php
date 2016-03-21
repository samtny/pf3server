<?php

namespace PF\Slim\Tests;

use GuzzleHttp\Client;

class PinfinderAppTest extends \PHPUnit_Framework_TestCase
{
  private $createdVenueId;

  public function testCreateVenue() {
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

    $message = $data['message'];

    $this->assertContains('with ID ', $message);

    preg_match('/with\sID\s([0-9]+)?/', $message, $matches);

    return $matches[1];
  }

  /**
   * @depends testCreateVenue
   */
  public function testDeleteVenue($id) {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->delete('/venue/' . $id);

    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $data);
    $this->assertArrayHasKey('message', $data);

    $message = $data['message'];

    $this->assertContains('with ID ', $message);

    return $id;
  }

  /**
   * @depends testDeleteVenue
   */
  public function testGetDeletedVenue($id) {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/venue/' . $id);

    $this->assertEquals(404, $response->getStatusCode());
  }
}