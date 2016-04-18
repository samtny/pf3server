<?php

namespace FastAPNS\Tests;

use FastAPNS\ClientBuilder;
use FastAPNS\ClientStreamSocket;
use PHPUnit_Framework_MockObject_MockObject;

class ClientTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var PHPUnit_Framework_MockObject_MockObject $_client_stream_socket
   */
  private $_client_stream_socket;

  public function setUp() {
    $this->_client_stream_socket = $this->getMockBuilder('FastAPNS\ClientStreamSocket')
      ->setMethods(array('isConnected', 'reconnect', 'connect', 'write', 'read', 'status'))
      ->disableOriginalConstructor()
      ->disableProxyingToOriginalMethods()
      ->getMock();

    $this->_client_stream_socket->method('disconnect')
      ->willReturn(NULL);

    $this->_client_stream_socket->method('connect')
      ->willReturn(NULL);

    $this->_client_stream_socket->method('reconnect')
      ->willReturn(TRUE);

    $this->_client_stream_socket->method('isConnected')
      ->willReturn(TRUE);
  }

  public function testWrite1GoodToken() {
    $this->_client_stream_socket->method('write')
      ->willReturn(ClientStreamSocket::FASTAPNS_WRITE_SUCCESS);

    $this->_client_stream_socket->expects($this->once())
      ->method('write');

    $this->_client_stream_socket->method('status')
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_NONE);

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
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_NONE);

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
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_NONE);

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
      ->willReturn(ClientStreamSocket::FASTAPNS_STATUS_NONE);

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

  public function testRewind() {
    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->expects($this->exactly(6))
      ->method('write');

    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_NONE
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $this->_client_stream_socket->method('read')
      ->willReturn(array('command' => 8, 'status' => 8, 'identifier' => 4));

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('read');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->build();

    $client->send('foo', array(
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'bad',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'
    ), 0);

    $this->assertEquals(array('bad'), $client->getBadTokens());
  }

  public function testRewindMulti() {
    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->method('read')
      ->willReturnOnConsecutiveCalls(
        array('command' => 8, 'status' => 10, 'identifier' => 1),
        array('command' => 8, 'status' => 8, 'identifier' => 4)
      );

    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_NONE
      );

    $this->_client_stream_socket->expects($this->exactly(7))
      ->method('write');

    $this->_client_stream_socket->expects($this->exactly(2))
      ->method('read');

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->build();

    $client->send('foo', array(
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914d',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914f'
    ), 0);

    $this->assertEquals(array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e'
    ), $client->getBadTokens());
  }

  public function testBadTokenAsync() {
    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->expects($this->exactly(10))
      ->method('write');

    $this->_client_stream_socket->method('read')
      ->willReturnOnConsecutiveCalls(
        array('command' => 8, 'status' => 8, 'identifier' => 0)
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('read');

    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_NONE
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->build();

    $client->send('foo', array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914d',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914f'
    ), 0);

    $this->assertEquals(array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a'
    ), $client->getBadTokens());
  }

  public function testBadLastToken() {
    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->expects($this->exactly(5))
      ->method('write');

    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_READABLE
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $this->_client_stream_socket->method('read')
      ->willReturnOnConsecutiveCalls(
        array('command' => 8, 'status' => 8, 'identifier' => 4)
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('read');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->build();

    $client->send('foo', array(
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914d',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e'
    ), 0);

    $this->assertEquals(array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e'
    ), $client->getBadTokens());
  }

  public function testBadTokenImmediatePriorBatch() {
    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->expects($this->exactly(18))
      ->method('write');

    // read
    $this->_client_stream_socket->method('read')
      ->willReturnOnConsecutiveCalls(
        array('command' => 8, 'status' => 8, 'identifier' => 1)
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('read');

    // status
    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_NONE
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->setBatchSize(5)
      ->build();

    $client->send('foo', array(
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914d',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914f',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79150',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79151',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79152',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79153'
    ), 0);

    $this->assertEquals(array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b'
    ), $client->getBadTokens());
  }

  public function testBadTokenEarlierBatch() {
    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->expects($this->exactly(26))
      ->method('write');

    // read
    $this->_client_stream_socket->method('read')
      ->willReturnOnConsecutiveCalls(
        array('command' => 8, 'status' => 8, 'identifier' => 2)
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('read');

    // status
    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_NONE
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->setBatchSize(5)
      ->build();

    $client->send('foo', array(
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914d',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e',

      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914f',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79150',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79151',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79152',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79153',

      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79154',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79155',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79156',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79157',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79158'
    ), 0);

    $this->assertEquals(array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c'
    ), $client->getBadTokens());
  }

  public function testMultipleBadTokensEarlierBatches() {
    $payload = 'foo';
    $expiry = 0;

    $tokens = array(
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914d',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e',

      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914f',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79150',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79151',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79152',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79153',

      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79154',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79155',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79156',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79157',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79158',
    );

    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->expects($this->exactly(32))
      ->method('write')
      ->withConsecutive(
        array($this->equalTo(chr(1) . pack('N', 0) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[0]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 1) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[1]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 2) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[2]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 3) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[3]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 4) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[4]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 5) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[5]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 6) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[6]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 7) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[7]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 8) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[8]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 9) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[9]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 10) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[10]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 11) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[11]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 12) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[12]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 13) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[13]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 3) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[3]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 4) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[4]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 5) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[5]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 6) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[6]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 7) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[7]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 8) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[8]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 9) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[9]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 10) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[10]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 5) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[5]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 6) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[6]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 7) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[7]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 8) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[8]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 9) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[9]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 10) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[10]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 11) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[11]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 12) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[12]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 13) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[13]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 14) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[14]) . chr(0) . chr(mb_strlen($payload)) . $payload))
      );

    // read
    $this->_client_stream_socket->method('read')
      ->willReturnOnConsecutiveCalls(
        array('command' => 8, 'status' => 8, 'identifier' => 2),
        array('command' => 8, 'status' => 8, 'identifier' => 4)
      );

    $this->_client_stream_socket->expects($this->exactly(2))
      ->method('read');

    // status
    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_NONE
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->setBatchSize(5)
      ->build();

    $client->send($payload, $tokens, 0);

    $this->assertEquals(array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e',
    ), $client->getBadTokens());
  }

  public function testBadTokenAndDisconnect() {
    $payload = 'foo';
    $expiry = 0;

    $tokens = array(
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914a',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914b',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914d',
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914e',

      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914f',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79150',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79151',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79152',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79153',

      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79154',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79155',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79156',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79157',
      'ca360e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b79158',
    );

    $this->_client_stream_socket->method('write')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_STATUS_READABLE,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,

        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS,
        ClientStreamSocket::FASTAPNS_WRITE_SUCCESS
      );

    $this->_client_stream_socket->expects($this->exactly(33))
      ->method('write')
      ->withConsecutive(
        array($this->equalTo(chr(1) . pack('N', 0) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[0]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 1) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[1]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 2) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[2]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 3) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[3]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 4) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[4]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 5) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[5]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 6) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[6]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 7) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[7]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 8) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[8]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 9) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[9]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 10) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[10]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 11) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[11]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 12) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[12]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 13) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[13]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 3) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[3]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 4) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[4]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 5) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[5]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 6) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[6]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 7) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[7]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 8) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[8]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 9) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[9]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 10) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[10]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 4) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[4]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 5) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[5]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 6) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[6]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 7) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[7]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 8) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[8]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 9) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[9]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 10) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[10]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 11) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[11]) . chr(0) . chr(mb_strlen($payload)) . $payload)),

        array($this->equalTo(chr(1) . pack('N', 12) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[12]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 13) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[13]) . chr(0) . chr(mb_strlen($payload)) . $payload)),
        array($this->equalTo(chr(1) . pack('N', 14) . pack('N', $expiry) .  chr(0) . chr(32) . pack('H*', $tokens[14]) . chr(0) . chr(mb_strlen($payload)) . $payload))
      );

    // read
    $this->_client_stream_socket->method('read')
      ->willReturnOnConsecutiveCalls(
        array('command' => 8, 'status' => 8, 'identifier' => 2),
        array('command' => 8, 'status' => 10, 'identifier' => 4)
      );

    $this->_client_stream_socket->expects($this->exactly(2))
      ->method('read');

    // status
    $this->_client_stream_socket->method('status')
      ->willReturnOnConsecutiveCalls(
        ClientStreamSocket::FASTAPNS_STATUS_NONE
      );

    $this->_client_stream_socket->expects($this->exactly(1))
      ->method('status');

    $client = ClientBuilder::create()
      ->setStreamSocketClient($this->_client_stream_socket)
      ->setBatchSize(5)
      ->build();

    $client->send($payload, $tokens, 0);

    $this->assertEquals(array(
      'BAD60e9029938b9ed8ed435640f3760620526bd72037017d3c50cfa264b7914c',
    ), $client->getBadTokens());
  }
}
