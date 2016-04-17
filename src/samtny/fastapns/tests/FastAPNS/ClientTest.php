<?php

namespace FastAPNS\Tests;

use FastAPNS\ClientBuilder;
use FastAPNS\ClientStreamSocket;

class ClientTest extends \PHPUnit_Framework_TestCase
{
  private $_client_stream_socket;

  public function setUp() {
    $this->_client_stream_socket = $this->getMockBuilder('ClientStreamSocket')
      ->setMethods(array('isConnected', 'connect', 'write', 'read', 'status'))
      ->disableOriginalConstructor()
      ->getMock();

    $this->_client_stream_socket->method('connect')
      ->willReturn(TRUE);
  }

  public function testWrite1GoodToken() {
    $this->_client_stream_socket->method('write')
      ->willReturn(ClientStreamSocket::FASTAPNS_WRITE_SUCCESS);

    $this->_client_stream_socket->expects($this->once())
      ->method('write');

    $this->_client_stream_socket->method('status')
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_OTHER);

    $this->_client_stream_socket->expects($this->once())
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->build();

    $client->send('foo', array('ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'), 0);

    $this->assertEmpty($client->getBadTokens());
  }

  public function testWrite1GoodTokenBatchSize1() {
    $this->_client_stream_socket->method('write')
      ->willReturn(ClientStreamSocket::FASTAPNS_WRITE_SUCCESS);

    $this->_client_stream_socket->expects($this->once())
      ->method('write');

    $this->_client_stream_socket->method('status')
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_OTHER);

    $this->_client_stream_socket->expects($this->once())
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->setBatchSize(1)
      ->build();

    $client->send('foo', array('ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'), 0);

    $this->assertEmpty($client->getBadTokens());
  }

  public function testWrite1GoodTokenBatchSize2() {
    $this->_client_stream_socket->method('write')
      ->willReturn(ClientStreamSocket::FASTAPNS_WRITE_SUCCESS);

    $this->_client_stream_socket->expects($this->once())
      ->method('write');

    $this->_client_stream_socket->method('status')
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_OTHER);

    $this->_client_stream_socket->expects($this->once())
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->setBatchSize(2)
      ->build();

    $client->send('foo', array('ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'), 0);

    $this->assertEmpty($client->getBadTokens());
  }

  public function testWrite1GoodTokenBatchSize1700() {
    $this->_client_stream_socket->method('write')
      ->willReturn(ClientStreamSocket::FASTAPNS_WRITE_SUCCESS);

    $this->_client_stream_socket->expects($this->once())
      ->method('write');

    $this->_client_stream_socket->method('status')
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_OTHER);

    $this->_client_stream_socket->expects($this->once())
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->setBatchSize(1700)
      ->build();

    $client->send('foo', array('ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'), 0);

    $this->assertEmpty($client->getBadTokens());
  }

  public function testWrite1BadToken() {
    $this->_client_stream_socket->method('write')
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_READABLE);

    $this->_client_stream_socket->method('read')
      ->willReturn(array('command' => 8, 'status' => 8, 'identifier' => 0));

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->build();

    $client->send('foo', array('ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'), 0);

    $this->assertEquals(1, count($client->getBadTokens()));
  }
}
