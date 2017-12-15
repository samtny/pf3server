<?php

namespace PF\Slim\Tests;

use GuzzleHttp\Client;

class CommentTest extends \PHPUnit_Framework_TestCase
{
  private $bogusCreatedDate;
  private $bogusCreateToken;

  public function setUp() {
    $this->bogusCreatedDate = '2009-09-01T02:49:45+0000';
    $this->bogusCreateToken = 'bogus_token';
  }

  public function testCreateComment() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $data = array(
      'text' => 'A fun new comment',
      'created_token' => 'FE66489F304DC75B8D6E8200DFF8A456E8DAEACEC428B427E9518741C92C6660',
      'created' => $this->bogusCreatedDate,
    );

    $response = $client->post('/comment', array(
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
   * @depends testCreateComment
   */
  public function testUpdateComment($id) {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $data = array(
      'id' => $id,
      'text' => 'I changed my mind',
      'created_token' => $this->bogusCreateToken,
      'created' => $this->bogusCreatedDate,
    );

    $response = $client->post('/comment', array(
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
   * @depends testCreateComment
   */
  public function testSearchComment() {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'query' => array(
        's' => 'NEW',
      ),
    ));

    $response = $client->get('/comment/search');

    $this->assertEquals(200, $response->getStatusCode());

    $response_body = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $response_body);
    $this->assertArrayHasKey('data', $response_body);

    $data = $response_body['data'];

    $this->assertArrayHasKey('comments', $data);

    $first_comment = $data['comments'][0];

    $this->assertArrayHasKey('id', $first_comment);
  }

  /**
   * @depends testCreateComment
   */
  public function testGetComment($id) {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
    ));

    $response = $client->get('/comment/' . $id);

    $this->assertEquals(200, $response->getStatusCode());

    $response_body = json_decode($response->getBody(), true);

    $this->assertArrayHasKey('status', $response_body);
    $this->assertArrayHasKey('data', $response_body);

    $data = $response_body['data'];

    $this->assertArrayHasKey('comment', $data);

    $venue = $data['comment'];

    $this->assertArrayHasKey('created', $venue);

    $created = $venue['created'];

    $this->assertNotEquals($this->bogusCreatedDate, $created);

    return $id;
  }

  /**
   * @depends testGetComment
   */
  public function testDeleteCommentAnonymous($id) {
    $client = new Client(array(
      'base_uri' => 'http://localhost:80',
      'exceptions' => false,
      'allow_redirects' => false,
    ));

    $response = $client->delete('/comment/' . $id);

    $this->assertEquals(401, $response->getStatusCode());

    return $id;
  }
}
