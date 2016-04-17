<?php

namespace FastAPNS\Tests;

use FastAPNS\ClientBuilder;
use FastAPNS\ClientStreamSocket;

class ClientTest extends \PHPUnit_Framework_TestCase
{
  public function testWrite1GoodToken() {
    $client_stream_socket = $this->getMockBuilder('ClientStreamSocket')
      ->disableOriginalConstructor()
      ->setMethods(array('isConnected', 'connect', 'write', 'status'))
      ->getMock();

    $client_stream_socket->method('isConnected')
      ->willReturn(FALSE);

    $client_stream_socket->expects($this->once())
      ->method('isConnected');

    $client_stream_socket->method('connect');

    $client_stream_socket->expects($this->once())
      ->method('connect');

    $client_stream_socket->method('write')
      ->willReturn(ClientStreamSocket::FASTAPNS_WRITE_SUCCESS);

    $client_stream_socket->expects($this->once())
      ->method('write');

    $client_stream_socket->method('status')
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_OTHER);

    $client_stream_socket->expects($this->once())
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($client_stream_socket)
      ->build();

    $client->send('foo', array('ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'), 0);
  }
}
