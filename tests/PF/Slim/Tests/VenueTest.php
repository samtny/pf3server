<?php

namespace PF\Slim\Tests;

use GuzzleHttp\Client;

class VenueTest extends \PHPUnit_Framework_TestCase
{
  private $bogusCreatedDate;
  private $bogusCreateToken;

  public function setUp() {
    $this->bogusCreatedDate = '2009-09-01T02:49:45+0000';
    $this->bogusCreateToken = 'bogus_token';
  }

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
      'created_token' => 'FE66489F304DC75B8D6E8200DFF8A456E8DAEACEC428B427E9518741C92C6660',
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
      'created' => $this->bogusCreatedDate,
    );

    $response = $client->post('/venue', array(
      'body' => json_encode($data),
    ));

    $this->assertEquals(201, $response->getStatusCode());

    $data = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $data);
    $this->assertArrayHasKey('message', $data);

    $message = $data['message'];

    $this->assertContains('Created', $message);

    preg_match('/with\sID\s([0-9]+)?/', $message, $matches);

    return $matches[1];
  }

  /**
   * @depends testCreateVenue
   */
  public function testUpdateVenue($id) {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $data = array(
      'id' => $id,
      'name' => 'Arcade Age',
      'street' => '1018 Commercial Dr.',
      'city' => 'Brooklyn',
      'state' => 'New York',
      'zipcode' => '11238',
      'latitude' => 30.4331415,
      'longitude' => -84.2938679,
      'phone' => '3789337',
      'url' => 'pinballfinder.org',
      'created_token' => $this->bogusCreateToken,
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
      'created' => $this->bogusCreatedDate,
    );

    $response = $client->post('/venue', array(
      'body' => json_encode($data),
    ));

    $this->assertEquals(200, $response->getStatusCode());

    $data = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $data);
    $this->assertArrayHasKey('message', $data);

    $message = $data['message'];

    $this->assertContains('Updated', $message);

    preg_match('/with\sID\s([0-9]+)?/', $message, $matches);
  }

  /**
   * @depends testCreateVenue
   */
  public function testSearchVenue() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/venue/search');

    $this->assertEquals(200, $response->getStatusCode());

    $response_body = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $response_body);
    $this->assertArrayHasKey('data', $response_body);

    $data = $response_body['data'];

    $this->assertArrayHasKey('venues', $data);

    $first_venue = $data['venues'][0];

    $this->assertArrayHasKey('id', $first_venue);
  }

  public function testGetNewYork() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/venue/search?n=New%20York');

    $this->assertEquals(200, $response->getStatusCode());

    $response_body = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $response_body);
    $this->assertArrayHasKey('data', $response_body);

    $data = $response_body['data'];

    $this->assertArrayHasKey('venues', $data);

    $first_venue = $data['venues'][0];

    $this->assertArrayHasKey('id', $first_venue);
  }

  /**
   * @depends testCreateVenue
   */
  public function testSearchNearbyVenue() {
    $client = new Client(array(
        'base_uri' => 'http://localhost:80',
        'exceptions' => false,
    ));

    $response = $client->get('/venue/search?n=new york');

    $this->assertEquals(200, $response->getStatusCode());

    $response_body = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $response_body);
    $this->assertArrayHasKey('data', $response_body);

    $data = $response_body['data'];

    $this->assertArrayHasKey('venues', $data);

    $first_venue = $data['venues'][0];

    $this->assertArrayHasKey('id', $first_venue);
  }

  /**
   * @depends testCreateVenue
   */
  public function testGetVenue($id) {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/venue/' . $id);

    $this->assertEquals(200, $response->getStatusCode());

    $response_body = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $response_body);
    $this->assertArrayHasKey('data', $response_body);

    $data = $response_body['data'];

    $this->assertArrayHasKey('venue', $data);

    $venue = $data['venue'];

    $this->assertArrayHasKey('created', $venue);

    $created = $venue['created'];

    $this->assertNotEquals($this->bogusCreatedDate, $created);

    return $id;
  }

  /**
   * @depends testGetVenue
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
