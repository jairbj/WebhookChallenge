<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Test\HookTestCase;



class MessageControllerTest extends HookTestCase
{
    private $urlTest = 'http://requestb.in/zfdcbdzf';
    private $contentTypeTest = 'application/json';
    private $msgBodyTest = '{"test": true}';
    private $debugResponse = false;

    public function testPOSTMessageAdd()
    {
        $destination = $this->createDestination($this->urlTest);

        $data = array(
            'destination'   => $destination->getId(),
            'contentType'   => $this->contentTypeTest,
            'msgBody'       => $this->msgBodyTest
        );

        $client = static::createClient();

        $this->jsonRequest(
            $client,
            'POST',
            '/messages',
            $data);

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertArrayHasKey('id', json_decode((string)$client->getResponse()->getContent(), true));
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['destination']['id'], $destination->getId());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['destination']['url'], $destination->getUrl());
    }

    public function testPOSTMessageInvalidDestination()
    {

        $data = array(
            'destination'   => 0,
            'contentType'   => $this->contentTypeTest,
            'msgBody'       => $this->msgBodyTest
        );

        $client = static::createClient();

        $this->jsonRequest(
            $client,
            'POST',
            '/messages',
            $data);

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/problem+json'
            )
        );

        $this->assertArrayHasKey('errors', json_decode((string)$client->getResponse()->getContent(), true));
    }

    public function testPOSTMessageInvalidJson() {

        $client = static::createClient();

        //Request without or with invalid json
        $client->request(
            'POST',
            '/messages');


        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testGETMessage() {
        $client = static::createClient();

        $destination = $this->createDestination($this->urlTest);
        $message = $this->createMessage($destination, $this->contentTypeTest, $this->msgBodyTest);


        $client->request(
            'GET',
            '/messages/' . $message->getId()
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('id', json_decode((string)$client->getResponse()->getContent(), true));
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['id'], $message->getId());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['contentType'], $message->getContentType());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['msgBody'], $message->getMsgBody());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['destination']['id'], $destination->getId());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['destination']['url'], $this->urlTest);

    }

    public function testMessage404Exception() {
        $client = static::createClient();

        $client->request(
            'GET',
            '/messages/0'
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('status', json_decode((string)$client->getResponse()->getContent(), true));
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['type'], 'about:blank');
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['title'], 'Not Found');

    }

    public function testDELETEMessage() {
        $client = static::createClient();

        $destination = $this->createDestination($this->urlTest);
        $message = $this->createMessage($destination, $this->contentTypeTest, $this->msgBodyTest);

        $client->request(
            'DELETE',
            '/messages/' . $message->getId()
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testGETMessagesCollection()
    {
        $client = static::createClient();

        $destination = $this->createDestination($this->urlTest);

        for ($i = 1; $i <= 5; $i++) {
            $this->createMessage($destination, $this->contentTypeTest, $this->msgBodyTest . $i);
        }

        $client->request(
            'GET',
            '/messages'
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)[0]['msgBody'], $this->msgBodyTest . '1');
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)[4]['msgBody'], $this->msgBodyTest . '5');
    }
}
